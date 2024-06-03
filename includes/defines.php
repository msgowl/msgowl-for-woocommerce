<?php

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
 * Check get_plugin_data function exist
 */
if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

// Set Plugin path and url defines.
define( 'SFW_SMS_TO_WOO_URL', plugin_dir_url( dirname( __FILE__ ) ) );
define( 'SFW_SMS_TO_WOO_DIR', plugin_dir_path( dirname( __FILE__ ) ) );


// Get plugin Data.
$plugin_data = get_plugin_data( SFW_SMS_TO_WOO_DIR . 'msgowl-for-woocommerce.php' );

// Set another useful Plugin defines.
define( 'SFW_SMS_TO_WOO_VERSION', $plugin_data['Version'] );
define( 'SFW_SMS_TO_WOO_ADMIN_URL', get_admin_url() );
define( 'SFW_SMS_TO_WOO_CURRENT_DATE', date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) );