<?php

namespace SFW_SMS_TO_WOO;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * This plugin is a fork from https://wordpress.org/plugins/wp-sms/ developed by VeronaLabs
 * @author mostafa.s1990, kashani, mehrshaddarzi, alifallahrn, panicoschr10
 * @copyright  2020 VeronaLabs
 * @license    GPLv3
 * @license uri: http://www.gnu.org/licenses/gpl.html
 */
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
 
$option_name = 'wpsmstowoo_settings';
 
delete_option($option_name);

$option_name = 'sms_pp_settings';

delete_option($option_name); 
 
// drop a custom database table
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}msgowl_woo_send");