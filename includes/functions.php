<?php

use SFW_SMS_TO_WOO\SFW_SMS_TO_Gateway;
use SFW_SMS_TO_WOO\SFW_SMS_TO_Option;

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/**
 * This plugin is a fork from https://wordpress.org/plugins/wp-sms/ developed by VeronaLabs
 * @author mostafa.s1990, kashani, mehrshaddarzi, alifallahrn, panicoschr10
 * @copyright  2020 VeronaLabs
 * @license    GPLv3
 * @license uri: http://www.gnu.org/licenses/gpl.html
 */

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * @return mixed
 */
function sfc_sms_to_woo_initial_gateway() {
    require_once SFW_SMS_TO_WOO_DIR . 'includes/class-smsforwoocommerce-option.php';

    return SFW_SMS_TO_Gateway::initial();
}

add_action('rest_api_init', 'sfw_sms_to_register_route');

add_action('wp_ajax_sfw_get_updates_from_db_woo', 'sfw_get_updates_from_db_woo');
add_action('wp_ajax_nopriv_sfw_get_updates_from_db_woo', 'sfw_get_updates_from_db_woo');

require_once SFW_SMS_TO_WOO_DIR . 'includes/class-smsforwoocommerce-option.php';

if (isset(SFW_SMS_TO_Option::getOptions($pro = true, $setting_name = 'wpsmstowoo_settings')['international_mobile_phone'])) {
    //if woocommerce is installed and active
    if (is_plugin_active('woocommerce/woocommerce.php')) {
        add_filter('woocommerce_billing_fields', 'sfw_sms_to_custom_override_checkout_fields');
    }
}

/**
 * Override WooCommerce Checkout Fields
 * to set the class for mobile phone
 * @author Christodoulou Panikos
 * @email christodoulou.panicos@cytanet.com.cy
 * return billing fields
 */
function sfw_sms_to_custom_override_checkout_fields($fields) {
    require_once SFW_SMS_TO_WOO_DIR . 'includes/class-smsforwoocommerce-option.php';
    if (isset(SFW_SMS_TO_Option::getOptions($pro = true, $setting_name = 'wpsmstowoo_settings')['international_mobile_phone'])) {
        $sms_to_woo_input_mobile_phone = ' sms-for-woocommerce-input-mobile_phone';
    } else {
        $sms_to_woo_input_mobile_phone = '';
    }
    $fields['billing_phone']['input_class'][] = 'regular-text' . $sms_to_woo_input_mobile_phone;
    $fields['billing_phone']['type'] = 'text';
    return $fields;
}

/**
 * Register Route
 * 
 * @author Christodoulou Panikos
 * @email christodoulou.panicos@cytanet.com.cy
 */
function sfw_sms_to_register_route() {
    register_rest_route('sms-for-woocommerce', 'get_post', array(
        'methods' => 'POST',
        'callback' => 'sfw_sms_to_update_db',
        'permission_callback' => '__return_true'
    ));
}

/**
 * Update DB
 * 
 * @author Christodoulou Panikos
 * @email christodoulou.panicos@cytanet.com.cy
 * @param WP_REST_Request
 * @return Response
 */
function sfw_sms_to_update_db(WP_REST_Request $request) {

    global $wpdb;
    $table = $wpdb->prefix . 'msgowl_woo_send';
    //parameters is an array
    $parameters = $request->get_params();

    if ((isset($parameters['status'])) &&
            (isset($parameters['messageId'])) &&
            (isset($parameters['phone'])) &&
            (isset($parameters['trackingId'])) &&
            (isset($parameters['price']))) {

        $status = $parameters['status'];
        $messageId = $parameters['messageId'];
        $trackingId = $parameters['trackingId'];
        $price = $parameters['price'];

        $wpdb->query("UPDATE $table SET status='$status', cost='$price', type='sms' WHERE response Like '%$messageId%' and response Like '%message_id%'");
        $wpdb->query("UPDATE $table SET status='$status' WHERE response Like '%$trackingId%' and response Like '%campaign_id%'");
    }
}


/**
 * Update DB
 * 
 * @author Christodoulou Panikos
 * @email christodoulou.panicos@cytanet.com.cy
 * @param WP_REST_Request
 * @return Response
 */
function sfw_get_updates_from_db_woo() {

    global $wpdb;

    //update campaign records in DB
    \SFW_SMS_TO_WOO\Admin\SFW_SMS_TO_Helper::sfw_sms_to_update_db();

    //get last 15 mins interval
    $time_interval_update = date('Y-m-d H:i:s', current_time('timestamp') - (60 * 15));
    $time_interval_create = date('Y-m-d H:i:s', current_time('timestamp') - (60 * 60 * 24 * 3));

    $get_latest_updated_data = $wpdb->get_results(
            $wpdb->prepare(
                    "SELECT _id, cost, status, type, failed, sms_parts, pending, sent FROM $wpdb->prefix" . "msgowl_woo_send WHERE "
                    . "(updated_at > %s or updated_at is null) and date > %d LIMIT 1000",
                    $time_interval_update, $time_interval_create
            ), ARRAY_A
    );

    $response_init = array();
    $response_init['latest_updated_data'] = json_encode($get_latest_updated_data);
    $response = array_map('sanitize_text_field', $response_init);
    echo  wp_kses_post($response['latest_updated_data']);
    exit;
}
/**
 * Retrieve User mobile phone from wp_usermeta 
 * 
 * @author Christodoulou Panikos
 * @email christodoulou.panicos@cytanet.com.cy
 * @param mobile_phone, mobile_phone number
 * @return User
 */
function sfw_sms_to_get_user_by_mobile_phone($db_field, $value) {
    global $wpdb;

    $user_id = $wpdb->get_row(
            $wpdb->prepare(
                    "SELECT user_id FROM $wpdb->prefix" . "usermeta WHERE meta_key = %s
				 AND REPLACE(meta_value, ' ', '') = %d LIMIT 1",
                    $db_field, $value
            )
    );

    if ($user_id) {
        $array = json_decode(json_encode($user_id), true);
        $user = get_user_by('ID', $array ["user_id"]);
        return $user;
    } else {
        return null;
    }
}


if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
   add_action('init', 'sfw_sms_to_update_credit_balance');
}
 

/**
 * Update Credit Balance only if in admin
 * Will run on every page load if it is set to be displayed in admin menu
 * @author Christodoulou Panikos
 * @email christodoulou.panicos@cytanet.com.cy
 */
function sfw_sms_to_update_credit_balance() {

    $options = \SFW_SMS_TO_WOO\SFW_SMS_TO_Option::getOptions();
    if (isset($options['account_credit_in_menu'])) {
        $api_key = isset($options['gateway_wpsmstowoo_api_key']) ? $options['gateway_wpsmstowoo_api_key'] : '';
        $existing_credit = get_option('wpsmstowoo_gateway_credit');
        if (isset($existing_credit) && isset($api_key)) {
            $response = wp_remote_get(\SFW_SMS_TO_WOO\Gateway\wpsmstowoo::getTariff() . '/api/balance?api_key=' . $api_key);
            $body = json_decode(wp_remote_retrieve_body($response));

            if ((200 == wp_remote_retrieve_response_code($response)) && (!is_wp_error($response))) {
                $result = round($body->balance, 2) . ' ' . $body->currency;
                if ($result != $existing_credit) {
                    update_option('wpsmstowoo_gateway_credit', $result);
                }
            }
        }
    }
}


