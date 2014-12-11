$(function () {
    $('form.comment-logged-out textarea').focus(function () {
        $(".account-panel-toggle").trigger("click");
        $(this).blur();
    });

    $('form.comment-logged-in .ui.rating').rating({
        onRate: function (value) {
            var dbURI = $(this).closest('.comment').attr('data-dburi');
            console.log(dbURI);
            $.ajax({
                type: "POST",
                url: '/Comment/api/upvote/' + dbURI,
                success: function (response) {
                    consle.log(response);
                },
                error: function () {
                    console.log('Error');
                },
                dataType: 'json'
            });
        }
    }).popup({inline: true});

    $('form.comment-logged-out .ui.rating').rating({
        onRate: function (value) {
            $(".account-panel-toggle").trigger("click");
        }
    });

    $('body').on({
        click: function () {
            $(this).closest('form').submit();
        }
    }, 'a.comment-submit');

    $('body').on({
        click: function () {
            var $content = $(this).closest('.content');
            var $clone = $('#main-comment-post').clone();
            var unique = 'A' + 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
                return v.toString(16);
            });
            var replyto = $(this).attr('data-replyto');
            $clone.attr('id', unique);
            $clone.append('<input type="hidden" name="FooComment-Post[reply_to]" value="' + replyto + '">');
            $content.append($clone);
            $content.find('textarea').focus();
            $(this).unbind().removeClass('comment-reply').addClass('cancel-reply').attr('data-id', unique);
            $(this).html('Cancel Reply');
        }
    }, '.comment-logged-in a.comment-reply');

    $('body').on({
        click: function () {
            var id = $(this).attr('data-id');
            $('#' + id).remove();
            $(this).unbind().removeClass('cancel-reply').addClass('comment-reply').removeAttr('data-id');
            $(this).html('Reply');
        }
    }, 'a.cancel-reply');

    $('.comment-logged-out a.comment-reply').click(function () {
        $(".account-panel-toggle").trigger("click");
    });
});