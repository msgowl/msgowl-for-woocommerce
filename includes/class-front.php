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
#[\AllowDynamicProperties]
class SFW_SMS_TO_Front {

	public function __construct() {

		$this->options = SFW_SMS_TO_Option::getOptions();

		// Load assets
		add_action( 'wp_enqueue_scripts', array( $this, 'front_assets' ) );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar' ) );
	}

	/**
	 * Include front table
	 *
	 * @param  Not param
	 */
	public function front_assets() {

		//Register admin-bar.css for whole admin area
		wp_register_style( 'wpsmstowoo-admin-bar', SFW_SMS_TO_WOO_URL . 'assets/css/admin-bar.css', true, SFW_SMS_TO_WOO_VERSION );
		wp_enqueue_style( 'wpsmstowoo-admin-bar' );

		// Check if "Disable Style" in frontend is active or not
		if ( empty( $this->options['disable_style_in_front'] ) or ( isset( $this->options['disable_style_in_front'] ) and ! $this->options['disable_style_in_front'] ) ) {
			wp_register_style( 'wpsmstowoo-subscribe', SFW_SMS_TO_WOO_URL . 'assets/css/subscribe.css', true, SFW_SMS_TO_WOO_VERSION );
			wp_enqueue_style( 'wpsmstowoo-subscribe' );
		}
	}

	/**
	 * Admin bar plugin
	 */
	public function admin_bar() {
		global $wp_admin_bar;
		if ( is_super_admin() && is_admin_bar_showing() ) {
			$credit = get_option( 'wpsmstowoo_gateway_credit' );
			if ( $credit AND isset( $this->options['account_credit_in_menu'] ) AND ! is_object( $credit ) ) {
                                $wp_admin_bar->add_node(
                                    array(
                                        'id' => 'wp-credit-sms',
                                        'parent' => 'top-secondary',
                                        'title' => '<span class="ab-icon"></span>' . $credit,
                                        'href' => SFW_SMS_TO_WOO_ADMIN_URL . '/admin.php?page=sms-for-woocommerce-settings&tab=gateway'
                                    )
                                );
			}
		}

		$wp_admin_bar->add_menu( array(
			'id'     => 'wp-send-sms',
			'parent' => 'new-content',
			'title'  => __( 'SMSTO', 'sms-for-woocommerce' ),
			'href'   => SFW_SMS_TO_WOO_ADMIN_URL . '/admin.php?page=sms-for-woocommerce'
		) );
	}
}

new SFW_SMS_TO_Front();