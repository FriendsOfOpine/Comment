<?php
return function ($arguments, $options) {
    $layout = \Opine\container()->layout;
    return $layout->make(
        ['comments/index', 'Comments/index']);
};