<?php

namespace SFW_SMS_TO_WOO;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // No direct access allowed ;)


/**
 * This plugin is a fork from https://wordpress.org/plugins/wp-sms/ developed by VeronaLabs
 * @author mostafa.s1990, kashani, mehrshaddarzi, alifallahrn, panicoschr10
 * @copyright  2020 VeronaLabs
 * @license    GPLv3
 * @license uri: http://www.gnu.org/licenses/gpl.html
 */
#[\AllowDynamicProperties]
class Settings {

	public $setting_name;
	public $options = array();

	public function __construct() {
		$this->setting_name = 'wpsmstowoo_settings';
		$this->get_settings();
		$this->options = get_option( $this->setting_name );
              
		if ( empty( $this->options ) ) {
			update_option( $this->setting_name, array() );
		}
                
		add_action( 'admin_menu', array( $this, 'add_settings_menu' ), 11 );

		if ( isset( $_GET['page'] ) and sanitize_text_field($_GET['page']) == 'sms-for-woocommerce-settings' or isset( $_POST['option_page'] ) and sanitize_text_field($_POST['option_page']) == 'wpsmstowoo_settings' ) {
			add_action( 'admin_init', array( $this, 'register_settings' ) );
		}
                              
	}

	/**
	 * Add Package admin page settings
	 * */
	public function add_settings_menu() {
		add_submenu_page( 'sms-for-woocommerce', __( 'Settings', 'sms-for-woocommerce' ), __( 'Settings', 'sms-for-woocommerce' ), 'wpsmstowoo_setting', 'sms-for-woocommerce-settings', array(
			$this,
			'render_settings'
		) );
	}

	/**
	 * Gets saved settings from WP core
	 *
	 * @since           2.0
	 * @return          array
	 */
	public function get_settings() {
		$settings = get_option( $this->setting_name );
                
               $url = site_url();
         
		if ( ! $settings ) {
			update_option( $this->setting_name, array(
                            'gateway_wpsmstowoo_sender_id' => 'msgowl',
                            'international_mobile_phone' => 1,
                            'gateway_wpsmstowoo_callback_url' => $url, 
                            'add_mobile_phone_field' => 1,
                            'account_credit_in_menu' => 1,
			) );        
		}
		return apply_filters( 'wpsmstowoo_get_settings', $settings );
	}

	/**
	 * Registers settings in WP core
	 *
	 * @since           2.0
	 * @return          void
	 */
	public function register_settings() {
		if ( false == get_option( $this->setting_name ) ) {
			add_option( $this->setting_name );
		}

		foreach ( $this->get_registered_settings() as $tab => $settings ) {
			add_settings_section(
				'wpsmstowoo_settings_' . $tab,
				__return_null(),
				'__return_false',
				'wpsmstowoo_settings_' . $tab
			);                        
               
			if ( empty( $settings ) ) {
				return;
			}

			foreach ( $settings as $option ) {
				$name = isset( $option['name'] ) ? $option['name'] : '';

				add_settings_field(
					'wpsmstowoo_settings[' . $option['id'] . ']',
					$name,
					array( $this, $option['type'] . '_callback' ),
					'wpsmstowoo_settings_' . $tab,
					'wpsmstowoo_settings_' . $tab,
					array(
						'id'      => isset( $option['id'] ) ? $option['id'] : null,
						'desc'    => ! empty( $option['desc'] ) ? $option['desc'] : '',
						'name'    => isset( $option['name'] ) ? $option['name'] : null,
						'section' => $tab,
						'size'    => isset( $option['size'] ) ? $option['size'] : null,
						'options' => isset( $option['options'] ) ? $option['options'] : '',
						'std'     => isset( $option['std'] ) ? $option['std'] : ''
					)
				);
                        
				register_setting( $this->setting_name, $this->setting_name, array( $this, 'settings_sanitize' ) );
			}
		}
                 }
                 
	/**
	 * Gets settings tabs
	 *
	 * @since               2.0
	 * @return              array Tabs list
	 */
	public function get_tabs() {
		$tabs = array(
			'general'       => __( 'General', 'sms-for-woocommerce' ),
			'gateway'       => __( 'Gateway', 'sms-for-woocommerce' ),
			'feature'       => __( 'Features', 'sms-for-woocommerce' ),
			'integration'   => __( 'WooCommerce', 'sms-for-woocommerce' ),
		);

		return $tabs;
	}

	/**
	 * Sanitizes and saves settings after submit
	 *
	 * @since               2.0
	 *
	 * @param               array $input Settings input
	 *
	 * @return              array New settings
	 */
	public function settings_sanitize( $input = array() ) {            

		if ( empty( $_POST['_wp_http_referer'] ) ) {
			return $input;
		}

		parse_str( sanitize_text_field($_POST['_wp_http_referer']), $referrer );

		$settings = $this->get_registered_settings();
		$tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'wp';

		$input = $input ? $input : array();
		$input = apply_filters( 'wpsmstowoo_settings_' . $tab . '_sanitize', $input );

		// Loop through each setting being saved and pass it through a sanitization filter
		foreach ( $input as $key => $value ) {

			// Get the setting type (checkbox, select, etc)
			$type = isset( $settings[ $tab ][ $key ]['type'] ) ? $settings[ $tab ][ $key ]['type'] : false;

			if ( $type ) {
				// Field type specific filter
				$input[ $key ] = apply_filters( 'wpsmstowoo_settings_sanitize_' . $type, $value, $key );
			}

			// General filter
			$input[ $key ] = apply_filters( 'wpsmstowoo_settings_sanitize', $value, $key );
		}

		// Loop through the whitelist and unset any that are empty for the tab being saved
		if ( ! empty( $settings[ $tab ] ) ) {
			foreach ( $settings[ $tab ] as $key => $value ) {

				// settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
				if ( is_numeric( $key ) ) {
					$key = $value['id'];
				}

				if ( empty( $input[ $key ] ) ) {
					unset( $this->options[ $key ] );
				}

			}
		}

		// Merge our new settings with the existing
		$output = array_merge( $this->options, $input );

		add_settings_error( 'wpsmstowoo-notices', '', __( 'Settings updated', 'sms-for-woocommerce' ), 'updated' );

		return $output;

	}

