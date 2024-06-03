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
class SFW_SMS_TO_Features {

    public $wpsmstowoo;
    public $date;
    public $options;
    protected $db;
    protected $tb_prefix;

    /**
     * SMS_TO_WOO_Features constructor.
     */
    public function __construct() {
        global $wpsmstowoo, $wpdb;

        $this->sms = $wpsmstowoo;
        $this->db = $wpdb;
        $this->tb_prefix = $wpdb->prefix;
        $this->date = SFW_SMS_TO_WOO_CURRENT_DATE;
        $this->options = SFW_SMS_TO_Option::getOptions();

        if (isset($this->options['add_mobile_phone_field'])) {

            //check if any other SMSTO plugin has already added the action
            // check only for register form, if it applies then it applies also for 
            // user_new_form and user_contactmethods
            
            $hook_name = 'register_form';
            $search_value = 'add_smsto_woo_mobile_phone_field_to_register_form';
            global $wp_filter;
            if (isset($wp_filter[$hook_name])) {
                $encoded_hook_name = json_encode($wp_filter[$hook_name]);
                $no_of_occurances = substr_count($encoded_hook_name, $search_value);
                if ($no_of_occurances == 0) {
                    add_action('register_form', array($this, 'add_smsto_woo_mobile_phone_field_to_register_form'));
                    add_action('user_new_form', array($this, 'add_smsto_woo_mobile_phone_field_to_newuser_form'));
                    add_filter('user_contactmethods', array($this, 'add_smsto_woo_mobile_phone_field_to_profile_form'));
                }
            } else {
                    add_action('register_form', array($this, 'add_smsto_woo_mobile_phone_field_to_register_form'));
                    add_action('user_new_form', array($this, 'add_smsto_woo_mobile_phone_field_to_newuser_form'));
                    add_filter('user_contactmethods', array($this, 'add_smsto_woo_mobile_phone_field_to_profile_form'));
            }

            add_filter('registration_errors', array($this, 'registration_errors'), 10, 3);
            add_action('user_register', array($this, 'save_register'));

            add_action('user_register', array($this, 'check_admin_duplicate_number'));
            add_action('profile_update', array($this, 'check_admin_duplicate_number'));
        }



        if (isset($this->options['international_mobile_phone'])) {
            add_action('wp_enqueue_scripts', array($this, 'load_international_input'));
            add_action('admin_enqueue_scripts', array($this, 'load_international_input'));
            add_action('login_enqueue_scripts', array($this, 'load_international_input'));
        }
        
        $page = isset($_GET['page']) ? $_GET['page'] : null;
        if (isset($page)) {
            if ($page == 'sms-for-woocommerce-outbox') {
                add_action('admin_enqueue_scripts', array($this, 'update_outbox_woo'));
                add_action('wp_enqueue_scripts', array($this, 'update_outbox_woo'), 10, 2);
            }
        }
    
    }
    
    /**
     * Add javascript for outbox
     * 
     * @author Christodoulou Panikos
     * @email christodoulou.panicos@cytanet.com.cy
     */
    
    function update_outbox_woo() {
        wp_register_script('update_outbox_woo', SFW_SMS_TO_WOO_URL . 'assets/js/outbox-form-woo.js', true, SFW_SMS_TO_WOO_VERSION);
        wp_enqueue_script('update_outbox_woo', SFW_SMS_TO_WOO_URL . 'assets/js/outbox-form-woo.js', true, SFW_SMS_TO_WOO_VERSION);
    }    
    
    
    
    
    /**
     * Retrieve User using mobile phone
     * 
     * @author Christodoulou Panikos
     * @email christodoulou.panicos@cytanet.com.cy
     * @param mobile_phone, mobile_phone number
     * @return User
     */
    public function sfw_sms_to_get_user_by_mobile_phone($db_field, $value) {
        global $wpdb;

        $user_id_obj = $wpdb->get_row(
                $wpdb->prepare(
                        "SELECT user_id FROM $wpdb->prefix" . "usermeta WHERE meta_key = %s
				 AND replace(meta_value, ' ', '') = +%d LIMIT 1",
                        $db_field, $value
                )
        );
        if (isset($user_id_obj)) {
            $user = get_user_by('id', $user_id_obj->user_id);
            return $user;
        } else {
            return null;
        }
    }

