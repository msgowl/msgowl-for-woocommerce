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
#[\AllowDynamicProperties]
class SFW_SMS_TO_WOO {

	public function __construct() {
		/*
		 * Plugin Loaded Action
		 */
		add_action( 'plugins_loaded', array( $this, 'wpsmstowoo_plugin_setup' ) );
                add_action( 'init', array( $this, 'sfw_sms_to_woo_update_db' ) );
              
		/**
		 * Install And Upgrade plugin
		 */
		require_once SFW_SMS_TO_WOO_DIR . 'includes/class-smsforwoocommerce-install.php';

		register_activation_hook( SFW_SMS_TO_WOO_DIR . 'msgowl-for-woocommerce.php', array( '\SFW_SMS_TO_WOO\Install', 'install' ) );
                
                add_action('init', array( $this, 'redirect_to_general_url_handler' ) );
    }

	/**
	 * Constructors plugin Setup
	 *
	 * @param Not param
	 */
	public function wpsmstowoo_plugin_setup() {
		// Load text domain
		add_action( 'init', array( $this, 'sms_to_woo_load_textdomain' ) );

		$this->includes();
	}

	/**
	 * Redirect to specific tab
	 * If the page = =sms-for-woocommerce-settings then redirect to tab=general
	 * 
	 */    
        
       public function redirect_to_general_url_handler() {
        if (substr($_SERVER["REQUEST_URI"], -20) == 'page=sms-for-woocommerce-settings') {
            $url = $_SERVER["REQUEST_URI"] . '&tab=general';
            wp_redirect($url);
        }
    }     

    /**
	 * Load plugin textdomain.
	 *
	 * @since 1.0.0
	 */
	public function sms_to_woo_load_textdomain() {           
		load_plugin_textdomain( 'sms-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

        
        
        
    /**
	 * update db on update plugin
	 *
	 * @since 1.0.0
	 */
    public function sfw_sms_to_woo_update_db() {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $installer_wpsms_ver = get_option('sms_to_woo_db_version');

        if ($installer_wpsms_ver < SFW_SMS_TO_WOO_VERSION) {

            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();

            // Add response and status for outbox
            $table_name = $wpdb->prefix . 'msgowl_woo_send';
            $column = $wpdb->get_results($wpdb->prepare(
                            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
                            DB_NAME, $table_name, 'updated_at'
                    ));

            if (empty($column)) {
                $wpdb->query("ALTER TABLE {$table_name} ADD updated_at DATETIME AFTER date");
            }

            update_option('sms_to_woo_db_version', SFW_SMS_TO_WOO_VERSION);
        }
    }        
        
        
	/**
	 * Includes plugin files
	 *
	 * @param Not param
	 */
	public function includes() {

		// Utility classes.
		require_once SFW_SMS_TO_WOO_DIR . 'includes/class-smsforwoocommerce-features.php';
		require_once SFW_SMS_TO_WOO_DIR . 'includes/class-smsforwoocommerce-integrations.php';
                
		if ( is_admin() ) {
			// Admin classes.
			require_once SFW_SMS_TO_WOO_DIR . 'includes/admin/class-smsforwoocommerce-admin.php';
			require_once SFW_SMS_TO_WOO_DIR . 'includes/admin/class-smsforwoocommerce-admin-helper.php';

			// Outbox class.
			require_once SFW_SMS_TO_WOO_DIR . 'includes/admin/outbox/class-smsforwoocommerce-outbox.php';
                      
                        
                        
			// Privacy class.
			require_once SFW_SMS_TO_WOO_DIR . 'includes/admin/privacy/class-smsforwoocommerce-privacy-actions.php';
                        
			// Documentation class.
			require_once SFW_SMS_TO_WOO_DIR . 'includes/admin/documentation/class-smsforwoocommerce-documentation.php';

			// Settings classes.
			require_once SFW_SMS_TO_WOO_DIR . 'includes/admin/settings/class-smsforwoocommerce-settings.php';


}
		
		if ( ! is_admin() ) {
			// Front Class.
			require_once SFW_SMS_TO_WOO_DIR . 'includes/class-front.php';
		}


		// Template functions.
		require_once SFW_SMS_TO_WOO_DIR . 'includes/template-functions.php';
	}
}