<?php
namespace Comment\Collection;

class Comments {
    public $publishable = false;
    public $singular = 'comment';

    public function indexSearch ($document) {
        return [
            'title'       => (strlen($document['body']) > 80) ? substr($document['body'], 0, 80) . '...' : $document['body'],
            'description' => $document['body'],
            'date'        => date('c', $document['created_date']->sec),
            'acl'         => ['public']
        ];
    }

    public function indexData () {
        return [
            ['keys' => ['code' => 1, 'created_date' -1]]
        ];
    }
}