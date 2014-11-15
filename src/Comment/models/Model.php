<?php
namespace Foo\Comment;

class Model {
    private $db;

    public function __construct ($db) {
        $this->db = $db;
    }

    public function count ($code) {
        return $this->db->collection('comments')->find()->count();
    }
}