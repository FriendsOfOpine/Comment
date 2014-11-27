<?php
namespace Helper\Comment;

use Opine\Interfaces\Layout as LayoutInterface;

class Comment {
    private $layout;
    private $person;
    private $commentModel;
    private $secret;

    public function __construct (LayoutInterface $layout, $person, $commentModel, $secret) {
        $this->layout = $layout;
        $this->person = $person;
        $this->commentModel = $commentModel;
        $this->secret = $secret;
    }

    public function render (Array $arguments, Array $options) {
        $code = '';
        if (isset($options['code'])) {
            $code = $options['code'];
        }
        $authors = '';
        if (isset($options['authors'])) {
            $authors = $this->secret->encrypt($options['authors']);
        }
        $url = '';
        if (isset($options['url'])) {
            $url = $options['url'];
        }
        $commentCount = $this->commentModel->count($code);
        $context = [
            'comment_count' => $commentCount . ' Comment' . (($commentCount == 1) ? '' : 's'),
            'code'          => $code,
            'status'        => 'comment-logged-in',
            'authors'       => $authors,
            'url'           => $url
        ];
        $user = $this->person->current();
        if (is_array($user)) {
            $context['name'] = $user['first_name'] . ' ' . $user['last_name'];
            $context['email'] = $user['email'];
            $context['status'] = 'comment-logged-in';
        } else {
            $context['status'] = 'comment-logged-out';
            $context['email'] = '';
        }
        return $this->layout->
            config(['comment/index', 'Comment/index'])->
            url('comments', '/Comment/api/collection/Comments/byField-code-' . $code . '/1000/0/{"created_date":-1}')->
            container(['comment/index', 'Comment/index'], $context)->
            render();
    }
}