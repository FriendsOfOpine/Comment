<?php
namespace Foo\Comment;
use Exception;

class Model {
    private $db;
    private $post;
    private $person;
    private $secret;

    public function __construct ($db, $post, $person, $secret) {
        $this->db = $db;
        $this->post = $post;
        $this->person = $person;
        $this->secret = $secret;
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
            'body'         => strip_tags($document['body']),
            'code'         => $document['code'],
            'created_date' => $this->db->date(),
            'replies'      => [],
            'votes'        => [],
            'reply_count'  => 0,
            'status'       => 'pending',
            'url'          => $document['url']
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
                $authors = $this->secret->decrypt($document['authors']);
                if (substr_count($authors, ',')) {
                    $authors = explode(',', $authors);
                } else {
                    $authors = [$authors];
                }
                if (in_array($user['email'], $authors)) {
                    $comment['moderator'] = true;
                }
            }
        }
        $this->db->documentStage($dbURI)->upsert($comment);
        $context['formObject']->after = 'refresh';
        if (isset($document['url'])) {
            $context['formObject']->after = 'redirect';
            $context['formObject']->redirect = $document['url'] . '#comment-' . (string)$commentId;
        }
        $this->post->statusSaved();
    }
}