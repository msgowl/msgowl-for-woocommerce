<?php
if ( sms_to_woo_get_option( 'international_mobile_phone' ) ) {
	$sms_to_woo_input_mobile_phone = " sms-for-woocommerce-input-mobile_phone";
} else {
	$sms_to_woo_input_mobile_phone = "";
}
?>
<table class="form-table">
    <tr>
        <th><label for="mobile_phone"><?php _e( 'Mobile Number', 'sms-for-woocommerce' ); ?></label></th>
        <td>
            <input type="text" class="regular-text<?php echo esc_html($sms_to_woo_input_mobile_phone) ?>" name="mobile_phone" value="" id="mobile_phone"/>
          
        </td>
    </tr>
</table>