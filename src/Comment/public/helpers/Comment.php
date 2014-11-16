<?php
return function ($arguments, $options) {
    $layout = \Opine\container()->layout;
    $person = \Opine\container()->person;
    $commentModel = \Opine\container()->commentModel;
    $secret = \Opine\container()->secret;
    $code = '';
    if (isset($options['code'])) {
        $code = $options['code'];
    }
    $authors = '';
    if (isset($options['authors'])) {
        $authors = $secret->encrypt($options['authors']);
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
        $context['email'] = $user['email'];
        $context['status'] = 'comment-logged-in';
    } else {
        $context['status'] = 'comment-logged-out';
        $context['email'] = '';
    }
    return $layout->
        app(['comment/index', 'Comment/index'])->
        context($context)->
        url('comments', '/Comment/api/collection/Comments/byField-code-' . $code . '/1000/0/{"created_date":-1}')->
        layout(['comment/index', 'Comment/index'])->
        render();
};