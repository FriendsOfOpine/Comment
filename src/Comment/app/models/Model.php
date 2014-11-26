<?php
/**
 * Foo\Comment\Model
 *
 * Copyright (c)2013, 2014 Ryan Mahoney, https://github.com/Opine-Org <ryan@virtuecenter.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
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
        $pipeline = [
            ['$match' => ['code' => $code]],
            ['$group' =>
                [
                    '_id' => 'sum',
                    'total' => ['$sum' => '$count']
                ]
            ]
        ];
        $results = $this->db->collection('comments')->aggregate($pipeline);
        if (isset($results['result'][0]['total'])) {
            return $results['result'][0]['total'];
        }
        return 0;
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
            '_id'             => $commentId,
            'body'            => strip_tags($document['body']),
            'code'            => $document['code'],
            'created_date'    => $this->db->date(),
            'replies'         => [],
            'upvotes'         => [],
            'downvotes'       => [],
            'upvotes_count'   => 0,
            'downvotes_count' => 0,
            'status'          => 'pending',
            'url'             => $document['url'],
            'count'           => 0
        ];
        if (isset($document['reply_to'])) {
            unset($comment['replies']);
            $dbURI = 'comments:' . (string)$document['reply_to'] . ':replies:' . (string)$commentId;
            $parentURI = 'comments:' . (string)$document['reply_to'];
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
        $this->db->document($dbURI)->upsert($comment);
        $context['formObject']->after = 'refresh';
        if (isset($document['url'])) {
            $context['formObject']->after = 'refresh';
            $context['formObject']->redirect = $document['url'] . '#comment-' . (string)$commentId;
        }
        $parts = explode(':', $parentURI);
        $parentId = $parts[1];
        $this->db->collection('comments')->update(['_id' => $this->db->id($parentId)], ['$inc' => ['count' => 1]]);
        $this->post->statusSaved();
    }

    public function vote ($dbURI, $mode) {
        $field = 'upvotes';
        if ($mode == 'down') {
            $field = 'downvotes';
        }
        $parts = explode(':', $dbURI);
        $id = $parts[1];
        $user = $this->person->current();
        if (!is_array($user)) {
            return;
        }
        $checkDbURI = $dbURI . ':' . $field;
        $check = $this->db->document($checkDbURI)->checkByCriteria(['email' => $user['email']]);
        if ($check === false) {
            $voteDbURI = $dbURI . ':' . $field . ':' . (string)$this->db->id();
            $this->db->document($voteDbURI, [
                'email' => $user['email'],
                'name' => $user['first_name'] . ' ' . $user['last_name']
            ])->upsert();
            $this->db->document($dbURI)->increment($field . '_count');
        } else {
            $this->db->document($check['dbURI'])->remove();
            $this->db->document($dbURI)->decrement($field . '_count');
        }
        echo json_encode(['success' => true]);
    }
}