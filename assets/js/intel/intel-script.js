jQuery(document).ready(function () {
    var input = document.querySelectorAll(".sms-for-woocommerce-input-mobile_phone, #sms-for-woocommerce-input-mobile_phone, .user-mobile_phone-wrap #mobile_phone, .mobile_phone #mobile_phone");

    for (var i = 0; i < input.length; i++) {
        if (input[i]) {
            window.intlTelInput(input[i], {
                onlyCountries: sms_to_woo_intel_tel_input.only_countries,
                preferredCountries: sms_to_woo_intel_tel_input.preferred_countries,
                autoHideDialCode: sms_to_woo_intel_tel_input.auto_hide,
                nationalMode: sms_to_woo_intel_tel_input.national_mode,
                separateDialCode: sms_to_woo_intel_tel_input.separate_dial,
                utilsScript: sms_to_woo_intel_tel_input.util_js
            });
        }
    }

    var input = document.querySelector("#job_mobile_phone, #_job_mobile_phone");
    if (input && !input.getAttribute('placeholder')) {
        window.intlTelInput(input, {
            onlyCountries: sms_to_woo_intel_tel_input.only_countries,
            preferredCountries: sms_to_woo_intel_tel_input.preferred_countries,
            autoHideDialCode: sms_to_woo_intel_tel_input.auto_hide,
            nationalMode: sms_to_woo_intel_tel_input.national_mode,
            separateDialCode: sms_to_woo_intel_tel_input.separate_dial,
            utilsScript: sms_to_woo_intel_tel_input.util_js
        });
    }

});