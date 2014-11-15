<?php
return function ($arguments, $options) {
    $layout = \Opine\container()->layout;
    $person = \Opine\container()->person;
    $commentModel = \Opine\container()->commentModel;
    $code = '';
    if (isset($options['code'])) {
        $code = $options['code'];
    }
    $context = [
        'comment_count' => $commentModel->count($code),
        'code' => $code
    ];
    $user = $person->current();
    if (is_array($user)) {
        $context['name'] = $user['first_name'] . ' ' . $user['last_name'];
    }
    return $layout->
        app(['comment/index', 'Comment/index'])->
        context($context)->
        layout(['comment/index', 'Comment/index'])->
        render();
};