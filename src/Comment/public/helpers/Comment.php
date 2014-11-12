<?php
return function ($arguments, $options) {
    $layout = \Opine\container()->layout;
    return $layout->make(['comment/index', 'Comment/index']);
};