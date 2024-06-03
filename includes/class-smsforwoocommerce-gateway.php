<?php

namespace SFW_SMS_TO_WOO;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * SMS_TO_WOO gateway class
 * This plugin is a fork from https://wordpress.org/plugins/wp-sms/ developed by VeronaLabs
 * @author mostafa.s1990, kashani, mehrshaddarzi, alifallahrn, panicoschr10
 * @copyright  2020 VeronaLabs
 * @license    GPLv3
 * @license uri: http://www.gnu.org/licenses/gpl.html
 */
#[\AllowDynamicProperties]
class SFW_SMS_TO_Gateway {

	public $client_id;
	public $secret;
    public $api_key;
	public $has_key = false;
	public $validateNumber = "";
	public $help = false;
	public $bulk_send = true;
	public $from;
	public $to;
	public $campaign_recipients;        
	public $_id;
	public $msg;
	protected $db;
	protected $tb_prefix;
	public $options;

	public function __construct() {
		global $wpdb;

		$this->db        = $wpdb;
		$this->tb_prefix = $wpdb->prefix;
		$this->options   = SFW_SMS_TO_Option::getOptions();               

		if ( isset( $this->options['send_unicode'] ) and $this->options['send_unicode'] ) {
			//add_filter( 'sms_to_woo_msg', array( $this, 'applyUnicode' ) );
		}

		// Add Filters
		add_filter( 'sms_to_woo_to', array( $this, 'modify_bulk_send' ) );
	}

	/**
	 * Initial Gateway
	 *
	 * @return mixed
	 */
	public static function initial() {
		
		// Include gateway
		include_once SFW_SMS_TO_WOO_DIR . 'includes/gateways/class-smsforwoocommerce-gateway-wpsmstowoo.php';
                $gateway_name = 'wpsmstowoo'; 
                $class_name = '\\SFW_SMS_TO_WOO\\Gateway\\' . $gateway_name;
                $wpsmstowoo        = new $class_name();
                        
		// Set client_id and secret
                $wpsmstowoo->api_key = SFW_SMS_TO_Option::getOption( 'gateway_wpsmstowoo_api_key' );
                $wpsmstowoo->sender_id = SFW_SMS_TO_Option::getOption( 'gateway_wpsmstowoo_sender_id' );
               

		$gateway_key = SFW_SMS_TO_Option::getOption( 'wpsmstowoo_gateway_key' );

		// Set api key
		if ( $wpsmstowoo->has_key && $gateway_key ) {
			$wpsmstowoo->has_key = $gateway_key;
		}

		// Show gateway help configuration in gateway page
		if ( $wpsmstowoo->help ) {
			add_action( 'sms_to_woo_after_gateway', function () {
				echo ' < p class="description" > ' . esc_html($wpsmstowoo->help) . '</p > ';
			} );
		}

		// Check unit credit gateway
		if ( $wpsmstowoo->unitrial == true ) {
			$wpsmstowoo->unit = __( 'Credit', 'wp - sms' );
		} else {
			$wpsmstowoo->unit = __( 'SMS', 'wp - sms' );
		}

		// Set sender id
		if ( ! $wpsmstowoo->from ) {
			$wpsmstowoo->from = SFW_SMS_TO_Option::getOption( 'gateway_wpsmstowoo_sender_id' );
		}

		// Unset gateway key field if not available in the current gateway class.
		add_filter( 'sms_to_woo_gateway_settings', function ( $filter ) {
			global $wpsmstowoo;

			if ( ! $wpsmstowoo->has_key ) {
				unset( $filter['wpsmstowoo_gateway_key'] );
			}

			return $filter;
		} );
           
		// Return gateway object
		return $wpsmstowoo;                
	}

	/**
	 * @param $sender
	 * @param $message
	 * @param $to
	 * @param $response
	 * @param string $status
	 *
	 * @return false|int
	 */
	public function log_message($_id,  $sender, $message, $to, $response, $status = 'ONGOING' ) { 
		return $this->db->insert(
			$this->tb_prefix . "msgowl_woo_send",
			array(
                                '_id'      => $_id,
				'date'      => SFW_SMS_TO_WOO_CURRENT_DATE,
				'sender'    => $sender,
				'message'   => $message,
				'recipient' => implode( ',', $to ),
				'response'  => 'message_id '.var_export( $response, true ),
				'status'    => $status,
			)
		);
	}        

