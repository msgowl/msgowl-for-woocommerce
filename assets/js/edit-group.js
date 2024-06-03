//Show the Modal ThickBox For Each Edit link
function sms_to_woo_edit_group(group_id, group_name) {
    tb_show(sms_to_woo_edit_group_ajax_vars.tb_show_tag, sms_to_woo_edit_group_ajax_vars.tb_show_url + '&group_id=' + group_id + '&group_name=' + encodeURIComponent(group_name) + '&width=400&height=125');
}