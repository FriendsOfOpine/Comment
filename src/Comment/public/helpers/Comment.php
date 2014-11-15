<?php
return function ($arguments, $options) {
    $layout = \Opine\container()->layout;
    $person = \Opine\container()->person;
    $commentModel = \Opine\container()->commentModel;
    $code = '';
    if (isset($options['code'])) {
        $code = $options['code'];
    }
    $authors = '';
    if (isset($options['authors'])) {
        $authors = $options['authors'];
    }
    $url = '';
    if (isset($options['url'])) {
        $url = $options['url'];
    }
    $commentCount = $commentModel->count($code);
    $context = [
        'comment_count' => $commentCount . ' Comment' . (($commentCount == 1) ? '' : 's'),
        'code'          => $code,
        'status'        => 'comment-logged-in',
        'authors'       => $authors,
        'url'           => $url
    ];
    $user = $person->current();
    if (is_array($user)) {
        $context['name'] = $user['first_name'] . ' ' . $user['last_name'];
    } else {
        $context['status'] = 'comment-logged-out';
    }
    return $layout->
        app(['comment/index', 'Comment/index'])->
        context($context)->
        layout(['comment/index', 'Comment/index'])->
        render();
};