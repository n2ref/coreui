$(document).ready(function() {
    $(document).click(function() {
        if ($('.combine-tabs-container > ul .dropdown.open')[0]) {
            $('.combine-tabs-container > ul .dropdown.open').removeClass('open');
        }
    });

    $('.combine-tabs-container > ul .dropdown-toggle').click(function() {
        $(this).parent().toggleClass('open');
        return false;
    });
});