	/**
	 * Apply Unicode for non-English characters
	 *
	 * @param string $msg
	 *
	 * @return string
	 */
	public function applyUnicode( $msg = '' ) {
		$encodedMessage = bin2hex( mb_convert_encoding( $msg, 'utf-16', 'utf-8' ) );

		return $encodedMessage;
	}

	/**
	 * @var
	 */
	static $get_response;

	/**
	 * @return mixed|void
	 */
	public static function gateway() {
		$gateways = array(
			''               => array(
				'default' => __( 'Please select your gateway', 'wpsmstowoo' ),
			),
			'cyprus'         => array(
				'websmscy' => 'websms.com.cy',
				'smsnetgr' => 'sms.net.gr',
                'wpmsgowlwoo' => 'msgowl.com',
			),
		
		);

		return apply_filters( 'wpsmstowoo_gateway_list', $gateways );
	}

	/**
	 * @return string
	 */
	public static function status() {
		global $wpsmstowoo;

		//Check that, Are we in the Gateway SMS_TO_WOO tab setting page or not?
		if ( is_admin() AND isset( $_REQUEST['page'] ) AND isset( $_REQUEST['tab'] ) AND sanitize_text_field($_REQUEST['page']) == 'sms-for-woocommerce-settings' AND sanitize_text_field($_REQUEST['tab'] == 'gateway' )) {

			// Get credit
			$result = $wpsmstowoo->GetCredit();

			if ( is_wp_error( $result ) ) {
				// Set error message
				self::$get_response = var_export( $result->get_error_message(), true );

				// Update credit
				update_option( 'wpsmstowoo_gateway_credit', 0 );

				// Return html
				return '<div class="wpsmstowoo-no-credit"><span class="dashicons dashicons-no"></span> ' . __( 'Deactive!', 'sms-for-woocommerce' ) . '</div>';
			}
			// Update credit
			if ( ! is_object( $result ) ) {
				update_option( 'wpsmstowoo_gateway_credit', $result );
			}
			self::$get_response = var_export( $result, true );

			// Return html
			return '<div class="wpsmstowoo-has-credit"><span class="dashicons dashicons-yes"></span> ' . __( 'Active!', 'sms-for-woocommerce' ) . '</div>';
		}
	}

	/**
	 * @return mixed
	 */
	public static function response() {
		return self::$get_response;
	}

	/**
	 * @return mixed
	 */
	public static function help() {
		global $wpsmstowoo;

		// Get gateway help
		return $wpsmstowoo->help;
	}

	/**
	 * @return mixed
	 */
	public static function from() {
		global $wpsmstowoo;
		// Get gateway from
		return $wpsmstowoo->from;
	}

	/**
	 * @return string
	 */
	public static function bulk_status() {
		global $wpsmstowoo;

		// Get bulk status
		if ( $wpsmstowoo->bulk_send == true ) {
			// Return html
			return '<div class="wpsmstowoo-has-credit"><span class="dashicons dashicons-yes"></span> ' . __( 'Supported', 'sms-for-woocommerce' ) . '</div>';
		} else {
			// Return html
			return '<div class="wpsmstowoo-no-credit"><span class="dashicons dashicons-no"></span> ' . __( 'Does not support!', 'sms-for-woocommerce' ) . '</div>';
		}
	}

	/**
	 * @return int
	 */
	public static function credit() {
		global $wpsmstowoo;

		// Get credit
		$result = $wpsmstowoo->GetCredit();

		if ( is_wp_error( $result ) ) {
			update_option( 'wpsmstowoo_gateway_credit', 0 );

			return 0;
		}

		if ( ! is_object( $result ) ) {
			update_option( 'wpsmstowoo_gateway_credit', $result );
		}

		return $result;
	}

	/**
	 * Modify destination number
	 *
	 * @param array $to
	 *
	 * @return array/string
	 */
	public function modify_bulk_send( $to ) {
		global $wpsmstowoo;
		if ( ! $wpsmstowoo->bulk_send ) {
			return array( $to[0] );
		}

		return $to;
	}

}
