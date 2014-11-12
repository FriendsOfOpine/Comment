<?php
namespace Foo\Comment;

class CommentApiController {
    private $anonymous;
    private $moderated;

    public function __construct ($config) {
        $config = array_merge(['anonymous' => false, 'moderated' => false], $config->comment);
        $this->anonymous = $config['anonymous'];
        $this->moderated = $config['moderated'];
    }

    public function authFilter () {

    }
}