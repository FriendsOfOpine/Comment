if ($('a.comment-submit').length) {
    console.log("Loading Opine Comment");
    require.ensure([], function(require) {
        var $ = require('jquery');
        require('semantic');
        require('./behaviors.js');
        if ($('#opine-comment-config').length) {
            var config = JSON.parse($('#opine-comment-config').text());
            if (typeof(config['cssPath']) == 'undefined') {
                require('./../css/style.css');
            }
        } else {
            require('./../css/style.css');
        }
    });
} else {
    console.log("Skipping Loading Opine Comment");
}