	/**
	 * Get settings fields
	 *
	 * @since           2.0
	 * @return          array Fields
	 */
	public function get_registered_settings() {

		$options = array(
			'enable'  => __( 'Enable', 'sms-for-woocommerce' ),
			'disable' => __( 'Disable', 'sms-for-woocommerce' )
		);

               
		$settings = apply_filters( 'sms_to_woo_registered_settings', array(
			// General tab
			'general'       => apply_filters( 'sms_to_woo_general_settings', array(
				'admin_title'         => array(
					'id'   => 'admin_title',
					'name' => __( 'Mobile', 'sms-for-woocommerce' ),
					'type' => 'header'
				),                   
				'admin_mobile_phone_number' => array(
					'id'   => 'admin_mobile_phone_number',
					'name' => __( 'Operator mobile phone number', 'sms-for-woocommerce' ),
					'type' => 'international_phone_number',
                                        'desc' => __( 'Operator mobile phone number to get any sms notifications<br>', 'sms-for-woocommerce' )                                    
				)
			) ),

			// Gateway tab
			'gateway'       => apply_filters( 'sms_to_woo_gateway_settings', array(
				// Gateway
				'gayeway_title'             => array(
					'id'   => 'gayeway_title',
					'name' => __( 'Gateway information', 'sms-for-woocommerce' ),
					'type' => 'header'
				),
				'gateway_wpsmstowoo_api_key'          => array(
					'id'   => 'gateway_wpsmstowoo_api_key',
					'name' => __( 'API Key', 'sms-for-woocommerce' ),
					'type' => 'text',
					'desc' => __( 'Enter Api Key', 'sms-for-woocommerce' )
				),                                                        
				'gateway_wpsmstowoo_sender_id'         => array(
					'id'   => 'gateway_wpsmstowoo_sender_id',
					'name' => __( 'Sender', 'sms-for-woocommerce' ),
					'type' => 'text11chars',
					'std'  => SFW_SMS_TO_Gateway::from(),
					'desc' => __( 'Sender number or sender ID - 11 characters max. <br>Can contain only letters digits and spaces.', 'sms-for-woocommerce' )
				),
				'wpsmstowoo_gateway_key'               => array(
					'id'   => 'wpsmstowoo_gateway_key',
					'name' => __( 'API key', 'sms-for-woocommerce' ),
					'type' => 'text',
					'desc' => __( 'Enter API key of gateway', 'sms-for-woocommerce' )
				),
				// Gateway status
				'wpsmstowoo_gateway_status_title'      => array(
					'id'   => 'wpsmstowoo_gateway_status_title',
					'name' => __( 'Gateway status', 'sms-for-woocommerce' ),
					'type' => 'header'
				),
				'account_credit'            => array(
					'id'      => 'account_credit',
					'name'    => __( 'Status', 'sms-for-woocommerce' ),
					'type'    => 'html',
					'options' => SFW_SMS_TO_Gateway::status(),
				),
				'account_response'          => array(
					'id'      => 'account_response',
					'name'    => __( 'Result request', 'sms-for-woocommerce' ),
					'type'    => 'html',
					'options' => SFW_SMS_TO_Gateway::response(),
				),
				'bulk_send'                 => array(
					'id'      => 'bulk_send',
					'name'    => __( 'Bulk send', 'sms-for-woocommerce' ),
					'type'    => 'html',
					'options' => SFW_SMS_TO_Gateway::bulk_status(),
				),
				// Account credit
				'account_credit_title'      => array(
					'id'   => 'account_credit_title',
					'name' => __( 'Account balance', 'sms-for-woocommerce' ),
					'type' => 'header'
				),
				'account_credit_in_menu'    => array(
					'id'      => 'account_credit_in_menu',
					'name'    => __( 'Show in admin menu', 'sms-for-woocommerce' ),
					'type'    => 'checkbox',
					'options' => $options,
					'desc'    => __( 'Show your account credit in admin menu.', 'sms-for-woocommerce' )
				),
			) ),

			
			// Feature tab
			'feature'       => apply_filters( 'sms_to_woo_feature_settings', array(
				'mobile_phone_field'                     => array(
					'id'   => 'mobile_phone_field',
					'name' => __( 'Mobile field', 'sms-for-woocommerce' ),
					'type' => 'header'
				),
				'add_mobile_phone_field'                 => array(
					'id'      => 'add_mobile_phone_field',
					'name'    => __( 'Add Mobile number field', 'sms-for-woocommerce' ),
					'type'    => 'checkbox',
					'options' => $options,
					'desc'    => __( 'Add Mobile number to user profile and register form.', 'sms-for-woocommerce' )
				),
				'international_mobile_phone_title'       => array(
					'id'   => 'international_mobile_phone_title',
					'name' => __( 'International Telephone Input', 'sms-for-woocommerce' ),
					'type' => 'header'
				),
				'international_mobile_phone'             => array(
					'id'      => 'international_mobile_phone',
					'name'    => __( 'Enable for mobile phone fields', 'sms-for-woocommerce' ),
					'type'    => 'checkbox',
					'options' => $options,
					'desc'    => __( 'Adds country code in mobile phone field', 'sms-for-woocommerce' )
				),
				'international_mobile_phone_only_countries'      => array(
					'id'      => 'international_mobile_phone_only_countries',
					'name'    => __( 'Only Countries', 'sms-for-woocommerce' ),
					'type'    => 'countryselect',
					'options' => $this->get_countries_list(),
					'desc'    => __( 'In the dropdown Country select display only the countries you specify.', 'sms-for-woocommerce' )
				),
				'international_mobile_phone_preferred_countries' => array(
					'id'      => 'international_mobile_phone_preferred_countries',
					'name'    => __( 'Prefix Countries', 'sms-for-woocommerce' ),
					'type'    => 'countryselect',
					'options' => $this->get_countries_list(),
					'desc'    => __( 'Specify the countries to appear at the top of the list.', 'sms-for-woocommerce' )
				),
			) ),
			// Notifications tab
			'notifications' => apply_filters( 'sms_to_woo_notifications_settings', array(
				// Publish new post
				'notif_publish_new_post_title'            => array(
					'id'   => 'notif_publish_new_post_title',
					'name' => __( 'Published new posts', 'sms-for-woocommerce' ),
					'type' => 'header'
				),
				'notif_publish_new_post'                  => array(
					'id'      => 'notif_publish_new_post',
					'name'    => __( 'Status', 'sms-for-woocommerce' ),
					'type'    => 'checkbox',
					'options' => $options,
					'desc'    => __( 'Send an SMS to Wordpress subscribers When publish new post.', 'sms-for-woocommerce' )
				),
				'notif_publish_new_post_words_count'     => array(
					'id'      => 'notif_publish_new_post_words_count',
					'name'    => __( 'Post content words count', 'sms-for-woocommerce' ),
					'type'    => 'number',
					'desc'    => __( 'The number of word cropped in Post Content publish notification. Default : 10', 'sms-for-woocommerce' )
				),
				'notif_publish_new_post_template'         => array(
					'id'   => 'notif_publish_new_post_template',
					'name' => __( 'Message body', 'sms-for-woocommerce' ),
					'type' => 'textarea',
					'desc' => __( 'Enter the contents of the sms message.', 'sms-for-woocommerce' ) . '<br>' .
					          sprintf(
						          __( 'Post title: %s, Post content: %s, Post url: %s, Post date: %s', 'sms-for-woocommerce' ),
						          '<code>%post_title%</code>',
						          '<code>%post_content%</code>',
						          '<code>%post_url%</code>',
						          '<code>%post_date%</code>'
					          )
				),
				// Publish new post
				'notif_publish_new_post_author_title'     => array(
					'id'   => 'notif_publish_new_post_author_title',
					'name' => __( 'Author of the post', 'sms-for-woocommerce' ),
					'type' => 'header'
				),
				'notif_publish_new_post_author'           => array(
					'id'      => 'notif_publish_new_post_author',
					'name'    => __( 'Status', 'sms-for-woocommerce' ),
					'type'    => 'checkbox',
					'options' => $options,
					'desc'    => __( 'Send an SMS to the author of the post when that post is published.', 'sms-for-woocommerce' )
				),
				'notif_publish_new_post_author_post_type' => array(
					'id'      => 'notif_publish_new_post_author_post_type',
					'name'    => __( 'Post Types', 'sms-for-woocommerce' ),
					'type'    => 'multiselect',
					'options' => $this->get_list_post_type( array( 'show_ui' => 1 ) ),
					'desc'    => __( 'Select post types that you want to use this option.<br>Must select at least one to enable.', 'sms-for-woocommerce' )
				),
				'notif_publish_new_post_author_template'  => array(
					'id'   => 'notif_publish_new_post_author_template',
					'name' => __( 'Message body', 'sms-for-woocommerce' ),
					'type' => 'textarea',
					'desc' => __( 'Enter the contents of the sms message.', 'sms-for-woocommerce' ) . '<br>' .
					          sprintf(
						          __( 'Post title: %s, Post content: %s, Post url: %s, Post date: %s', 'sms-for-woocommerce' ),
						          '<code>%post_title%</code>',
						          '<code>%post_content%</code>',
						          '<code>%post_url%</code>',
						          '<code>%post_date%</code>'
					          )
				),
				// Publish new wp version
				'notif_publish_new_wpversion_title'       => array(
					'id'   => 'notif_publish_new_wpversion_title',
					'name' => __( 'The new release of WordPress', 'sms-for-woocommerce' ),
					'type' => 'header'
				),
				'notif_publish_new_wpversion'             => array(
					'id'      => 'notif_publish_new_wpversion',
					'name'    => __( 'Status', 'sms-for-woocommerce' ),
					'type'    => 'checkbox',
					'options' => $options,
					'desc'    => __( 'Send an SMS to Operator mobile phone number when a new release of WordPress.', 'sms-for-woocommerce' )
				),
				// Register new user
				'notif_register_new_user_title'           => array(
					'id'   => 'notif_register_new_user_title',
					'name' => __( 'Register a new user', 'sms-for-woocommerce' ),
					'type' => 'header'
				),
				'notif_register_new_user'                 => array(
					'id'      => 'notif_register_new_user',
					'name'    => __( 'Status', 'sms-for-woocommerce' ),
					'type'    => 'checkbox',
					'options' => $options,
					'desc'    => __( 'Send an SMS to Operator mobile phone number and to the user when registers on wordpress.', 'sms-for-woocommerce' )
				),
				'notif_register_new_user_admin_template'  => array(
					'id'   => 'notif_register_new_user_admin_template',
					'name' => __( 'Message body for Operator', 'sms-for-woocommerce' ),
					'type' => 'textarea',
					'desc' => __( 'Enter the contents of the sms message.', 'sms-for-woocommerce' ) . '<br>' .
					          sprintf(
						          __( 'User login: %s, User email: %s, Register date: %s', 'sms-for-woocommerce' ),
						          '<code>%user_login%</code>',
						          '<code>%user_email%</code>',
						          '<code>%date_register%</code>'
					          )
				),
				'notif_register_new_user_template'        => array(
					'id'   => 'notif_register_new_user_template',
					'name' => __( 'Message body for User', 'sms-for-woocommerce' ),
					'type' => 'textarea',
					'desc' => __( 'Enter the contents of the sms message.', 'sms-for-woocommerce' ) . '<br>' .
					          sprintf(
						          __( 'User login: %s, User email: %s, Register date: %s', 'sms-for-woocommerce' ),
						          '<code>%user_login%</code>',
						          '<code>%user_email%</code>',
						          '<code>%date_register%</code>'
					          )
				),
				// New comment
				'notif_new_comment_title'                 => array(
					'id'   => 'notif_new_comment_title',
					'name' => __( 'New comment', 'sms-for-woocommerce' ),
					'type' => 'header'
				),
				'notif_new_comment'                       => array(
					'id'      => 'notif_new_comment',
					'name'    => __( 'Status', 'sms-for-woocommerce' ),
					'type'    => 'checkbox',
					'options' => $options,
					'desc'    => __( 'Send an SMS to Operator mobile phone number when get a new comment.', 'sms-for-woocommerce' )
				),
				'notif_new_comment_template'              => array(
					'id'   => 'notif_new_comment_template',
					'name' => __( 'Message body', 'sms-for-woocommerce' ),
					'type' => 'textarea',
					'desc' => __( 'Enter the contents of the sms message.', 'sms-for-woocommerce' ) . '<br>' .
					          sprintf(
						          __( 'Comment author: %s, Author email: %s, Author url: %s, Author IP: %s, Comment date: %s, Comment content: %s', 'sms-for-woocommerce' ),
						          '<code>%comment_author%</code>',
						          '<code>%comment_author_email%</code>',
						          '<code>%comment_author_url%</code>',
						          '<code>%comment_author_IP%</code>',
						          '<code>%comment_date%</code>',
						          '<code>%comment_content%</code>'
					          )
				),
				// User login
				'notif_user_login_title'                  => array(
					'id'   => 'notif_user_login_title',
					'name' => __( 'User login', 'sms-for-woocommerce' ),
					'type' => 'header'
				),
				'notif_user_login'                        => array(
					'id'      => 'notif_user_login',
					'name'    => __( 'Status', 'sms-for-woocommerce' ),
					'type'    => 'checkbox',
					'options' => $options,
					'desc'    => __( 'Send an SMS to Operator mobile phone number when user is login.', 'sms-for-woocommerce' )
				),
				'notif_user_login_template'               => array(
					'id'   => 'notif_user_login_template',
					'name' => __( 'Message body', 'sms-for-woocommerce' ),
					'type' => 'textarea',
					'desc' => __( 'Enter the contents of the sms message.', 'sms-for-woocommerce' ) . '<br>' .
					          sprintf(
						          __( 'Username: %s, Nickname: %s', 'sms-for-woocommerce' ),
						          '<code>%username_login%</code>',
						          '<code>%display_name%</code>'
					          )
				),
			) ),
			// Integration  tab
			'integration'   => apply_filters( 'sms_to_woo_integration_settings', class_exists( 'WooCommerce' ) ? array(
				// Woocommerce
				'wc_title'                     => array(
					'id'   => 'wc_title',
					'name' => __( 'WooCommerce', 'sms-for-woocommerce' ),
					'type' => 'header'
				),
                            

                             
                            
                                'wc_notify_product'          => array(
					'id'   => 'wc_notify_product',
					'name' => __( 'Notify for new product', 'sms-for-woocommerce' ),
					'type' => 'header'
				),
				'wc_notify_product_enable'   => array(
					'id'      => 'wc_notify_product_enable',
					'name'    => __( 'Status', 'sms-for-woocommerce' ),
					'type'    => 'checkbox',
					'options' => $options,
					'desc'    => __( 'Send SMS to Customers when publish a new product', 'sms-for-woocommerce' )
				),

				'wc_notify_product_message'  => array(
					'id'   => 'wc_notify_product_message',
					'name' => __( 'Message body', 'sms-for-woocommerce' ),
					'type' => 'textarea',
					'desc' => __( 'Enter the contents of the SMS message.', 'sms-for-woocommerce' ) . '<br>' .
					          sprintf(
						          __( 'Product title: %s, Product url: %s, Product date: %s, Product price: %s', 'sms-for-woocommerce' ),
						          '<code>%product_title%</code>',
						          '<code>%product_url%</code>',
						          '<code>%product_date%</code>',
						          '<code>%product_price%</code>'
					          )
				),                            
                                'wc_notify_new_order'          => array(
					'id'   => 'wc_notif_new_order',
					'name' => __( 'Notify Operator for New order', 'sms-for-woocommerce' ),
					'type' => 'header'
				),                            
				'wc_notif_new_order_enable'           => array(
					'id'      => 'wc_notif_new_order_enable',
					'name'    => __( 'Status', 'sms-for-woocommerce' ),
					'type'    => 'checkbox',
					'options' => $options,
					'desc'    => __( 'Send SMS to Operator when New order.', 'sms-for-woocommerce' )
				),
				'wc_notif_new_order_template'  => array(
					'id'   => 'wc_notif_new_order_template',
					'name' => __( 'Message body', 'sms-for-woocommerce' ),
					'type' => 'textarea',
					'desc' => __( 'Enter the contents of the sms message.', 'sms-for-woocommerce' ) . '<br>' .
					          sprintf(
						          __( 'Order ID: %s, Order status: %s', 'sms-for-woocommerce' ),
						          '<code>%order_id%</code>',
						          '<code>%status%</code>'
					          )
				),
                                'wc_notify_order'            => array(
					'id'   => 'wc_notify_order',
					'name' => __( 'Notify for new order', 'sms-for-woocommerce' ),
					'type' => 'header'
				),
				'wc_notify_order_enable'     => array(
					'id'      => 'wc_notify_order_enable',
					'name'    => __( 'Status', 'sms-for-woocommerce' ),
					'type'    => 'checkbox',
					'options' => $options,
					'desc'    => __( 'Send SMS when submit new order', 'sms-for-woocommerce' )
				),
				'wc_notify_order_receiver'   => array(
					'id'   => 'wc_notify_order_receiver',
					'name' => __( 'SMS receiver', 'sms-for-woocommerce' ),
					'type' => 'international_phone_number',
					'desc' => __( 'Please enter mobile phone number.', 'sms-for-woocommerce' )
				),
				'wc_notify_order_message'    => array(
					'id'   => 'wc_notify_order_message',
					'name' => __( 'Message body', 'sms-for-woocommerce' ),
					'type' => 'textarea',
					'desc' => __( 'Enter the contents of the SMS message.', 'sms-for-woocommerce' ) . '<br>' .
					          sprintf(
						          __( 'Billing First Name: %s, Billing Last Name: %s,Billing Phone Number: %s, Order id: %s, Order number: %s, Order Total: %s, Order status: %s', 'sms-for-woocommerce' ),
						          '<code>%billing_first_name%</code>',
                                                          '<code>%billing_last_name%</code>',
						          '<code>%billing_phone%</code>',
						          '<code>%order_id%</code>',
						          '<code>%order_number%</code>',
						          '<code>%order_total%</code>',
						          '<code>%status%</code>'
					          )
				),      
                                'wc_orders'         => array(
					'id'   => 'wc_orders',
					'type' => 'header'
				),                            
                                'wc_mobile_phone_field'            => array(
                                'id'      => 'wc_mobile_phone_field',
                                'name'    => __( 'Orders related Customer Phone Number field', 'sms-for-woocommerce' ),
                                'type'    => 'select',
                                'options' => array(
                                        'disable'            => __( 'Disable (No field)', 'sms-for-woocommerce' ),
                                        'customer_mobile_phone'      => __( 'Customer Profile Phone Number', 'sms-for-woocommerce' ),
                                        'order_mobile_phone' => __( 'Customer Billing Phone Number as on Order', 'sms-for-woocommerce' ),
                                ),
                                'desc'    => __( 'Choose from which field you get Customer Phone Number for sending SMS for orders. (Applies for the orders below)', 'sms-for-woocommerce' )
                                 ),                                
                                'wc_notify_customer'         => array(
					'id'   => 'wc_notify_customer',
					'name' => __( 'Notify to customer order', 'sms-for-woocommerce' ),
					'type' => 'header'
				),
				'wc_notify_customer_enable'  => array(
					'id'      => 'wc_notify_customer_enable',
					'name'    => __( 'Status', 'sms-for-woocommerce' ),
					'type'    => 'checkbox',
					'options' => $options,
					'desc'    => __( 'Send SMS to customer when new order', 'sms-for-woocommerce' )
				),
				'wc_notify_customer_message' => array(
					'id'   => 'wc_notify_customer_message',
					'name' => __( 'Message body', 'sms-for-woocommerce' ),
					'type' => 'textarea',
					'desc' => __( 'Enter the contents of the SMS message.', 'sms-for-woocommerce' ) . '<br>' .
					          sprintf(
						          __( 'Order id: %s, Order number: %s, Order status: %s, Order Total: %s, Customer name: %s, Customer family: %s', 'sms-for-woocommerce' ),
						          '<code>%order_id%</code>',
						          '<code>%order_number%</code>',
						          '<code>%status%</code>',
						          '<code>%order_total%</code>',
						          '<code>%billing_first_name%</code>',
						          '<code>%billing_last_name%</code>'
					          )
				),                            
				'wc_notify_status'           => array(
					'id'   => 'wc_notify_status',
					'name' => __( 'Notify of status', 'sms-for-woocommerce' ),
					'type' => 'header'
				),
				'wc_notify_status_enable'    => array(
					'id'      => 'wc_notify_status_enable',
					'name'    => __( 'Status', 'sms-for-woocommerce' ),
					'type'    => 'checkbox',
					'options' => $options,
					'desc'    => __( 'Send SMS to customer when status is changed', 'sms-for-woocommerce' )
				),
				'wc_notify_status_message'   => array(
					'id'   => 'wc_notify_status_message',
					'name' => __( 'Message body', 'sms-for-woocommerce' ),
					'type' => 'textarea',
					'desc' => __( 'Enter the contents of the SMS message.', 'sms-for-woocommerce' ) . '<br>' .
					          sprintf(
						          __( 'Order number: %s, Order status: %s, Customer name: %s, Customer family: %s', 'sms-for-woocommerce' ),
                                                          '<code>%order_number%</code>',
                                                           '<code>%status%</code>',
						          '<code>%customer_first_name%</code>',
						          '<code>%customer_last_name%</code>'
					          )
				),   
                                'wc_notify_by_status'           => array(
					'id'   => 'wc_notify_by_status',
					'name' => __( 'Notify by status', 'sms-for-woocommerce' ),
					'type' => 'header'
				),
				'wc_notify_by_status_enable'    => array(
					'id'      => 'wc_notify_by_status_enable',
					'name'    => __( 'Status', 'sms-for-woocommerce' ),
					'type'    => 'checkbox',
					'options' => $options,
					'desc'    => __( 'Send SMS by order status', 'sms-for-woocommerce' )
				),
				'wc_notify_by_status_content'    => array(
					'id'      => 'wc_notify_by_status_content',
					'name'    => __( 'Order Status & Message', 'sms-for-woocommerce' ),
					'type'    => 'wpsmstowoorepeater',
					'desc'    => __( 'Add Order Status & Write Message Body Per Order Status', 'sms-for-woocommerce' )
				),                            
			) : array(
				'wc_fields' => array(
					'id'   => 'wc_fields',
					'name' => __( 'Not active', 'sms-for-woocommerce' ),
					'type' => 'notice',
					'desc' => __( 'WooCommerce should be enable to run this tab.', 'sms-for-woocommerce' ),
				) ) ),
		) );

		return $settings;
	}

