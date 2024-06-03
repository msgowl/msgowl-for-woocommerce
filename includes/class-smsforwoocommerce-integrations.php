<?php

namespace SFW_SMS_TO_WOO;


if (!defined('ABSPATH')) {
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
class SFW_SMS_TO_Integrations {

    public $wpsmstowoo;
    public $date;
    public $options;
    public $cf7_data;

    public function __construct() {
        global $wpsmstowoo;

        $this->sms = $wpsmstowoo;
        $this->date = SFW_SMS_TO_WOO_CURRENT_DATE;
        $this->options = SFW_SMS_TO_Option::getOptions();



        // Woocommerce
        if (isset($this->options['wc_notif_new_order_enable'])) {
            add_action('woocommerce_thankyou', array($this, 'wc_new_order'));
        }

        if (isset($this->options['wc_notify_customer_enable'])) {
            add_action('woocommerce_thankyou', array($this, 'wc_new_order_customer_notification'));
        }
        
        if (isset($this->options['wc_notify_order_enable'])) {
            add_action('woocommerce_thankyou', array($this, 'wc_new_order_receipient_notification'));
        }        


        if (isset($this->options['wc_notify_product_enable'])) {
            add_action('transition_post_status', array($this, 'wc_new_product'), 10, 3);
        }

        if (isset($this->options['wc_notify_status_enable'])) {
            add_action('woocommerce_order_status_changed', array($this, 'wc_new_status'), 10, 3);
        }        
        
        
        if ( isset( $this->options['wc_notify_by_status_enable'] ) ) {
                add_action( 'woocommerce_order_status_changed', array( $this, 'notification_group_by_order_status' ), 10, 4);
        }        
        
        
        // EDD
        if (isset($this->options['edd_notif_new_order'])) {
            add_action('edd_complete_purchase', array($this, 'add_new_order'));
        }
    }

    public function wc_new_order($order_id) {
        $order = new \WC_Order($order_id);
        if (isset($this->options['admin_mobile_phone_number'])) {
            $admin_mobile_phone_number = $this->options['admin_mobile_phone_number'];
        }
        
        if ((isset($admin_mobile_phone_number)) && ($admin_mobile_phone_number)) {
            $this->sms->to = array($admin_mobile_phone_number);
            $template_vars = array(
                '%order_id%' => $order_id,
                '%status%' => $order->get_status(),
                '%order_number%' => $order->get_order_number(),
            );
            $message = str_replace(array_keys($template_vars), array_values($template_vars), $this->options['wc_notif_new_order_template']);
            $this->sms->msg = $message;
           
            $this->sms->SendSMS();
        }
    }

    /**
     * Send SMS to recipient for new order
     * 
     * @author Christodoulou Panikos
     * @email christodoulou.panicos@cytanet.com.cy
     * @param order id
     * @return 
     */
    public function wc_new_order_receipient_notification($order_id) {
        $order = new \WC_Order($order_id);

            if (isset($this->options['wc_notify_order_receiver'])) {
                $mobile_phone = $this->options['wc_notify_order_receiver']; 
            }
            
            if ((isset($mobile_phone)) && ($mobile_phone)) {
                $this->sms->to = array_map( 'sanitize_text_field', explode(",", $mobile_phone) );
                
                
               if ($mobile_phone[1]) {
                $this->sms->campaign_recipients = 'SMS Receivers';
                } else {
                    $this->sms->campaign_recipients = $mobile_phone;
                }
                $template_vars = array(
                    '%order_id%' => $order_id,
                    '%order_number%' => $order->get_order_number(),
                    '%status%' => $order->get_status(),
                    '%order_total%' => get_woocommerce_currency_symbol().$order->get_total(),
                    '%billing_first_name%' => $order->get_billing_first_name(),
                    '%billing_last_name%' => $order->get_billing_last_name(),
                    '%billing_phone%' => $order->get_billing_phone()
                );

                $message = str_replace(array_keys($template_vars), array_values($template_vars), $this->options['wc_notify_order_message']);
                $this->sms->msg = $message;

                $this->sms->SendSMS();
            }
      
    }

    /**
     * Send SMS to customer for new order
     * 
     * @author Christodoulou Panikos
     * @email christodoulou.panicos@cytanet.com.cy
     * @param order id
     * @return 
     */
    public function wc_new_order_customer_notification($order_id) {
        $order = new \WC_Order($order_id);

        $mobile_phone = $this->getCustomerPhoneNumber($order_id);
        
        if ($mobile_phone[0]) {   
            $this->sms->to = $mobile_phone;
            $template_vars = array(
                '%order_id%' => $order_id,
                '%order_number%' => $order->get_order_number(),
                '%status%' => $order->get_status(),
                '%order_total%' => get_woocommerce_currency_symbol().$order->get_total(),
                '%billing_first_name%' => $order->get_billing_first_name(),
                '%billing_last_name%' => $order->get_billing_last_name()
            );

            $message = str_replace(array_keys($template_vars), array_values($template_vars), $this->options['wc_notify_customer_message']);
            $this->sms->msg = $message;

            $this->sms->SendSMS();
        }
    }

    /**
     * Retrieve the customer phone number from customer or from the order
     * 
     * @author Christodoulou Panikos
     * @email christodoulou.panicos@cytanet.com.cy
     * @param order
     * @return Number
     */
    private function getCustomerPhoneNumber($order_id) {

        $mobile_phone = '';
              
        if (isset($this->options['wc_mobile_phone_field'])) {
            if ($this->options['wc_mobile_phone_field'] == 'customer_mobile_phone') {
                
                //check if user is logged in
                $c_user_id = get_current_user_id();
                
                //frontend
                if (isset($_REQUEST['customer_user'])) {
                    $user_id = sanitize_text_field($_REQUEST['customer_user']);
                    $mobile_phone = get_user_meta($user_id, 'mobile_phone', true);                    
                } else
                //backend    
                if (isset($_REQUEST['user_ID'])) {
                    $user_id = sanitize_text_field($_REQUEST['user_ID']);
                    $mobile_phone = get_user_meta($user_id, 'mobile_phone', true);                    
                } else
                
                if (isset($c_user_id) && ($c_user_id != 0 ))
                {
                    $mobile_phone = get_user_meta($c_user_id, 'mobile_phone', true);  
                }                
            } elseif ($this->options['wc_mobile_phone_field'] == 'order_mobile_phone') {
                if ($order_id) {
                    
                    $order = new \WC_Order($order_id);
                        
                    $mobile_phone = $order->get_billing_phone();

                }               
            } else {
                $mobile_phone = '';
            }
            return array($mobile_phone);
        } else {
               return array($mobile_phone);
        }
    }

    /**
     * Retrieve the users phone numbers of a role
     * 
     * @author Christodoulou Panikos
     * @email christodoulou.panicos@cytanet.com.cy
     * @param role
     * @return Array
     */
    private function getGroupPhoneNumbers($role) {

        $mobile_phone_numbers = array();
        $args = array(
            'role' => $role,
            'fields' => 'ID'
        );

        $users = get_users($args);        
        if ($users) {
            foreach ($users as $user) {
                    $mobile_phone = get_user_meta($user, 'mobile_phone', true);
                if (isset($mobile_phone)) {
                    $mobile_phone_numbers[] = $mobile_phone;
                }
            }
        }
        return $mobile_phone_numbers;
    }

    /**
     * Send SMS as per Settings to recipients for the publishing of a New Product
     * 
     * @author Christodoulou Panikos
     * @email christodoulou.panicos@cytanet.com.cy
     * @param new_status, old_status, Post
     * @return 
     */
    public function wc_new_product($new_status, $old_status, $post) {
        
        $mobile_phone_numbers = array();
           
        if ($old_status != 'publish' && $new_status == 'publish' && !empty($post->ID) && in_array($post->post_type, array('product'))) {

            $mobile_phone_numbers = $this->getGroupPhoneNumbers('customer');

            if ($mobile_phone_numbers[0]) {                     
                $this->sms->to = $mobile_phone_numbers;
                
                if ($mobile_phone_numbers[1]) {
                $this->sms->campaign_recipients = 'Customers';
                } else {
                    $this->sms->campaign_recipients = $mobile_phone_numbers;
                }
                
                $post_data = json_decode(file_get_contents('php://input'), true);
                $template_vars = array(
                    '%product_title%' => $post->post_title,
                    '%product_url%' => $post->guid,
                    '%product_date%' => $post->post_date,
                    '%product_price%' => get_woocommerce_currency_symbol().$this->get_product_price($post_data)
                );
                $message = str_replace(array_keys($template_vars), array_values($template_vars), $this->options['wc_notify_product_message']);
                $this->sms->msg = $message;
                $this->sms->SendSMS();
            }
        }
    }
    
    
     /**
     * Get product price
     * 
     * @author Christodoulou Panikos
     * @email christodoulou.panicos@cytanet.com.cy
     * @param 
     * @return $price
     */
    private function get_product_price($post_data) {

        if (isset($post_data['sale_price'])) {
            return (sanitize_text_field($post_data['sale_price']));
        }
        if (isset($post_data['regular_price'])) {
            return (sanitize_text_field($post_data['regular_price']));
        }
        if (isset($_REQUEST['_sale_price'])) {
            return sanitize_text_field($_REQUEST['_sale_price']);
        }
        if (isset($_REQUEST['_regular_price'])) {
            return sanitize_text_field($_REQUEST['_regular_price']);
        }
        return '';
    }

    /**
     * Send SMS to selected groups for different order status changes
     * 
     * @author Christodoulou Panikos
     * @email christodoulou.panicos@cytanet.com.cy
     * @param new_status, old_status, Post
     * @return 
     */
    public function notification_group_by_order_status($order_id, $old_status  = '', $new_status  = '') {
        
        
        $order = new \WC_Order($order_id);
        
        if (isset($this->options['wc_notify_by_status_enable'])) {

                $array = $this->options['wc_notify_by_status_content'];

                foreach ($array as $row) {
                    $role = $row['role'];
                    $notify_status = $row['notify_status'];
                    $order_status = $row['order_status'];
                    $row_message = $row['message'];

                if (($role == 'administrator') || ($role == 'shop_manager') || ($role == 'customer')) {
                    if ($notify_status == '1') {
                        if ($new_status == $order_status) {
                            if ($role == 'customer') {
                                $mobile_phone = $this->getCustomerPhoneNumber($order_id);
                                if ($mobile_phone[1]) {
                                    $this->sms->campaign_recipients = 'Customers';
                                } else {
                                    $this->sms->campaign_recipients = $mobile_phone;
                                }
                            }
                            if ($role == 'administrator') {
                                $mobile_phone = $this->getGroupPhoneNumbers($role);
                                if ($mobile_phone[1]) {
                                    $this->sms->campaign_recipients = 'Administrators';
                                } else {
                                    $this->sms->campaign_recipients = $mobile_phone;
                                }                                
                            }
                            
                            if ($role == 'shop_manager') {
                                $mobile_phone = $this->getGroupPhoneNumbers($role);
                                if ($mobile_phone[1]) {
                                    $this->sms->campaign_recipients = 'Shop Managers';
                                } else {
                                    $this->sms->campaign_recipients = $mobile_phone;
                                }                                   
                            }                            

                     if ($mobile_phone[0]) {  
                                $this->sms->to = $mobile_phone;
                                $template_vars = array(
                                    '%order_id%' => $order_id,
                                    '%order_number%' => $order->get_order_number(),
                                    '%status%' => $order_status,
                                    '%order_total%' => get_woocommerce_currency_symbol().$order->get_total(),
                                    '%billing_first_name%' => $order->get_billing_first_name(),
                                    '%billing_last_name%' => $order->get_billing_last_name()
                                );
                                $message = str_replace(array_keys($template_vars), array_values($template_vars), $row_message);
                                $this->sms->msg = $message;

                                $this->sms->SendSMS();
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Send SMS to customer when order status change
     * 
     * @author Christodoulou Panikos
     * @email christodoulou.panicos@cytanet.com.cy
     * @param new_status, old_status, Post
     * @return 
     */
    public function wc_new_status($order_id) {
        
	$order = new \WC_Order( $order_id );

        $mobile_phone = $this->getCustomerPhoneNumber($order_id);
        if ($mobile_phone[0]) {  
            $this->sms->to = $mobile_phone;
            $template_vars = array(
                '%order_number%' => $order_id,
                '%status%' => $order->get_status(),
                '%customer_first_name%' => $order->get_billing_first_name(),
                '%customer_last_name%' => $order->get_billing_last_name(),
            );
            
            $message = str_replace(array_keys($template_vars), array_values($template_vars), $this->options['wc_notify_status_message']);
            $this->sms->msg = $message;
            $this->sms->SendSMS();
        }
    }    

    public function add_new_order() {
        $this->sms->to = array($this->options['admin_mobile_phone_number']);
        $this->sms->msg = $this->options['edd_notif_new_order_template'];
        $this->sms->SendSMS();
    }

}

new SFW_SMS_TO_Integrations();
