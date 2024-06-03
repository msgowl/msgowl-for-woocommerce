<?php

namespace SFW_SMS_TO_WOO;

/**
 * This plugin is a fork from https://wordpress.org/plugins/wp-sms/ developed by VeronaLabs
 * @author mostafa.s1990, kashani, mehrshaddarzi, alifallahrn, panicoschr10
 * @copyright  2020 VeronaLabs
 * @license    GPLv3
 * @license uri: http://www.gnu.org/licenses/gpl.html
 */
#[\AllowDynamicProperties]
class SFW_SMS_TO_Admin {

	public $wpsmstowoo;
	protected $db;
	protected $tb_prefix;
	protected $options;

	public function __construct() {
		global $wpdb;

		$this->db        = $wpdb;
		$this->tb_prefix = $wpdb->prefix;
		$this->options   = SFW_SMS_TO_Option::getOptions();
		$this->init();

		// Add Actions
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar' ) );
		
                //check if any other SMSTO plugin has already added the action
                $hook_name = 'dashboard_glance_items';
                $search_value = 'dashboard_smsto_glance';
                global $wp_filter;

                if (isset($wp_filter[$hook_name])) {
                    $encoded_hook_name = json_encode($wp_filter[$hook_name]);
                    $no_of_occurances = substr_count($encoded_hook_name, $search_value);
                    if ($no_of_occurances == 0) {
                        add_action('dashboard_glance_items', array($this, 'dashboard_smsto_glance'));
                    }
                } else {
                    add_action('dashboard_glance_items', array($this, 'dashboard_smsto_glance'));
                }             
                
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
                
		// Add Filters
		add_filter( 'plugin_row_meta', array( $this, 'meta_links' ), 0, 2 );
	}

	/**
	 * Include admin assets
	 */
	public function admin_assets() {
            
            
                wp_register_style( 'wpsmstowoo-documentation_style', SFW_SMS_TO_WOO_URL . 'assets/css/documentation_style.min.css', true, SFW_SMS_TO_WOO_VERSION );
		wp_enqueue_style( 'wpsmstowoo-documentation_style' );
                wp_enqueue_script( 'wpsmstowoo-documentation_settings', SFW_SMS_TO_WOO_URL . 'assets/js/documentation_settings.min.js', true, SFW_SMS_TO_WOO_VERSION );
            
		// Register admin-bar.css for whole admin area
		wp_register_style( 'wpsmstowoo-admin-bar', SFW_SMS_TO_WOO_URL . 'assets/css/admin-bar.css', true, SFW_SMS_TO_WOO_VERSION );
		wp_enqueue_style( 'wpsmstowoo-admin-bar' );

		if ( stristr( get_current_screen()->id, "sms-for-woocommerce" ) ) {
			wp_register_style( 'wpsmstowoo-admin', SFW_SMS_TO_WOO_URL . 'assets/css/admin.css', true, SFW_SMS_TO_WOO_VERSION );
			wp_enqueue_style( 'wpsmstowoo-admin' );
			if ( is_rtl() ) {
				wp_enqueue_style( 'wpsmstowoo-rtl', SFW_SMS_TO_WOO_URL . 'assets/css/rtl.css', true, SFW_SMS_TO_WOO_VERSION );
			}

			wp_enqueue_style( 'wpsmstowoo-chosen', SFW_SMS_TO_WOO_URL . 'assets/css/chosen.min.css', true, SFW_SMS_TO_WOO_VERSION );
			wp_enqueue_script( 'wpsmstowoo-chosen', SFW_SMS_TO_WOO_URL . 'assets/js/chosen.jquery.min.js', true, SFW_SMS_TO_WOO_VERSION );
			wp_enqueue_script( 'wpsmstowoo-word-and-character-counter', SFW_SMS_TO_WOO_URL . 'assets/js/jquery.word-and-character-counter.min.js', true, SFW_SMS_TO_WOO_VERSION );
			wp_enqueue_script( 'wpsmstowoorepeater', SFW_SMS_TO_WOO_URL . 'assets/js/jquery.repeater.min.js', true, SFW_SMS_TO_WOO_VERSION );
                        wp_enqueue_script( 'wpsmstowooblocktimerepeater', SFW_SMS_TO_WOO_URL . 'assets/js/jquery.repeater.min.js', true, SFW_SMS_TO_WOO_VERSION );
			wp_enqueue_script( 'wpsmstowoo-admin', SFW_SMS_TO_WOO_URL . 'assets/js/admin.js', true, SFW_SMS_TO_WOO_VERSION );
		}
	}

