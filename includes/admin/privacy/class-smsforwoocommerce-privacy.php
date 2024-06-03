<?php

namespace SFW_SMS_TO_WOO;


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
class SFW_SMS_TO_Privacy {

	public $pagehook;
	public $metabox = 'privacy_metabox_general';

	/*
	 * Gdpr Text Metabox
	 */
	public static function privacy_meta_html_gdpr() {
		echo '<p style="text-align: center;"><img src="' . SFW_SMS_TO_WOO_URL . '/assets/images/gdpr.png" alt="GDPR"></p>';
		echo '<p class="text-lead">';
		echo sprintf( __( 'According to Article 17 GDPR, the user (data subject) shall have the right to obtain his/her data or have them erased and forgotten. In MSGOWL for WooCommerce plugin you can export the user\'s data or erase his/her data in the case she/he asks. For more information, read %1$sArticle 17 GDPR%2$s.%3$s Note: In this page you can export or delete only the user data related to MSGOWL for WooCommerce plugin. For doing the same for your whole WordPress, see the "Export Personal Data" or "Erase Personal Data" pages.', 'sms-for-woocommerce' ), '<a href="' . esc_url( 'https://gdpr-info.eu/art-17-gdpr/' ) . '" target="_blank" style="text-decoration: none; color:#ff0000;">', '</a>', '<br />' ) . "\n";
		echo '</p>';
	}

	/*
	 * export Text Metabox
	 */
	public static function privacy_meta_html_export() {
		?>
        <form method="post" action="">
            <div id="universal-message-container">
                <div class="options">
                    <p>
                        <label><?php _e( 'User’s Mobile Number', 'sms-for-woocommerce' ); ?></label>
                        <br/>
                        <input type="tel" name="mobile_phone-number-export" value=""/>
                    </p>
                </div>
				<?php submit_button( __( 'Export' ), 'primary', 'submit', false ); ?>
            </div>
            <input type="hidden" name="sms_to_woo_nonce_privacy" value="<?php echo esc_html(wp_create_nonce( 'sms_to_woo_nonce_privacy' )); ?>">
        </form>
        <div class="clear"></div>
		<?php
	}


	/*
	 * delete Text Metabox
	 */
	public static function privacy_meta_html_delete() {
		?>
        <form method="post" action="">
            <div id="universal-message-container">
                <div class="options">
                    <p>
                        <label><?php _e( 'Enter User’s Mobile Number', 'sms-for-woocommerce' ); ?></label>
                        <br/>
                        <input type="tel" name="mobile_phone-number-delete" value=""/>
                        <br/>
                        <span class="description"><?php _e( 'Note: You cannot undo these actions.', 'sms-for-woocommerce' ); ?></span>
                    </p>
                </div><!-- #universal-message-container -->
				<?php submit_button( __( 'Delete' ), 'primary', 'submit', false ); ?>
            </div>
            <input type="hidden" name="sms_to_woo_nonce_privacy" value="<?php echo esc_html(wp_create_nonce( 'sms_to_woo_nonce_privacy' )); ?>">
        </form>
        <div class="clear"></div>
		<?php
	}

	/*
	 * Show MetaBox System
	 */
	public function render_page() {
		?>
        <div id="<?php echo esc_html($this->metabox); ?>" class="wrap privacy_page">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="admin-post.php" method="post">
				<?php wp_nonce_field( $this->metabox ); ?>
				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
                <input type="hidden" name="action" value="save_<?php echo esc_html($this->metabox); ?>"/>
            </form>
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-<?php echo 1 == esc_html(get_current_screen()->get_columns()) ? '1' : '2'; ?>">
                    <div id="postbox-container-1" class="postbox-container">
						<?php do_meta_boxes( $this->pagehook, 'side', '' ); ?>
                    </div>

                    <div id="postbox-container-2" class="postbox-container">
						<?php do_meta_boxes( $this->pagehook, 'normal', '' ); ?>
                    </div>
                </div><!-- #post-body --><br class="clear">
            </div><!-- #poststuff -->

        </div>
		<?php
	}

}

new SFW_SMS_TO_Privacy();