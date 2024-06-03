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
class Install {

	public function __construct() {
		add_action( 'wpmu_new_blog', array( $this, 'add_table_on_create_blog' ), 10, 1 );
		add_filter( 'wpmu_drop_tables', array( $this, 'remove_table_on_delete_blog' ) );
	}

	/**
	 * Adding new MYSQL Table in Activation Plugin
	 *
	 * @param Not param
	 */
	public static function create_table( $network_wide ) {
		global $wpdb;

		if ( is_multisite() && $network_wide ) {
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );

				self::table_sql();

				restore_current_blog();
			}
		} else {
			self::table_sql();
		}

	}

	/**
	 * Table SQL
	 *
	 * @param Not param
	 */
	public static function table_sql() {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$charset_collate = $wpdb->get_charset_collate();

		$table_name = $wpdb->prefix . 'msgowl_woo_send';
		if ( $wpdb->get_var( "show tables like '{$table_name}'" ) != $table_name ) {
			$create_wpsmstowoo_send = ( "CREATE TABLE IF NOT EXISTS {$table_name}(
            ID int(10) NOT NULL auto_increment,
            _id VARCHAR(50),
            type VARCHAR(10),
            cost REAL,
            message_count INTEGER,
            sms_parts INTEGER,
            sent INTEGER,
            failed INTEGER,
            pending INTEGER,
            date DATETIME,
            updated_at DATETIME,
            sender VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            recipient TEXT NOT NULL,
            response TEXT NOT NULL,
            status varchar(100) NOT NULL,
            PRIMARY KEY(ID)) $charset_collate" );

			dbDelta( $create_wpsmstowoo_send );
		}
                
	}

	/**
	 * Creating plugin tables
	 *
	 * @param $network_wide
	 */
	static function install( $network_wide ) {
		global $sms_to_woo_db_version;

		self::create_table( $network_wide );

		add_option( 'sms_to_woo_db_version', SFW_SMS_TO_WOO_VERSION );

		// Delete notification new wp_version option
		delete_option( 'wp_notification_new_wp_version' );

		if ( is_admin() ) {
			self::upgrade();
		}
	}

	/**
	 * Upgrade plugin requirements if needed
	 */
	static function upgrade() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	}

	/**
	 * Creating Table for New Blog in wordpress
	 *
	 * @param $blog_id
	 */
	public function add_table_on_create_blog( $blog_id ) {
		if ( is_plugin_active_for_network( 'msgowl-for-woocommerce/msgowl-for-woocommerce.php' ) ) {
			switch_to_blog( $blog_id );

			self::table_sql();

			restore_current_blog();
		}
	}

	/**
	 * Remove Table On Delete Blog Wordpress
	 *
	 * @param $tables
	 *
	 * @return array
	 */
	public function remove_table_on_delete_blog( $tables ) {

		return [ $this->tb_prefix . 'msgowl_woo_send'];
	}
}

new Install();