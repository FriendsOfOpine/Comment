<?php
return function ($arguments, $options) {
    $layout = \Opine\container()->layout;
    $person = \Opine\container()->person;

    return $layout->make(['comment/index', 'Comment/index']);
};