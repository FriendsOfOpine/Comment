<?php
return function ($arguments, $options) {
    $layout = \Opine\container()->layout;
    $person = \Opine\container()->person;
    $code = '';
    if (isset($options['code'])) {
        $code = $options['code'];
    }
    return $layout->make(['comment/index', 'Comment/index']);
};