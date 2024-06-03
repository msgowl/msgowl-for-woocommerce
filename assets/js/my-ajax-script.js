
window.$  = jQuery
$(document).ready(function(){
    
if (Foo == null || typeof(Foo) != "object") { var Foo = new Object();}    
var mobile_phone = $('#wpsmstowoo-mobile_phone').val();
      
        $.ajax({
            url: ajax_for_frontend.ajaxurl, //the page containing php script
            type: "post", //request type,
        //    dataType: 'json',
           data: {action: "save_and_send_otp_login", mobile_phone: mobile_phone},
           serialize: true,
            success:function(){
          
      
           }
         });
                });