    /**
     * @param $mobile_phone_number
     * @param null $user_id
     *
     * @return bool
     */
    private function check_mobile_phone_number($mobile_phone_number, $user_id = null) {
        
        $trimmed_mobile_phone_number = str_replace(' ', '', $mobile_phone_number);
        if ($user_id) {
            $result = $this->db->get_results("SELECT * from `{$this->tb_prefix}usermeta` WHERE meta_key = 'mobile_phone' AND REPLACE(meta_value, ' ', '') = '{$trimmed_mobile_phone_number}' AND user_id != '{$user_id}'");
        } else {
            $result = $this->db->get_results("SELECT * from `{$this->tb_prefix}usermeta` WHERE meta_key = 'mobile_phone' AND REPLACE(meta_value, ' ', '') = '{$trimmed_mobile_phone_number}'");
        }

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $user_id
     */
    private function delete_user_mobile_phone($user_id) {
        $this->db->delete(
                $this->tb_prefix . "usermeta",
                array(
                    'user_id' => $user_id,
                    'meta_key' => 'mobile_phone',
                )
        );
    }

    /**
     * @param $user_id
     */
    public function check_admin_duplicate_number($user_id) {
        // Get user mobile_phone
        $user_mobile_phone = get_user_meta($user_id, 'mobile_phone', true);
        
        if (empty($user_mobile_phone)) {
            return;
        }

        // Delete user mobile_phone
        if ($this->check_mobile_phone_number($user_mobile_phone, $user_id)) {
            $this->delete_user_mobile_phone($user_id);
        }
    }

    public function add_smsto_woo_mobile_phone_field_to_newuser_form() {
        include_once SFW_SMS_TO_WOO_DIR . "includes/templates/mobile_phone-field.php";
    }
    
    /**
     * @param $fields
     *
     * @return mixed
     */
    public function add_smsto_woo_mobile_phone_field_to_profile_form($fields) {
        $fields['mobile_phone'] = __('Mobile Number', 'sms-for-woocommerce');

        return $fields;
    }

    public function add_smsto_woo_mobile_phone_field_to_register_form() {
        $mobile_phone = ( isset($_POST['mobile_phone']) ) ? sanitize_text_field($_POST['mobile_phone']) : '';
        include_once SFW_SMS_TO_WOO_DIR . "includes/templates/mobile_phone-field-register.php";
    }

    /**
     * @param $errors
     * @param $sanitized_user_login
     * @param $user_email
     *
     * @return mixed
     */
    public function registration_errors($errors, $sanitized_user_login, $user_email) {
        $error = false;

        if (empty($_POST['mobile_phone'])) {
            $errors->add('first_name_error', __('<strong>ERROR</strong>: You must include a mobile_phone number.', 'sms-for-woocommerce'));
        }

        if (preg_match('/^[0-9\-\(\)\/\+\s]*$/', sanitize_text_field($_POST['mobile_phone']), $matches) == false) {
            $errors->add('invalid_mobile_phone_number', __('Please enter a valid mobile_phone number', 'sms-for-woocommerce'));
            $error = true;
        }
        if (!$error && !isset($matches[0])) {
            $errors->add('invalid_mobile_phone_number', __('Please enter a valid mobile_phone number', 'sms-for-woocommerce'));
            $error = true;
        }

        if (!$error && isset($matches[0]) && strlen($matches[0]) < 10) {
            $errors->add('invalid_mobile_phone_number', __('Please enter a valid mobile_phone number', 'sms-for-woocommerce'));
            $error = true;
        }

        if (!$error && isset($matches[0]) && strlen($matches[0]) > 14) {
            $errors->add('invalid_mobile_phone_number', __('Please enter a valid mobile_phone number', 'sms-for-woocommerce'));
        }
        
        if ($this->check_mobile_phone_number(sanitize_text_field($_POST['mobile_phone']))) {
            $errors->add('duplicate_mobile_phone_number', __('<strong>ERROR</strong>: This mobile_phone is already registered, please choose another one.', 'sms-for-woocommerce'));
        }

        return $errors;
    }

    /**
     * @param $user_id
     */
    public function save_register($user_id) {
        if (isset($_POST['mobile_phone'])) {
            update_user_meta($user_id, 'mobile_phone', sanitize_text_field($_POST['mobile_phone']));
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.2.0
     */
    public function load_international_input() {

        //Register IntelTelInput Assets
        wp_enqueue_style('wpsmstowoo-intel-tel-input', SFW_SMS_TO_WOO_URL . 'assets/css/intlTelInput.min.css', true, SFW_SMS_TO_WOO_VERSION);
        wp_enqueue_script('wpsmstowoo-intel-tel-input', SFW_SMS_TO_WOO_URL . 'assets/js/intel/intlTelInput.min.js', array('jquery'), SFW_SMS_TO_WOO_VERSION, true);
        wp_enqueue_script('wpsmstowoo-intel-script', SFW_SMS_TO_WOO_URL . 'assets/js/intel/intel-script.js', true, SFW_SMS_TO_WOO_VERSION, true);

        // Localize the IntelTelInput
        $tel_intel_vars = array();
        $only_countries_option = SFW_SMS_TO_Option::getOption('international_mobile_phone_only_countries');
        $preferred_countries_option = SFW_SMS_TO_Option::getOption('international_mobile_phone_preferred_countries');

        if ($only_countries_option) {
            $tel_intel_vars['only_countries'] = $only_countries_option;
        } else {
            $tel_intel_vars['only_countries'] = '';
        }

        if ($preferred_countries_option) {
            $tel_intel_vars['preferred_countries'] = $preferred_countries_option;
        } else {
            $tel_intel_vars['preferred_countries'] = '';
        }

        if (SFW_SMS_TO_Option::getOption('international_mobile_phone_auto_hide')) {
            $tel_intel_vars['auto_hide'] = true;
        } else {
            $tel_intel_vars['auto_hide'] = false;
        }

        if (SFW_SMS_TO_Option::getOption('international_mobile_phone_national_mode')) {
            $tel_intel_vars['national_mode'] = true;
        } else {
            $tel_intel_vars['national_mode'] = false;
        }

        if (SFW_SMS_TO_Option::getOption('international_mobile_phone_separate_dial_code')) {
            $tel_intel_vars['separate_dial'] = true;
        } else {
            $tel_intel_vars['separate_dial'] = false;
        }

        $tel_intel_vars['util_js'] = SFW_SMS_TO_WOO_URL . 'assets/js/intel/utils.js';

        wp_localize_script('wpsmstowoo-intel-script', 'sms_to_woo_intel_tel_input', $tel_intel_vars);
    }

}

new SFW_SMS_TO_Features();
