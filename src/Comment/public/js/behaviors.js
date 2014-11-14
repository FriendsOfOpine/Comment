$(function () {
    $('.comment-body.unauthorized').focus(function () {
        $(".account-panel-toggle").trigger("click");
        $(this).blur();
    });
});