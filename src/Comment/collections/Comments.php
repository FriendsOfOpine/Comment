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

    public function chunk (&$documents) {
        foreach ($documents as &$document) {
            if (isset($document['replies']) && is_array($document['replies'])) {
                foreach ($document['replies'] as &$reply) {
                    $reply['_id'] = (string)$reply['_id'];
                }
            }
        }
        foreach ($documents as &$document) {
            if (isset($document['likes']) && is_array($document['likes'])) {
                foreach ($document['likes'] as &$like) {
                    $like['_id'] = (string)$like['_id'];
                }
            }
        }
    }

    public function indexData () {
        return [
            ['keys' => ['code' => 1, 'created_date' -1]]
        ];
    }
}