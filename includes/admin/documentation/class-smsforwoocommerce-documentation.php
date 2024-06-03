<?php

namespace SFW_SMS_TO_WOO;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * Class Documentation
 * This plugin is a fork from https://wordpress.org/plugins/wp-sms/ developed by VeronaLabs
 * @author mostafa.s1990, kashani, mehrshaddarzi, alifallahrn, panicoschr10
 * @copyright  2020 VeronaLabs
 * @license    GPLv3
 * @license uri: http://www.gnu.org/licenses/gpl.html
 */
#[\AllowDynamicProperties]
class SFW_SMS_TO_Documentation {
    /*
     * Show MetaBox System
     */

    public function render_page() {
        echo'</br></br></br></br>';
        echo '<div class="wpsmstowoo_registration_table_layout wpsmstowoo-otp-center">';
        echo'	    <table style="width:100%">
	            <tr>
                    <td colspan="">
                        <h2>' . _("DOCUMENTATION") . '
                            <span style="float:right;margin-top:-10px;">
                                <span   class="dashicons dashicons-arrow-up toggle-div" 
                                        data-show="false" 
                                        data-toggle="wpsmstowoo_form_instructions">                                            
                                </span>
                            </span>
                        </h2> <hr>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div id="wpsmstowoo_form_instructions">
                            <div class="wpsmstowoo_otp_note">
                                <b><div class="wpsmstowoo_otp_dropdown_note" data-toggle="how_to_use_the_otp_plugin">
                                    ' . _('ENVIRONMENT') . '
                                    </div></b>
                                <div id="how_to_use_the_otp_plugin" hidden >

</br>
 <strong>     ' . _("GET WORDPRESS") . '</strong>:         
<ol>
    <li>' . _("Visit ") . '
        <i><a href="' . 'https://wordpress.org' . '">' . _("Wordpress") . '</a></i>
    </li>
    <li>' . '
        <i><a href="' . 'https://wordpress.org/download' . '">' . _(" Download ") . '</a></i>Wordpress
    </li>    


    <li>' . _("Copy the contents of the downloaded zip file under your web server");
        echo'									</li>
         
 </i>
 </ol>



  <strong>    ' . _("DB CONFIGURATION") . '</strong>:         
<ol>
    <li>' . _("Visit phpmyadmin");
        echo'									</li>
    <li>' . _("Add User account : wordpress(can be something else) to create the WordPress user to hold WP tables");
        echo'									</li>
    <li>' . _("Host name  : localhost");
        echo'									</li>            
    <li>' . _("Password : wordpress(can be something else)");
        echo'									</li>  
    <li>' . _("Create database with same name and grant all privileges - select it");
        echo'									</li>  
    <li>' . _("Grant all privileges on wildcard name (username_%) - select it");
        echo'									</li>  
    <li>' . _("Global privileges Check all - select it");
        echo'									</li>  
    <li>' . _("Select GO");
        echo'									</li>  
            
   </i>
   </ol>








  <strong>    ' . _("WORDPRESS INSTALLATION") . '</strong>:         
<ol>
    <li>' . _("Browse to the folder where you extracted Wordpress");
        echo'									</li>
    <li>' . _("Database Name : wordpress (as above)");
        echo'									</li>
    <li>' . _("Username : wordpress (as above)");
        echo'									</li>            
    <li>' . _("Password  : wordpress (as above)");
        echo'									</li>  
    <li>' . _("Database Host : localhost (as above)");
        echo'									</li>  
    <li>' . _("Table Prefix : wp_");
        echo'									</li>  
 </i>
 </ol>







  ' . _("If everything was used correctly you should see a message") . ':         
   </br>     </br> <strong> ' . _("All right, sparky! You’ve made it through this part of the installation. "
           . "WordPress can now communicate with your database. If you are ready, time now to…") . '</strong> 
<ol>
    <li>' . _("Run the installation");
        echo'									</li>
    <li>' . _("Provide a Site Title : my_wordpress_site");
        echo'									</li>
    <li>' . _("Username : wordpress (can be something else)");
        echo'									</li>            
    <li>' . _("Password : wordpress (can be something else - can change the proposed one)");
        echo'									</li>  
    <li>' . _("Your Email : wordpress@gmail.com (can be something else)");
        echo'									</li>  
    <li>' . _("Username and Password will be the site's administrator username and password");
        echo'									</li>  
             <li>' . _("Select Install WordPress.");
        echo'									</li>  
             <li>' . _("Once this is done, click Log In and use the site administrator username and password");
        echo'									</li>  
            
