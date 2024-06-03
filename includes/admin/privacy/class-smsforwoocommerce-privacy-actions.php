<?php

namespace SFW_SMS_TO_WOO;

use SFW_SMS_TO_WOO\Admin\SFW_SMS_TO_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Class Privacy
 * This plugin is a fork from https://wordpress.org/plugins/wp-sms/ developed by VeronaLabs
 * @author mostafa.s1990, kashani, mehrshaddarzi, alifallahrn
 * @copyright  2020 VeronaLabs
 * @license    GPLv3
 * @license uri: http://www.gnu.org/licenses/gpl.html
 */
#[\AllowDynamicProperties]
class SFW_SMS_TO_Privacy_Actions {

	public $options;
	public $metabox = 'privacy_metabox_general';

	public function __construct() {
		add_filter( 'screen_layout_columns', array( $this, 'on_screen_layout_columns' ), 10, 2 );
		add_action( 'admin_post_save_' . $this->metabox, array( $this, 'on_save_changes_metabox' ) );
		add_action( 'admin_notices', array( $this, 'admin_notification' ) );
		add_action( 'admin_init', array( $this, 'process_form' ) );
	}

	/*
	 * Set Screen layout columns
	 */
	function on_screen_layout_columns( $columns, $screen ) {
		if ( strpos( $screen, 'sms-for-woocommerce-subscribers-privacy' ) !== false ) {
			$columns[ $screen ] = 2;
		}

		return $columns;
	}

	/*
	 * Save Change Meta Box
	 */
	function on_save_changes_metabox() {
		//user permission check
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}
		check_admin_referer( $this->metabox );
		wp_redirect( $_POST['_wp_http_referer'] );
	}

	/**
	 * Show Admin Notification
	 *
	 * @param Not param
	 */
	public function admin_notification() {
		global $pagenow;

		/*
		 * privacy Page
		 */
		if ( $pagenow == "admin.php" and isset( $_GET['page'] ) AND sanitize_text_field($_GET['page']) == "sms-for-woocommerce-subscribers-privacy" ) {

			if ( isset( $_GET['error'] ) ) {
				/*
				 *  Empty Mobile Number
				 */
				if ( sanitize_text_field($_GET['error']) == "empty_number" ) {
					SFW_SMS_TO_Helper::notice( __( 'Please enter the mobile phone number', 'sms-for-woocommerce' ), "error" );
				}

				/*
				*  Not found User
				 */
				if ( sanitize_text_field($_GET['error']) == "not_found" ) {
					SFW_SMS_TO_Helper::notice( __( 'User with this mobile phone number was not found', 'sms-for-woocommerce' ), "error" );
				}
			}

			/*
			 * Success Mobile Number
			 */
			if ( isset( $_GET['delete_mobile_phone'] ) ) {
				SFW_SMS_TO_Helper::notice( sprintf( __( 'Mobile phone number %s is removed completely', 'sms-for-woocommerce' ), trim( sanitize_text_field($_GET['delete_mobile_phone']) ) ), "success" );
			}

		}
	}

	/*
	 * Process Privacy Form
	 *
	 */
	public function process_form() {
		if ( isset( $_POST['sms_to_woo_nonce_privacy'] ) and isset( $_POST['submit'] ) and ( isset( $_POST['mobile_phone-number-delete'] ) || isset( $_POST['mobile_phone-number-export'] ) ) ) {
			if ( wp_verify_nonce( sanitize_text_field($_POST['sms_to_woo_nonce_privacy']), 'sms_to_woo_nonce_privacy' ) ) {

				$mobile_phone = ( sanitize_text_field($_POST['submit']) == __( 'Export' ) ? sanitize_text_field( $_POST['mobile_phone-number-export'] ) : sanitize_text_field( $_POST['mobile_phone-number-delete'] ) );

				//Is Empty Mobile Number
				$this->check_empty_mobile_phone( $mobile_phone );

				//Check User Not Exist
				$user_data = $this->check_user_exist_mobile_phone( $mobile_phone );

				/*
				 * Export Area
				 */
				if ( sanitize_text_field($_POST['submit']) == __( 'Export' ) ) {
					$this->create_csv( $user_data, "sms-for-woocommerce-report-" . $mobile_phone );
				}

				/*
				 * Delete Area
				 */
				if ( sanitize_text_field($_POST['submit']) == __( 'Delete' ) ) {
					wp_redirect( admin_url( add_query_arg( array( 'page' => 'sms-for-woocommerce-subscribers-privacy', 'delete_mobile_phone' => $mobile_phone ), 'admin.php' ) ) );
					exit;
				}
			}
		}
	}


	/**
	 * Check Mobile Number is Empty
	 *
	 * @param $mobile_phone Mobile Number
	 */
	public function check_empty_mobile_phone( $mobile_phone ) {
		if ( empty( $mobile_phone ) ) {
			wp_redirect( admin_url( add_query_arg( array( 'page' => 'sms-for-woocommerce-subscribers-privacy', 'error' => 'empty_number' ), 'admin.php' ) ) );
			exit;
		}
	}


    /**
     * Get user by phone
     * @author Christodoulou Panikos
     * @email christodoulou.panicos@cytanet.com.cy
     */
    function sfw_get_users_with_phone($key_item) {

        $users = [];
        $mobile_phone_posted = isset($key_item) ? trim(sanitize_text_field($key_item)) : "";
        $mobile_phone = str_replace(' ', '', $mobile_phone_posted);
        if (($mobile_phone)) {
            $user = sfw_sms_to_get_user_by_mobile_phone('mobile_phone', $mobile_phone);
            $users[0] = $user;
        }
        return $users;
    }   
    
    
    /**
	 * Check Exist User By Mobile A
	 */
	public function check_user_exist_mobile_phone( $mobile_phone ) {
		global $wpdb;
		$result = array();

		/*
		 * Check in Wordpress User
		 */
                $get_user = $this->sfw_get_users_with_phone($mobile_phone);
                
		if (( count( $get_user ) > 0 ) && ($get_user[0] != NULL)) {
			foreach ( $get_user as $user ) {
				//Get User Data
				$result[] = array( "FullName" => $user->first_name . " " . $user->last_name, "Mobile" => $user->mobile_phone, "RegisterDate" => $user->user_registered );

				//Remove User data if Delete Request
				if ( sanitize_text_field($_POST['submit']) == __( 'Delete' ) ) {
					delete_user_meta( $user->ID, 'mobile_phone' );
				}
			}
		}

		if ( empty( $result ) ) {
			wp_redirect( admin_url( add_query_arg( array( 'page' => 'sms-for-woocommerce-subscribers-privacy', 'error' => 'not_found' ), 'admin.php' ) ) );
			exit;
		}

		return $result;
	}


	/**
	 * Check Exist User With Mobile Meta data
	 *
	 * @param array $data Mobile Number
	 * @param string $filename File Name
	 *
	 * @return string export Force Download Csv File
	 */
	public function create_csv( $data, $filename ) {
		$filepath = $_SERVER["DOCUMENT_ROOT"] . $filename . '.csv';
		$fp       = fopen( $filepath, 'w+' );

		$i = 0;
		foreach ( $data as $fields ) {
			if ( $i == 0 ) {
				fputcsv( $fp, array_keys( $fields ) );
			}
			fputcsv( $fp, array_values( $fields ) );
			$i ++;
		}
		header( 'Content-Type: application/octet-stream; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '.csv"' );
		header( 'Content-Length: ' . filesize( $filepath ) );
		echo file_get_contents( esc_html($filepath) );
		exit;
	}

}

new SFW_SMS_TO_Privacy_Actions();