
window.$ = jQuery
jQuery(document).ready(function ($) {
    $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
    postboxes.add_postbox_toggles('<?php echo esc_html($this->pagehook); ?>');
    $('input[type=tel]').bind('keypress', function (e) {
        var keyCode = (e.which) ? e.which : event.keyCode;
        return !(keyCode > 31 && (keyCode < 48 || keyCode > 57) && keyCode !== 43);
    });
});