   </i>
  </ol>





  <strong>    ' . _("WOOCOMMERCE INSTALLATION") . '</strong>:         
<ol>
    <li>' . _("Log in to WordPress");
        echo'									</li>
    <li>' . _("Visit the plugins page within your dashboard and select ‘Add New’");
        echo'									</li>
            
    <li>' . _("Search for ") . '
        <i><a href="' . 'https://wordpress.org/plugins/woocommerce/' . '">' . _("WooCommerce") . '</a></i>
    </li>

    <li>' . _("Download Woo Commerce");
        echo'									</li>            
    <li>' . _("Activate WooCommerce from your Plugins page");
        echo'									</li>  
   
    </i>
  </ol>




 <strong>     ' . _("ACTIVE MSGOWL ACCOUNT WITH SUFFICIENT FUNDS") . '</strong>:         
<ol>
                                        <li>' . '
                                            <i><a href="' . 'https://msgowl.com/register#/' . '">' . _("Sign Up ") . '</a></i>for an account </li>
                                        </i>
                                    </ol>

</div>
                             </div>
                            



  <div class="wpsmstowoo_otp_note">
                                <b><div class="wpsmstowoo_otp_dropdown_note" data-toggle="how_to_set_up_the_environment">
                                    ' . _('PLUGIN INSTALLATION') . '
                                    </div></b>
                                <div id="how_to_set_up_the_environment" hidden >
                           
   </br>                                 

 <strong>     ' . _("INSTALL MSGOWL for WooCommerce") . '</strong>:         
<ol>
                                        <li>' . _("Upload provided zip file using Plugins page");
        echo'									</li>
    <li>' . _("Activate MSGOWL for WooCommerce from your Plugins page");
        echo'									</li>
   </ol>
 <strong>     ' . _("UNINSTALL MSGOWL for WooCommerce") . '</strong>:         
<ol>
                                        <li>' . _("De-activate MSGOWL for WooCommerce from your Plugins page");
        echo'									</li>
    <li>' . _("Remove .git folder which exists in the MSGOWL for WooCommerce plugin folder(if installed from github)");
        echo'									</li>
    <li>' . _("Delete Plugin");
        echo'									</li>
 
    </i>
   </ol>


</div>
                                

                            </div>





  <div class="wpsmstowoo_otp_note">
                                <b><div class="wpsmstowoo_otp_dropdown_note" data-toggle="how_to_set_up_basic_configuration">
                                    ' . _('BASIC CONFIGURATION') . '
                                    </div></b>
                                <div id="how_to_set_up_basic_configuration" hidden >
                           
   </br>                                 

 <strong>     ' . _("STEP A") . '</strong>:         
<ol>
    <li>' . '
        <i><a href="' . 'https://msgowl.com/login#/' . '">' . _("Sign In") . '</a></i> with your <i><a href="' . 'https://msgowl.com' . '">' . _("Msgowl") . '</a></i> account
    </li>
    
    <li>' .  _("Go to API Key Authentication, enter a Title and generate an API Key with permissions message.read, message.write and account.balance.read");
        echo'									</li>
</br> 
      <img width="800" height="300" src="' . SFW_SMS_TO_WOO_URL . 'assets/images/api_key.png' . '">' . '</img>
          </br> </br>
          
</br> 
      <img width="800" height="400" src="' . SFW_SMS_TO_WOO_URL . 'assets/images/set_api_key.png' . '">' . '</img>
          </br> </br>

    <li>' . _("Copy the generated API Key into the MSGOWL for WooCommerce plugin - Settings - Gateway - API Key");
        echo'									</li>
    <li>' . _("Change Sender to approved sender ID");
        echo'									</li>        
    <li>' . _("Save Changes");
        echo'									</li>              
     </i>
   </ol>


        



<strong>     ' . _("STEP B") . '</strong>:         
<ol>

    <li>' . _("Go to MSGOWL for WooCommerce plugin - Settings - Features - must be selected as below");
        echo'									</li>    

</br> 
      <img width="800" height="400" src="' . SFW_SMS_TO_WOO_URL . 'assets/images/mobile-phone-settings.png' . '">' . '</img>
          </br> </br>            
    </i>
  </ol>

   
  




<strong>     ' . _("STEP C (optional)") . '</strong>:         
<ol>

