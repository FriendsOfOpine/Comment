$(function () {
    $('form.comment-logged-out textarea').focus(function () {
        $(".account-panel-toggle").trigger("click");
        $(this).blur();
    });
});