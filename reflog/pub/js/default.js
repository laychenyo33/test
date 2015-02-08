$(function () {
    $('h3 + ol').hide();
    $('h3').css('cursor', 'pointer').click(function () {
        $(this).next('ol').toggle('slow');
    });
});