	/**
	 * Admin bar plugin
	 */
	public function admin_bar() {
		global $wp_admin_bar;
		if ( is_super_admin() && is_admin_bar_showing() ) {                   
                    $credit = get_option('wpsmstowoo_gateway_credit');
                    if ($credit AND isset($this->options['account_credit_in_menu']) AND!is_object($credit)) {
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
        
	/**
	 * Dashboard glance plugin
	 */
	public function dashboard_smsto_glance() {
		$subscribe = $this->db->get_var( "SELECT COUNT(*) FROM `{$this->db->prefix}usermeta` where `meta_key` = 'wp_capabilities' and `meta_value` LIKE '%ubscriber%'" );
		$credit    = get_option( 'wpsmstowoo_gateway_credit' );

		echo "<li class='wpsmstowoo-subscribe-count'><a href='" . SFW_SMS_TO_WOO_ADMIN_URL . "users.php?role=subscriber'>" . sprintf( __( '%s WP Subscriber(s)', 'sms-for-woocommerce' ), esc_html($subscribe) ) . "</a></li>";
		if ( ! is_object( $credit ) ) {
			echo "<li class='wpsmstowoo-credit-count'><a href='" . SFW_SMS_TO_WOO_ADMIN_URL . "admin.php?page=sms-for-woocommerce-settings&tab=gateway'>" . sprintf( __( '%s SMSto Credit', 'sms-for-woocommerce' ), esc_html($credit) ) . "</a></li>";

		}
	}

	/**
	 * Administrator admin_menu
	 */
	public function admin_menu() {
		$hook_suffix = array();
		add_menu_page( __( 'MSGOWL for WooCommerce', 'sms-for-woocommerce' ), __( 'Msgowl For WooCommerce', 'sms-for-woocommerce' ), 'wpsmstowoo', 'sms-for-woocommerce', array( $this, 'outbox_callback' ), 'dashicons-smartphone' );
                add_submenu_page( 'sms-for-woocommerce', __( 'Reports', 'sms-for-woocommerce' ), __( 'Reports', 'sms-for-woocommerce' ), 'wpsmstowoo_outbox', 'sms-for-woocommerce-outbox', array( $this, 'outbox_callback' ) );
                add_submenu_page( 'sms-for-woocommerce', __( 'Documentation', 'sms-for-woocommerce' ), __( 'Documentation', 'sms-for-woocommerce' ), 'wpsmstowoo_outbox', 'sms-for-woocommerce-documentation', array( $this, 'documentation_callback' ) );
                
		// Check GDPR compliance for Privacy menu
		if ( isset( $this->options['gdpr_wpsmstowoo_compliance'] ) and $this->options['gdpr_wpsmstowoo_compliance'] == 1 ) {
			$hook_suffix['privacy'] = add_submenu_page( 'sms-for-woocommerce', __( 'Privacy', 'sms-for-woocommerce' ), __( 'Privacy', 'sms-for-woocommerce' ), 'manage_options', 'sms-for-woocommerce-subscribers-privacy', array( $this, 'privacy_callback' ) );
		}
                
                // Add styles to menu pages
                foreach ($hook_suffix as $menu => $hook) {
                    add_action("load-{$hook}", array($this, $menu . '_assets'));
                }
        }


    /**
	 * Callback outbox page.
	 */
	public function outbox_callback() {
		$page = new Outbox();
		$page->render_page();
	}
        
        
    /**
	 * Callback outbox page.
	 */
	public function documentation_callback() {
            require_once SFW_SMS_TO_WOO_DIR . 'includes/admin/documentation/class-smsforwoocommerce-documentation.php';
            
		$page = new SFW_SMS_TO_Documentation();
		$page->render_page();
	}            

	/**
	 * Callback subscribers page.
	 */
	public function privacy_callback() {
		// Privacy class.
		require_once SFW_SMS_TO_WOO_DIR . 'includes/admin/privacy/class-smsforwoocommerce-privacy.php';

		$page           = new SFW_SMS_TO_Privacy();
		$page->pagehook = get_current_screen()->id;
		$page->render_page();
	}

	/**
	 * Load send SMS page assets
	 */
	public function outbox_assets() {
			wp_enqueue_style( 'jquery-flatpickr', SFW_SMS_TO_WOO_URL . 'assets/css/flatpickr.min.css', true, SFW_SMS_TO_WOO_VERSION );
			wp_enqueue_script( 'jquery-flatpickr', SFW_SMS_TO_WOO_URL . 'assets/js/flatpickr.min.js', array( 'jquery' ), SFW_SMS_TO_WOO_VERSION );
	}

	/**
	 * Administrator add Meta Links
	 *
	 * @param $links
	 * @param $file
	 *
	 * @return array
	 */
	public function meta_links( $links, $file ) {
		if ( $file == 'msgowl-for-woocommerce/msgowl-for-woocommerce.php' ) {
			$docs_url =  SFW_SMS_TO_WOO_ADMIN_URL . '/admin.php?page=sms-for-woocommerce-documentation';
			$links[]  = '<a href="' . $docs_url . '" target="_blank" class="wpsmstowoo-plugin-meta-link" title="' . __( 'Click here to view plugin documentation', 'sms-for-woocommerce' ) . '">' . __( 'Docs', 'sms-for-woocommerce' ) . '</a>';

		}

		return $links;
	}

	/**
	 * Adding new capability in the plugin
	 */
	public function add_cap() {
		// Get administrator role
		$role = get_role( 'administrator' );

		$role->add_cap( 'wpsmstowoo_sendsms' );
		$role->add_cap( 'wpsmstowoo_outbox' );
		$role->add_cap( 'wpsmstowoo_subscribers' );
		$role->add_cap( 'wpsmstowoo_setting' );
	}

	/**
	 * Initial plugin
	 */
	private function init() {
		if ( isset( $_GET['action'] ) ) {
			if ( sanitize_text_field($_GET['action']) == 'wpsmstowoo-hide-newsletter' ) {
				update_option( 'wpsmstowoo_hide_newsletter', true );
			}
		}

		if ( ! get_option( 'wpsmstowoo_hide_newsletter' ) ) {
			//add_action( 'sms_to_woo_settings_page', array( $this, 'admin_newsletter' ) );
		}

		// Check exists require function
		if ( ! function_exists( 'wp_get_current_user' ) ) {
			include( ABSPATH . "wp-includes/pluggable.php" );
		}

		// Add plugin caps to admin role
		if ( is_admin() and is_super_admin() ) {
			$this->add_cap();
		}
	}

}

new SFW_SMS_TO_Admin();