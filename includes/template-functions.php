<?php

use SFW_SMS_TO_WOO\SFW_SMS_TO_Option;

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

/**
 * Get option value.
 *
 * @param $option_name
 * @param bool $pro
 * @param string $setting_name
 *
 * @return string
 */
function sms_to_woo_get_option( $option_name, $pro = false, $setting_name = '' ) {
	return SFW_SMS_TO_Option::getOption( $option_name, $pro, $setting_name );
}

/**
 * Send SMS.
 *
 * @param array $to
 * @param $msg $pro
 * @param bool $is_flash
 *
 * @param bool $from
 *
 * @return string | WP_Error
 */
function sms_to_woo_send( $to, $msg, $is_flash = false, $from = null ) {
	global $wpsmstowoo;

	$wpsmstowoo->isflash = $is_flash;
	$wpsmstowoo->to      = $to;
	$wpsmstowoo->msg     = $msg;

	if ( $from ) {
		$wpsmstowoo->from = $from;
	}

	return $wpsmstowoo->SendSMS();
}