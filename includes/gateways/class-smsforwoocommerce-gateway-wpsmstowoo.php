<?php

namespace SFW_SMS_TO_WOO\Gateway;

/**
 * This plugin is a fork from https://wordpress.org/plugins/wp-sms/ developed by VeronaLabs
 * @author mostafa.s1990, kashani, mehrshaddarzi, alifallahrn, panicoschr10
 * @copyright  2020 VeronaLabs
 * @license    GPLv3
 * @license uri: http://www.gnu.org/licenses/gpl.html
 */
#[\AllowDynamicProperties]
class wpsmstowoo extends \SFW_SMS_TO_WOO\SFW_SMS_TO_Gateway {

    public $wsdl_link;
    public $tariff;
    public $unitrial = true;
    public $unit;
    public $flash = "enable";
    public $isflash = false;
    public $callback_url ; 

    public function __construct() {
        parent::__construct();
        $this->validateNumber = "XXXXXXXX,YYYYYYYY";
        $this->api_key = false;
        $this->bulk_send = true;
        $this->tariff = $this->getTariff();
        $this->wsdl_link = $this->getWsdl_link();
    }

    /**
     * Returns the balance of msgowl account
     * 
     * @author Christodoulou Panikos
     * @email christodoulou.panicos@cytanet.com.cy
     */      
    public static function getTariff() {
        $tariff = "https://rest.msgowl.com";
        return $tariff;
    }    
    
    /**
     * Returns the wsdl_link
     * 
     * @author Christodoulou Panikos
     * @email christodoulou.panicos@cytanet.com.cy
     */    
    public static function getWsdl_link() {

            $wsdl_link = "https://rest.msgowl.com";
            
      
        return $wsdl_link;
    }        
       
    public function SendSMS() {        
    
        /**
         * Modify sender number
         *
         * @since 3.4
         *
         * @param string $this ->from sender number.
         */
        $this->from = apply_filters('sms_to_woo_from', substr($this->from, 0, 11));
        /**
         * Modify Receiver number
         *
         * @since 3.4
         *
         * @param array $this ->to receiver number
         */
        $this->to = apply_filters('sms_to_woo_to', $this->to);     
        
        /**
         * Modify campaign recipients
         *
         * @since 3.4
         *
         * @param array $this ->campaign recipients
         */
        $this->campaign_recipients = apply_filters('sms_to_woo_campaign_recipients', $this->campaign_recipients);      
        
        /**
         * Modify _id
         *
         * @since 3.4
         *
         * @param array $this ->_id
         */
        $this->_id = apply_filters('sms_to_woo__id', $this->_id);             
        /**
         * Modify text message
         *
         * @since 3.4
         *
         * @param string $this ->msg text message.
         */
        $api_key = $this->api_key;
        // Get the credit.
        $credit = $this->GetCredit();   

        
        // Check gateway credit
        if (is_wp_error($credit)) {
            // Log the result
            $this->log_message($this->_id, $this->from, $this->msg, $this->to, $credit->get_error_message(), 'FAILED');

            return $credit;
        }

        $this->msg = apply_filters('sms_to_woo_msg', $this->msg);

        $bodyContent = array(
            'sender_id' => $this->from,
            'recipients' => $this->to,
            'body' => $this->msg,
        );
        
        if ((!isset($this->to[1])) && (isset($this->to[0]))) {
            $bodyContent['to'] = $this->to[0];
        } 
        
        if  (isset($this->options['gateway_wpsmstowoo_callback_url']))  {
            $callback_url = apply_filters('sms_to_woo_callback', $this->options['gateway_wpsmstowoo_callback_url']); 
            $bodyContent['callback_url'] = $callback_url.'/wp-json/sms-for-woocommerce/get_post';
        }
        
        if (empty($api_key)) {
            return [
                'error' => true,
                'reason' => 'Invalid Credentials',
                'data' => null,
                'status' => 'FAILED'
            ];
        }
        
        
        
        
   if ($bodyContent) {
            $body = json_encode($bodyContent);
        }



        $url = $this->getWsdl_link() . '/messages';

        $headers = array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        );        

        $args = array(
            'body' => $body,
            'timeout' => '15',
            'redirection' => '10',
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => $headers,
        );


        $response = wp_remote_post($url, $args);

        $_id = '';

        if (!is_wp_error($response) && 200 == wp_remote_retrieve_response_code($response)) {
            $body = json_decode(wp_remote_retrieve_body($response));

            if (isset($body->id)) {
                $_id = $body->id;
            }

            $this->log_message($_id, $this->from, $this->msg, $this->to, $response, 'ONGOING');

            do_action('sms_to_woo_send', $response);

            return $response;

        } else {
            if (is_array($response)) {
                $error = json_encode($response);
            } else {
                $error = $response->get_error_message();
            }
            
            if (isset($error)) {
                $response = print_r($error, 1);
                $_id = $response;
            }
            $response = [
                'error' => true,
                'reason' => $response,
                'data' => $bodyContent,
                'status' => 'FAILED'
            ];
            do_action('sms_to_woo_send', $response);

            $this->log_message($_id, $this->from, $this->msg, $this->to, $response, 'FAILED');

            return new \WP_Error('send-sms', $response);
        }        
    }

    public function GetCredit() {
        // Check api
        if (!$this->api_key) {
            return new \WP_Error('account-credit', __('API not set', 'sms-for-woocommerce'));
        }
   
        $result = 'message';

        $args = [
            'headers' => [
                'Authorization' => 'Bearer '.$this->api_key
            ]
        ];
        
        $response = wp_remote_get(\SFW_SMS_TO_WOO\Gateway\wpsmstowoo::getTariff() . '/balance', $args);
        if (!is_wp_error($response) && 200 == wp_remote_retrieve_response_code($response)) {
            $body = json_decode(wp_remote_retrieve_body($response));
            return round($body->balance, 2) . ' '. $body->currency;
        } else {
            return new \WP_Error('account-credit', 'Unable to send your '.$result);
        }
    }

}