	public function header_callback( $args ) {
		echo '<hr/>';
	}

	public function html_callback( $args ) {
		echo wp_kses_post($args['options']);
	}

	public function notice_callback( $args ) {
		echo wp_kses_post($args['desc']);
	}

	public function checkbox_callback( $args ) {
		$checked = isset( $this->options[ $args['id'] ] ) ? checked( 1, $this->options[ $args['id'] ], false ) : '';
		$html    = '<input type="checkbox" id="wpsmstowoo_settings[' . $args['id'] . ']" name="wpsmstowoo_settings[' . $args['id'] . ']" value="1" ' . $checked . '/>';
		$html    .= '<label for="wpsmstowoo_settings[' . $args['id'] . ']"> ' . __( 'Active', 'sms-for-woocommerce' ) . '</label>';
		$html    .= '<p class="description"> ' . $args['desc'] . '</p>';

		 echo  wp_kses_normalize_entities($html);
	}





	public function text11chars_callback( $args ) {
		if ( isset( $this->options[ $args['id'] ] ) and $this->options[ $args['id'] ] ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

                $maxlength="11";
       
		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input required type="text" class="' . $size . '-text" id="wpsmstowoo_settings[' . $args['id'] . ']" name="wpsmstowoo_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '" maxlength="' . esc_attr( $maxlength ) . '"/>';
		$html .= '<p class="description"> ' . $args['desc'] . '</p>';

		echo  wp_kses_normalize_entities($html);
	}        
	public function text_callback( $args ) {
		if ( isset( $this->options[ $args['id'] ] ) and $this->options[ $args['id'] ] ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . $size . '-text" id="wpsmstowoo_settings[' . $args['id'] . ']" name="wpsmstowoo_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
		$html .= '<p class="description"> ' . $args['desc'] . '</p>';

		echo  wp_kses_normalize_entities($html);
	}
        
        
        
	public function international_phone_number_callback( $args ) {
   
            echo '<hr/>';
            
		if ( isset( $this->options[ $args['id'] ] ) and $this->options[ $args['id'] ] ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}
                
                

                if ( sms_to_woo_get_option( 'international_mobile_phone' ) ) {
                        $sms_to_woo_input_mobile_phone = " sms-for-woocommerce-input-mobile_phone";
                } else {
                        $sms_to_woo_input_mobile_phone = "";
                }                 

		$html = '<input type="text" class="regular-text' . $sms_to_woo_input_mobile_phone . '" id="wpsmstowoo_settings[' . $args['id'] . ']" name="wpsmstowoo_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
		$html .= '<p class="description"> ' . $args['desc'] . '</p>';

		echo  wp_kses_normalize_entities($html);


                        

	}        

	public function number_callback( $args ) {
            
                $style="width:15%";
                $required="true";
            
		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$max  = isset( $args['max'] ) ? $args['max'] : 999999;
		$min  = isset( $args['min'] ) ? $args['min'] : 1;
		$step = isset( $args['step'] ) ? $args['step'] : 1;

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="number" step="' . esc_attr( $step ) . '" required="' . esc_attr( $required ) . '" style="' . esc_attr( $style ). '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $size . '-text" id="wpsmstowoo_settings[' . $args['id'] . ']" name="wpsmstowoo_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
		$html .= '<p class="description"> ' . $args['desc'] . '</p>';

		echo  wp_kses_normalize_entities($html);
	}

	public function textarea_callback( $args ) {
		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<textarea class="large-text" cols="50" rows="5" id="wpsmstowoo_settings[' . $args['id'] . ']" name="wpsmstowoo_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
		$html .= '<p class="description"> ' . $args['desc'] . '</p>';

		echo wp_kses_post($html);
	}

	public function missing_callback( $args ) {
		echo '&ndash;';

		return false;
	}


	public function select_callback( $args ) {
		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$html = '<select id="wpsmstowoo_settings[' . $args['id'] . ']" name="wpsmstowoo_settings[' . $args['id'] . ']"/>';

		foreach ( $args['options'] as $option => $name ) :
			$selected = selected( $option, $value, false );
			$html     .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
		endforeach;

		$html .= '</select>';
		$html .= '<p class="description"> ' . $args['desc'] . '</p>';

		echo  wp_kses_normalize_entities($html);
	}

	public function multiselect_callback( $args ) {
		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$html     = '<select id="wpsmstowoo_settings[' . $args['id'] . ']" name="wpsmstowoo_settings[' . $args['id'] . '][]" multiple="true" class="chosen-select"/>';
		$selected = '';

		foreach ( $args['options'] as $k => $name ) :
			foreach ( $name as $option => $name ):
				if ( isset( $value ) AND is_array( $value ) ) {
					if ( in_array( $option, $value ) ) {
						$selected = " selected='selected'";
					} else {
						$selected = '';
					}
				}
				$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
			endforeach;
		endforeach;

		$html .= '</select>';
		$html .= '<p class="description"> ' . $args['desc'] . '</p>';

		echo  wp_kses_normalize_entities($html);
	}

	public function countryselect_callback( $args ) {
		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$html     = '<select id="wpsmstowoo_settings[' . $args['id'] . ']" name="wpsmstowoo_settings[' . $args['id'] . '][]" multiple="true" class="chosen-select"/>';
		$selected = '';

		foreach ( $args['options'] as $option => $country ) :
			if ( isset( $value ) AND is_array( $value ) ) {
				if ( in_array( $country['code'], $value ) ) {
					$selected = " selected='selected'";
				} else {
					$selected = '';
				}
			}
			$html .= '<option value="' . $country['code'] . '" ' . $selected . '>' . $country['name'] . '</option>';
		endforeach;

		$html .= '</select>';
		$html .= '<p class="description"> ' . $args['desc'] . '</p>';

		echo  wp_kses_normalize_entities($html);
	}


      
        
	public function wpsmstowoorepeater_callback($args)
	{
		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}
		$order_statuses = wc_get_order_statuses(); 
		ob_start();
		?>
		<div class="wpsmstowoorepeater" id="wpsmstowoorepeater">
			<div data-repeater-list="wpsmstowoo_settings[<?php echo esc_html($args['id']) ?>]">
				<?php if(is_array($value) && count($value)){ ?>
					<?php foreach($value as $data){ ?>
						<?php $order_status = isset($data['order_status']) ? $data['order_status'] : '' ?>
						<?php $notify_status = isset($data['notify_status']) ? $data['notify_status'] : '' ?>
                                                <?php $role = isset($data['role']) ? $data['role'] : '' ?>
						<?php $message = isset($data['message']) ? $data['message'] : '' ?>
						<div class="repeater-item" data-repeater-item>
							<div style="display: block; width: 100%; margin-bottom: 15px; border-bottom: 1px solid #ccc;">
								<div style="display: block; width: 33%; float: left; margin-bottom: 15px;">
									<select name="role" style="display: block; width: 100%;">
										<option value="">- Please Choose -</option>
										<option value="administrator" <?= ($role == 'administrator') ? 'selected' : '' ?>>Administrator(s)</option>
										<option value="shop_manager" <?= ($role == 'shop_manager') ? 'selected' : '' ?>>Shop Manager(s)</option>
                                                                                <option value="customer" <?= ($role == 'customer') ? 'selected' : '' ?>>Customer</option>
									</select>
									<p class="description">Please select role</p>
								</div>                                                                  
								<div style="display: block; width: 33%; float: left; margin-bottom: 15px;">
									<select name="order_status" style="display: block; width: 100%;">
										<option value="">- Please Choose -</option>
										<?php foreach ($order_statuses as $status_key => $status_name) { ?>
											<?php $key = str_replace('wc-', '', $status_key) ?>
											<option value="<?= $key ?>" <?= ($order_status == $key) ? 'selected' : '' ?>><?= $status_name ?></option>
										<?php } ?>
									</select>
									<p class="description">Please choose an order status</p>
								</div>
								<div style="display: block; width: 33%; float: left; margin-bottom: 15px;">
									<select name="notify_status" style="display: block; width: 100%;">
										<option value="">- Please Choose -</option>
										<option value="1" <?= ($notify_status == '1') ? 'selected' : '' ?>>Enable</option>
										<option value="2" <?= ($notify_status == '2') ? 'selected' : '' ?>>Disable</option>
									</select>
									<p class="description">Please select notify status</p>
								</div>
								<div style="display: block; width: 100%; margin-bottom: 15px;">
									<textarea name="message" rows="3" style="display: block; width: 100%;"><?php echo esc_html($message) ?></textarea>
									<p class="description">Enter the contents of the SMS message.</p>
									<p class="description"><?php echo sprintf(__( 'Order status: %s, Order number: %s, Customer name: %s, Customer family: %s', 'sms-for-woocommerce' ), '<code>%status%</code>', '<code>%order_number%</code>', '<code>%billing_first_name%</code>', '<code>%billing_last_name%</code>') ?></p>
								</div>
								<div>
									<input type="button" value="Delete" class="button" style="margin-bottom: 15px;" data-repeater-delete />
								</div>
							</div>
						</div>
					<?php } ?>
				<?php } else { ?>
					<div class="repeater-item" data-repeater-item>
						<div style="display: block; width: 100%; margin-bottom: 15px; border-bottom: 1px solid #ccc;">
                                                        <div style="display: block; width: 33%; float: left; margin-bottom: 15px;">
                                                                <select name="role" style="display: block; width: 100%;">
                                                                        <option value="">- Please Choose -</option>
                                                                        <option value="administrator">Administrator(s)</option>
                                                                        <option value="shop_manager">Shop Manager(s)</option>
                                                                        <option value="customer">Customer</option>
                                                                </select>
                                                                <p class="description">Please select role</p>
                                                        </div>                                                       
							<div style="display: block; width: 33%; float: left; margin-bottom: 15px;">
								<select name="order_status" style="display: block; width: 100%;">
									<option value="">- Please Choose -</option>
									<?php foreach ($order_statuses as $status_key => $status_name) { ?>
										<?php $key = str_replace('wc-', '', $status_key) ?>
										<option value="<?= $key ?>"><?= $status_name ?></option>
									<?php } ?>
								</select>
								<p class="description">Please choose an order status</p>
							</div>   
							<div style="display: block; width: 33%; float: left; margin-bottom: 15px;">
								<select name="notify_status" style="display: block; width: 100%;">
									<option value="">- Please Choose -</option>
									<option value="1">Enable</option>
									<option value="2">Disable</option>
								</select>
								<p class="description">Please select notify status</p>
							</div>
							<div style="display: block; width: 100%; margin-bottom: 15px;">
								<textarea name="message" rows="3" style="display: block; width: 100%;"></textarea>
								<p class="description">Enter the contents of the SMS message.</p>
								<p class="description"><?php echo sprintf(__( 'Order status: %s, Order number: %s, Customer name: %s, Customer family: %s', 'sms-for-woocommerce' ), '<code>%status%</code>', '<code>%order_number%</code>', '<code>%billing_first_name%</code>', '<code>%billing_last_name%</code>') ?></p>
							</div>
							<div>
								<input type="button" value="Delete" class="button" style="margin-bottom: 15px;" data-repeater-delete />
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
			<div style="margin: 10px 0;">
				<input type="button" value="Add another order status" class="button button-primary" data-repeater-create />
			</p>
		</div>
		<?php
		echo  wp_kses_normalize_entities(ob_get_clean());
	}        

        
     
        
	public function render_settings() {           
		$active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $this->get_tabs() ) ? sanitize_text_field($_GET['tab']) : 'general';

		ob_start();
		?>
        <div class="wrap wpsmstowoo-settings-wrap">
			<?php do_action( 'sms_to_woo_settings_page' ); ?>
            <h2><?php _e( 'Settings', 'sms-for-woocommerce' ) ?></h2>
            <div class="wpsmstowoo-tab-group">
                <ul class="wpsmstowoo-tab">
                    <li id="wpsmstowoo-logo" class="wpsmstowoo-logo-group">
                        <img src="<?php echo SFW_SMS_TO_WOO_URL; ?>assets/images/logo.png"/>
                        <p><?php echo sprintf( __( 'MSGOWL for WooCommerce - v%s', 'sms-for-woocommerce' ), SFW_SMS_TO_WOO_VERSION ); ?></p>
						<?php do_action( 'sms_to_woo_after_setting_logo' ); ?>
                    </li>              
					<?php                                    
					foreach ( $this->get_tabs() as $tab_id => $tab_name ) {

						$tab_url = add_query_arg( array(
							'settings-updated' => false,
							'tab'              => $tab_id
						) );

						$active = $active_tab == $tab_id ? 'active' : '';
                                              
						echo '<li><a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="' . $active . '">';
						echo esc_html($tab_name);
						echo '</a></li>';
					}
					?>
                </ul>
				<?php echo esc_html(settings_errors( 'wpsmstowoo-notices' )); ?>
                <div class="wpsmstowoo-tab-content">
                    <form method="post" action="options.php">
                        <table class="form-table">
							<?php
							settings_fields( $this->setting_name );
							do_settings_fields( 'wpsmstowoo_settings_' . $active_tab, 'wpsmstowoo_settings_' . $active_tab );
							?>
                        </table>
						<?php submit_button(); ?>
                    </form>
                </div>
            </div>
        </div>
		<?php
		echo  wp_kses_normalize_entities(ob_get_clean());
	}

	/*
	 * Get list Post Type
	 */
	public function get_list_post_type( $args = array() ) {

		// vars
		$post_types = array();

		// extract special arg
		$exclude   = array();
		$exclude[] = 'attachment';
		$exclude[] = 'acf-field'; //Advance custom field
		$exclude[] = 'acf-field-group'; //Advance custom field Group
		$exclude[] = 'vc4_templates'; //Visual composer
		$exclude[] = 'vc_grid_item'; //Visual composer Grid
		$exclude[] = 'acf'; //Advance custom field Basic
		$exclude[] = 'wpcf7_contact_form'; //contact 7 Post Type
		$exclude[] = 'shop_order'; //WooCommerce Shop Order
		$exclude[] = 'shop_coupon'; //WooCommerce Shop coupon

		// get post type objects
		$objects = get_post_types( $args, 'objects' );
		foreach ( $objects as $k => $object ) {
			if ( in_array( $k, $exclude ) ) {
				continue;
			}
			if ( $object->_builtin && ! $object->public ) {
				continue;
			}
			$post_types[] = array( $object->cap->publish_posts . '|' . $object->name => $object->label );
		}

		// return
		return $post_types;
	}

	/**
	 * Get countries list
	 *
	 * @return array|mixed|object
	 */
	public function get_countries_list() {
		// Load countries list file
		$file   = SFW_SMS_TO_WOO_DIR . 'assets/countries.json';
		$file   = file_get_contents( $file );
		$result = json_decode( $file, true );

		return $result;
	}
        
       
        
}

new Settings();