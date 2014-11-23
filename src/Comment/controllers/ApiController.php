<?php
namespace Foo\Comment;

class ApiController {
    private $model;
    private $anonymous;
    private $moderated;

    public function __construct ($config, $model) {
        $this->model = $model;
        $config = $config->comment;
        if (!is_array($config)) {
            $config = [];
        }
        $config = array_merge(['anonymous' => false, 'moderated' => false], $config);
        $this->anonymous = $config['anonymous'];
        $this->moderated = $config['moderated'];
    }

    public function authFilter () {}

    public function upvote ($dbURI) {
        $this->model->vote($dbURI, 'up');
    }

    public function downvote ($dbURI) {
        $this->model->vote($dbURI, 'down');
    }
}