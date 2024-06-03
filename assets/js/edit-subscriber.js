//Show the Modal ThickBox For Each Edit link
function sms_to_woo_edit_subscriber(subscriber_id) {
    tb_show(sms_to_woo_edit_subscribe_ajax_vars.tb_show_tag, sms_to_woo_edit_subscribe_ajax_vars.tb_show_url + '&subscriber_id=' + subscriber_id + '&width=400&height=310');
}