<?php
namespace Foo\Comment;
use Comment\Collection\Comment as CommentCollection;
use Exception;

class Model {
    private $db;
    private $post;
    private $person;

    public function __construct ($db, $post, $person) {
        $this->db = $db;
        $this->post = $post;
        $this->person = $person;
    }

    public function count ($code) {
        return $this->db->collection('comments')->find()->count();
    }

    public function save ($context) {
        $document = $this->post->getAndCheck($context['formMarker']);
        if (!isset($document['body']) || empty($document['body']) || trim($document['body']) == '') {
            $this->post->errorFieldSet($context['formMarker'], 'Comment can not be blank.');
            return;
        }
        if (!isset($document['code']) || empty($document['code'])) {
            $this->post->errorFieldSet($context['formMarker'], 'Comment must be assigned to specific content.');
            return;
        }
        $commentId = $this->db->id();
        $dbURI = 'comments:' . (string)$commentId;
        $parentURI = $dbURI;
        $comment = [
            '_id'          => $commentId,
            'body'         => $document['body'],
            'code'         => $document['code'],
            'created_date' => $this->db->date(),
            'replies'      => [],
            'reply_count'  => 0,
            'status'       => ''
        ];
        if (isset($document['reply_to'])) {
            unset($comment['replies']);
            unset($comment['reply_count']);
            $dbURI = 'comments:' . (string)$document['reply_to'] . ':replies:' . (string)$commentId;
        }
        $user = $this->person->current();
        if (is_array($user)) {
            $comment['user'] = [
                'email' => $user['email'],
                'name' => $user['first_name'] . ' ' . $user['last_name'],
                '_id' => $this->db->id($user['_id'])
            ];
            if (isset($document['authors']) || empty($document['authors'])) {
                if (substr_count($document['authors'], ',')) {
                    $authors = explode(',', $document['authors']);
                } else {
                    $authors = [$document['authors']];
                }
                if (in_array($user['email'], $authors)) {
                    $comment['moderator'] = true;
                }
            }
        }
        $this->db->documentStage($dbURI)->upsert($comment);
        $context['formObject']->after = 'refresh';
        if (isset($context['url'])) {
            $context['formObject']->after = 'redirect';
            $context['formObject']->redirect = $context['url'] . '#comment-' . (string)$commentId;
        }
        $collectionInstance = $this->collectionService->factory(new CommentCollection());
        $managerUrl = '/Manager/item/Comment-comment/' . $parentURI;
        $collectionInstance->index($dbURI, $comment, $managerUrl);
        $this->post->statusSaved();
    }
}