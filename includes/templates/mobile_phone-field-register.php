<?php
if ( sms_to_woo_get_option( 'international_mobile_phone' ) ) {
	$sms_to_woo_input_mobile_phone = " sms-for-woocommerce-input-mobile_phone";
} else {
	$sms_to_woo_input_mobile_phone = "";
}
?>
<p>
    <label for="mobile_phone"><?php _e( 'Mobile Number', 'sms-for-woocommerce' ) ?><br/>
        <input type="text" name="mobile_phone" id="mobile_phone" class="input<?php echo esc_html($sms_to_woo_input_mobile_phone) ?>"
               value="<?php echo esc_attr( stripslashes( $mobile_phone ) ); ?>" size="25"/></label>
</p>



