jQuery(document).ready(function ($) {
    // Check the GDPR enabled.
    if ($('#wpsmstowoo-gdpr-confirmation').length) {
        if ($('#wpsmstowoo-gdpr-confirmation').attr('checked')) {
            $("#wpsmstowoo-submit").removeAttr('disabled');
        } else {
            $("#wpsmstowoo-submit").attr('disabled', 'disabled');
        }
        $("#wpsmstowoo-gdpr-confirmation").click(function () {
            if (this.checked) {
                $("#wpsmstowoo-submit").removeAttr('disabled');
            } else {
                $("#wpsmstowoo-submit").attr('disabled', 'disabled');
            }
        });
    }

    $("#wpsmstowoo-subscribe #wpsmstowoo-submit").click(function () {
        $("#wpsmstowoo-result").hide();

        var verify = $("#newsletter-form-verify").val();

        subscriber = Array();
        subscriber['name'] = $("#wpsmstowoo-name").val();
        subscriber['mobile_phone'] = $("#wpsmstowoo-mobile_phone").val();
        subscriber['group_id'] = $("#wpsmstowoo-groups").val();
        subscriber['type'] = $('input[name=subscribe_type]:checked').val();

        $("#wpsmstowoo-subscribe").ajaxStart(function () {
            $("#wpsmstowoo-submit").attr('disabled', 'disabled');
            $("#wpsmstowoo-submit").text(wpsmstowoo_ajax_object.loading_text);
        });

        $("#wpsmstowoo-subscribe").ajaxComplete(function () {
            $("#wpsmstowoo-submit").removeAttr('disabled');
            $("#wpsmstowoo-submit").text(wpsmstowoo_ajax_object.subscribe_text);
        });
        if (subscriber['type'] === 'subscribe') {
            var method = 'POST';
        } else {
            var method = 'DELETE';
        }
        var data_obj = Object.assign({}, subscriber);
        var ajax = $.ajax({
            type: method,
            url: wpsmstowoo_ajax_object.ajaxurl,
            data: data_obj
        });
        ajax.fail(function (data) {
            var response = $.parseJSON(data.responseText);
            var message = null;

            if (typeof (response.error) != "undefined" && response.error !== null) {
                message = response.error.message;
            } else {
                message = wpsmstowoo_ajax_object.unknown_error;
            }

            $("#wpsmstowoo-result").fadeIn();
            $("#wpsmstowoo-result").html('<span class="wpsmstowoo-message-error">' + message + '</div>');
        });
        ajax.done(function (data) {
            var response = data;
            var message = response.message;

            $("#wpsmstowoo-result").fadeIn();
            $("#wpsmstowoo-step-1").hide();
            $("#wpsmstowoo-result").html('<span class="wpsmstowoo-message-success">' + message + '</div>');
            if (subscriber['type'] === 'subscribe' && verify === '1') {
                $("#wpsmstowoo-step-2").show();
            }
        });
    });

    $("#wpsmstowoo-subscribe #activation").on('click', function () {
        $("#wpsmstowoo-result").hide();
        subscriber['activation'] = $("#wpsmstowoo-ativation-code").val();

        $("#wpsmstowoo-subscribe").ajaxStart(function () {
            $("#activation").attr('disabled', 'disabled');
            $("#activation").text(wpsmstowoo_ajax_object.loading_text);
        });

        $("#wpsmstowoo-subscribe").ajaxComplete(function () {
            $("#activation").removeAttr('disabled');
            $("#activation").text(wpsmstowoo_ajax_object.activation_text);
        });

        var data_obj = Object.assign({}, subscriber);
        var ajax = $.ajax({
            type: 'PUT',
            url: wpsmstowoo_ajax_object.ajaxurl,
            data: data_obj
        });
        ajax.fail(function (data) {
            var response = $.parseJSON(data.responseText);
            var message = null;

            if (typeof (response.error) != "undefined" && response.error !== null) {
                message = response.error.message;
            } else {
                message = wpsmstowoo_ajax_object.unknown_error;
            }

            $("#wpsmstowoo-result").fadeIn();
            $("#wpsmstowoo-result").html('<span class="wpsmstowoo-message-error">' + message + '</div>');
        });
        ajax.done(function (data) {
            var response = data;
            var message = response.message;

            $("#wpsmstowoo-result").fadeIn();
            $("#wpsmstowoo-step-2").hide();
            $("#wpsmstowoo-result").html('<span class="wpsmstowoo-message-success">' + message + '</div>');
        });
    });
});