    <li>' . _("Go to WordPress Users - Your Profile - Add the mobile phone - Update the Profile");
        echo'									</li>    

</br> 
      <img width="800" height="400" src="' . SFW_SMS_TO_WOO_URL . 'assets/images/user-profile-mobile-phone.png' . '">' . '</img>
          </br> </br>        
          

    <li>' . _("Enable WP User registration so you can get the mobile phone during the time of registration");
        echo'									</li>  
    <li>' . _("Go to WordPress - Settings - General - Membership - select it.");
        echo'									</li>  
    <li>' . _("Save Changes");
        echo'									</li>              
            
</br> 
      <img width="800" height="500" src="' . SFW_SMS_TO_WOO_URL . 'assets/images/allow-registration.png' . '">' . '</img>
          </br> </br>  
          
</br> 
      <img width="400" height="600" src="' . SFW_SMS_TO_WOO_URL . 'assets/images/user-registration.png' . '">' . '</img>
          </br> </br>  
    </i>
 </ol>



</div>
  
                            </div>






  <div class="wpsmstowoo_otp_note">
                                <b><div class="wpsmstowoo_otp_dropdown_note" data-toggle="reports">
                                    ' . _('REPORTS') . '
                                    </div></b>
                                <div id="reports" hidden >
                           
   </br>                                 

<strong>     ' . _("MESSAGES : Show single messages") . '</strong>:     
  </br>  </br>
 
          </br> </br> 
          



<ol>

    <li>' . _("Date : Date and Time the message was sent");
        echo'									</li>
    <li>' . _("Message Id : The Id of the message given by SMS to, to your message. This is a unique Id and it can be used for any troubleshooting");
        echo'									</li>            
    <li>' . _("Sender : Sender of the message");
        echo'									</li>
    <li>' . _("Recipient : Recipient(s) of the SMS");
        echo'									</li>            
    
<li>' . _("Message : Content of the SMS");
        echo'									</li>     
</ol>

  </br> 
 </br>

                  

</div>
                                
   </div>


  <div class="wpsmstowoo_otp_note">
                                <b><div class="wpsmstowoo_otp_dropdown_note" data-toggle="general_settings">
                                    ' . _('GENERAL SETTINGS') . '
                                    </div></b>
                                <div id="general_settings" hidden >
                           
   </br>                                 

 <strong>     ' . _("General") . '</strong>:         
<ol>
    
    <li>' . _("Operator mobile phone number :  Operator mobile phone number for getting sms notifications");
        echo'									</li>

          </br> 
      <img width="800" height="350" src="' . SFW_SMS_TO_WOO_URL . 'assets/images/settings-general.png' . '">' . '</img>
          </br> </br> 

         
    </i>
</ol>

</div>
  </div>



  <div class="wpsmstowoo_otp_note">
                                <b><div class="wpsmstowoo_otp_dropdown_note" data-toggle="integration">
                                    ' . _('WOOCOMMERCE') . '
                                    </div></b>
                                <div id="integration" hidden >
                           
   </br>
 <strong>     ' . _("WooCommerce Integration") . '</strong>         
       </br>  </br> 
    
       

          </br> 
      <img width="900" height="1200" src="' . SFW_SMS_TO_WOO_URL . 'assets/images/woo.png' . '">' . '</img>
          </br> </br> 





<ol>
     <strong>  
      ' . _("Sends SMS to Operator when a new order is placed") . '    </strong>  
          
   </br> </br> 
   
    <li>' . _("Status : Set the status to active to enable the notification");
        echo'									</li>
            

    <li>' . _("Message body : This is the message that will be sent to the notification recipient");
        echo'	</li>
   
       </br> </br>  
      <img width="800" height="200" src="' . SFW_SMS_TO_WOO_URL . 'assets/images/new-order.png' . '">' . '</img>
          </br> </br> 
   </i>
</ol>






<ol>
     <strong>  
      ' . _("Sends SMS to SMS Receivers  when a new order is submitted") . '    </strong>  
          
   </br> </br> 
   
    <li>' . _("Status : Set the status to active to enable the notification");
        echo'									</li>
            
