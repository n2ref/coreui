$(document).ready(function() {
    $(document).click(function() {
        if ($('.combine-panel-tabs .dropdown.open')[0]) {
            $('.combine-panel-tabs .dropdown.open').removeClass('open');
        }
    });

    $('.combine-panel-tabs .dropdown-toggle').click(function() {
        $(this).parent().toggleClass('open');
        return false;
    });
});