    <li>' . _("SMS Receiver : These are the mobile phone numbers of the SMS Receivers. "
            . "You can enter more than one mobile phone number and separate them using comma , ");
        echo'	</li>

    <li>' . _("Message body : This is the message that will be sent to the notification recipient");
        echo'	</li>
   
       </br> </br>  
      <img width="800" height="250" src="' . SFW_SMS_TO_WOO_URL . 'assets/images/new-order-sms-receiver.png' . '">' . '</img>
          </br> </br> 

         
  </i>
 </ol>




    <strong>    ' . _("Orders related Customer Phone Number field") . ' </strong>
     
<ol>
    
      ' . _("Choose from which field you get Customer Phone Number for sending SMS for orders.Select an option from the "
              . "drop down list Customer profile phone number or Customer billing phone number as on order. So, now, when you receive Woo Commerce notifications "
              . "for orders this will use the selected phone. - If you select Order mobile phone and you don’t set a mobile phone on the order, "
              . "no message will be sent.") . '   
   
 </br> </br> 
 
<li>' . _("Customer profile phone number");
        echo'									</li>
            
<li>' . _("Customer billing phone number as on order");
        echo'									</li>



          </br> 
      <img width="800" height="200" src="' . SFW_SMS_TO_WOO_URL . 'assets/images/choose-the-field.png' . '">' . '</img>
          </br> </br> 

         
    </i>
</ol>




<ol>
     <strong>  
      ' . _("Sends SMS to the customer when new order") . '    </strong>  
          
   </br> </br> 
   
    <li>' . _("Status : Set the status to active to enable the notification");
        echo'									</li>
            
    <li>' . _("Message body : This is the message that will be sent to the notification recipient");
        echo'	</li>
   
       </br> </br>  
      <img width="800" height="200" src="' . SFW_SMS_TO_WOO_URL . 'assets/images/notify-customer-order.png' . '">' . '</img>
          </br> </br> 

    </i>
  </ol>




<ol>
     <strong>  
      ' . _("Sends an SMS to the customer when the order status is changed") . '    </strong>  
          
   </br> </br> 
   
    <li>' . _("Status : Set the status to active to enable the notification");
        echo'									</li>
            
    <li>' . _("Message body : This is the message that will be sent to the notification recipient");
        echo'	</li>
   
       </br> </br>  
      <img width="800" height="100" src="' . SFW_SMS_TO_WOO_URL . 'assets/images/notify-of-status-change.png' . '">' . '</img>
          </br> </br> 
   </i>
 </ol>
                                    



<ol>
     <strong>  
      ' . _("Sends an SMS by order status") . '    </strong>  
          
   </br> </br> 
      <img width="800" height="200" src="' . SFW_SMS_TO_WOO_URL . 'assets/images/notify-by-status.png' . '">' . '</img>
          </br> </br> 
          
    <li>' . _("Status : Set the status to active to enable the notification");
        echo'									</li>
   
 <li>'  . _("Order Status & Message : You can define the role, order status and notify status.  ");
        echo'									
            </br> </br>
      ' . _("Define the role : Select a role from the drop down list");
        echo'									
            </br> </br>
            
      <img width="800" height="100" src="' . SFW_SMS_TO_WOO_URL . 'assets/images/notify-by-status-role.png' . '">' . '</img>
          </br> </br> 
          

' . _("Define the order status : Select an order status from the drop down list you want this notification to consider.");
        echo'									
            </br> </br>
            
      <img width="800" height="140" src="' . SFW_SMS_TO_WOO_URL . 'assets/images/notify-by-status-order-status.png' . '">' . '</img>
          </br> </br> 
   

' . _("Define the notify status  : Select a notify status from the drop down list to enable or disable this notification");
        echo'		</li>							
            </br> </br>
            
      <img width="800" height="100" src="' . SFW_SMS_TO_WOO_URL . 'assets/images/notify-by-status-notify-status.png' . '">' . '</img>
          </br> </br> 

    ' . _("Message : This is the message that will be sent to the notification recipient");
        echo'	
      </br> </br> 
      

      <img width="800" height="100" src="' . SFW_SMS_TO_WOO_URL . 'assets/images/notify-of-status-change.png' . '">' . '</img>
          </br> </br> 
  


  <li> ' . _("Add another order status : Using this option you can add as many notifications as you wish - "
            . "You can also delete the ones you no longer wish to have, but instead of deleting them, you "
            . "can just disable them(use the last drop down list) to preserve all the notification information "
            . "(recipients, status, template, wording etc.).");
        echo'	</li>
      </br> </br> 
  </i>
 </ol>

</div>
                        </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>';
    }

}

new SFW_SMS_TO_Documentation();
