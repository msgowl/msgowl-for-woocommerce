<?php

namespace SFW_SMS_TO_WOO;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * This plugin is a fork from https://wordpress.org/plugins/wp-sms/ developed by VeronaLabs
 * @author mostafa.s1990, kashani, mehrshaddarzi, alifallahrn, panicoschr10
 * @copyright  2020 VeronaLabs
 * @license    GPLv3
 * @license uri: http://www.gnu.org/licenses/gpl.html
 */
#[\AllowDynamicProperties]
class SFW_SMS_TO_Outbox_List_Table extends \WP_List_Table {

	protected $db;
	protected $tb_prefix;
	protected $limit;
	protected $count;
	var $data;

	function __construct() {
		global $wpdb;

		//Set parent defaults
		parent::__construct( array(
			'singular' => 'ID',     //singular name of the listed records
			'plural'   => 'ID',    //plural name of the listed records
			'ajax'     => false        //does this table support ajax?
		) );
		$this->db        = $wpdb;
		$this->tb_prefix = $wpdb->prefix;
		$this->count     = $this->get_total();
		$this->limit     = 50;
		$this->data      = $this->get_data();
                   }

                   
	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'date':
				return sprintf( __( '%s <span class="wpsmstowoo-time">Time: %s</span>', 'sms-for-woocommerce' ), date_i18n( 'Y-m-d', strtotime( esc_html($item[ $column_name ] )) ), date_i18n( 'H:i:s', strtotime( $item[ $column_name ] ) ) );
			case '_id':
				//return $item[ $column_name ];
                                return sprintf( __( '<span> %s</span>', 'sms-for-woocommerce' ), $item[ $column_name ]);
                        case 'sender':
				return esc_html($item[ $column_name ]);                            
                        case 'type':
				return esc_html($item[ $column_name ]);
			case 'cost':
                                return isset($item[ $column_name ]) ? esc_html($item[ $column_name ]) : '---' ;
			case 'message_count':
                             if (isset($item[ 'sent' ]) && isset($item[ 'failed' ]) && isset($item[ 'pending' ])) {
				return ($item[ 'sent' ] + $item[ 'failed' ] + $item[ 'pending' ]);
                             } else {
                                 return '';
                             }
                        case 'sms_parts':
				return esc_html($item[ $column_name ]);
			case 'sent':
				return esc_html($item[ $column_name ]);
			case 'failed':
				return esc_html($item[ $column_name ]);
			case 'message':
				return esc_html($item[ $column_name ]);                            
                        case 'pending':
				return esc_html($item[ $column_name ]);
			case 'recipient':            
                return esc_html($item[ $column_name ]);

			case 'status':
				if (( $item[ $column_name ] == 'SENT' ) || ( $item[ $column_name ] == 'DELIVERED' ) || ( $item[ $column_name ] == 'DONE' )) {
					return '<span class="sms_to_woo_status_success">' . __( esc_html($item[ $column_name ]), 'sms-for-woocommerce' ) . '</span>';
				} 
				if (( $item[ $column_name ] == 'FAILED' ) || 
                                        ( $item[ $column_name ] == 'REJECTED' ) ||
                                        ( $item[ $column_name ] == 'UNDELIVERED' ) ||
                                        ( $item[ $column_name ] == 'EXPIRED' ) ||
                                        ( $item[ $column_name ] == 'UNSUBSCRIBED' )) {
					return '<span class="sms_to_woo_status_fail">' . __( esc_html($item[ $column_name ]), 'sms-for-woocommerce' ) . '</span>';
				}                               
				if (( $item[ $column_name ] == 'ONGOING' ) ||
                                        ( $item[$column_name] == 'SENDING' ) ||
                        ( $item[$column_name] == 'QUEUED' ) ||
                        ( $item[$column_name] == 'SCHEDULED' )) {
                    return '<span class="sms_to_woo_status_processing">' . __(esc_html($item[$column_name]), 'sms-for-woocommerce') . '</span>';
                }
                        default: {
                            $results = array_map( 'sanitize_text_field', explode(",", $item[$column_name]) );    

                            foreach ($results as $result) {
                                if ((strpos($result, 'SENT')) || 
                                        (strpos($result, 'DELIVERED'))){
                                echo '<span class="sms_to_woo_status_success">' . __(esc_html($result), 'sms-for-woocommerce') . '</span>';
                                }
                                if ((strpos($result, 'ONGOING')) || 
                                        (strpos($result, 'SENDING')) || 
                                        (strpos($result, 'QUEUED')) || 
                                        (strpos($result, 'SCHEDULED'))){
                                echo '<span class="sms_to_woo_status_processing">' . __(esc_html($result), 'sms-for-woocommerce') . '</span>';
                                }            
                                if ((strpos($result, 'FAILED')) || 
                                        (strpos($result, 'REJECTED')) || 
                                        (strpos($result, 'UNDELIVERED')) || 
                                        (strpos($result, 'EXPIRED')) || 
                                        (strpos($result, 'UNSUBSCRIBED'))){
                                echo '<span class="sms_to_woo_status_fail">' . __(esc_html($result), 'sms-for-woocommerce') . '</span>';
                                }                                  
                            }
                            }
                 }
    }

    function column__id($item ) {


		$actions = array(
			'resend' => sprintf( '<a href="?page=%s&action=%s&ID=%s">' . __( 'Resend', 'sms-for-woocommerce' ) . '</a>', sanitize_text_field($_REQUEST['page']), 'resend', $item['ID'] ),
			'delete' => sprintf( '<a href="?page=%s&action=%s&ID=%s">' . __( 'Delete', 'sms-for-woocommerce' ) . '</a>', sanitize_text_field($_REQUEST['page']), 'delete', $item['ID'] ),
                );                  

		//Return the title contents
		return sprintf( '%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
			/*$1%s*/
			$item['_id'],
			/*$2%s*/
			$item['ID'],
			/*$3%s*/
			$this->row_actions( $actions )
		);
	}

	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/
			$this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/
			$item['ID']                //The value of the checkbox should be the record's id
		);
	}

	function get_columns() {
     
        return array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'date' => __('Date', 'sms-for-woocommerce'),            
            '_id' => __('Message Id', 'sms-for-woocommerce'),
            'sender' => __('Sender', 'sms-for-woocommerce'),
            'recipient' => __('Recipient', 'sms-for-woocommerce'),
            'message' => __('Message', 'sms-for-woocommerce'),      
            'status' => __( 'Status', 'sms-for-woocommerce' ),
		);
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'ID'        => array( 'ID', true ),     //true means it's already sorted
			'sender'    => array( 'sender', false ),     //true means it's already sorted
			'date'      => array( 'date', false ),  //true means it's already sorted
			'_id'      => array( '_id', false ),  //true means it's already sorted
			'type'      => array( 'type', false ),  //true means it's already sorted
			'message_count'      => array( 'message_count', false ),  //true means it's already sorted
			'sms_parts'      => array( 'sms_parts', false ),  //true means it's already sorted
			'sent'      => array( 'sent', false ),  //true means it's already sorted
			'failed'      => array( 'failed', false ),  //true means it's already sorted
			'pending'      => array( 'pending', false ),  //true means it's already sorted
			'message'   => array( 'message', false ),   //true means it's already sorted
			'recipient' => array( 'recipient', false ), //true means it's already sorted
			'status'    => array( 'status', false ) //true means it's already sorted

		);

		return $sortable_columns;
	}

	function get_bulk_actions() {
		$actions = array(
			'bulk_delete' => __( 'Delete', 'sms-for-woocommerce' )
		);

		return $actions;
	}

        function redirect($url = false)
         {
           if(headers_sent())
           {
             $destination = (esc_url($url) == false ? 'location.reload();' : 'window.location.href="' . esc_url($url) . '";');
             echo die('<script>' . $destination . '</script>');
           }
           else
           {
             $destination = (esc_url($url) == false ? esc_url($_SERVER['REQUEST_URI']) : esc_url($url));
             header('Location: ' . $destination);
             die();
           }    
         }        
        
	function process_bulk_action() {
         
		//Detect when a bulk action is being triggered...
		// Search action
        if (isset($_GET['s'])) {
            $prepare = $this->db->prepare("SELECT * from `{$this->tb_prefix}msgowl_woo_send` WHERE (message LIKE %s OR recipient LIKE %s) ORDER BY date DESC ", '%' . $this->db->esc_like($_GET['s']) . '%', '%' . $this->db->esc_like($_GET['s']) . '%');
            $this->data = $this->get_data($prepare);
            $this->count = $this->get_total($prepare);
        } else {
            $prepare = $this->db->prepare("SELECT * from `{$this->tb_prefix}msgowl_woo_send` ORDER BY date DESC ");
            $this->data = $this->get_data($prepare);
            $this->count = $this->get_total($prepare);
        }

		// Bulk delete action
		if ( 'bulk_delete' == $this->current_action() ) {
                    
                        $ids = array_map( 'sanitize_text_field', $_GET['id'] ); 
                  
			foreach ( $ids as $id ) {
				$this->db->delete( $this->tb_prefix . "msgowl_woo_send", array( 'ID' => $id ) );
			}
			$this->data  = $this->get_data();
			$this->count = $this->get_total();
			echo '<div class="notice notice-success is-dismissible"><p>' . __( 'Items removed.', 'sms-for-woocommerce' ) . '</p></div>';
		}

		// Single delete action
		if ( 'delete' == $this->current_action() ) {
			$this->db->delete( $this->tb_prefix . "msgowl_woo_send", array( 'ID' => sanitize_text_field($_GET['ID']) ) );
			$this->data  = $this->get_data();
			$this->count = $this->get_total();
			echo '<div class="notice notice-success is-dismissible"><p>' . __( 'Item removed.', 'sms-for-woocommerce' ) . '</p></div>';
		}

		// Resend sms
		if ( 'resend' == $this->current_action() ) {
			global $wpsmstowoo;
			$error    = null;
			$result   = $this->db->get_row( $this->db->prepare( "SELECT * from `{$this->tb_prefix}msgowl_woo_send` WHERE ID =%s;", sanitize_text_field($_GET['ID'] )) );
                        $toArray = array_map( 'sanitize_text_field', explode(",", $result->recipient) );    
			$wpsmstowoo->to  = $toArray;
			$wpsmstowoo->msg = $result->message;
            $wpsmstowoo->from = $result->sender;
			$error    = $wpsmstowoo->SendSMS();
			if ( is_wp_error( $error ) ) { 
				echo '<div class="notice notice-error  is-dismissible"><p>' . esc_html($error->get_error_message()) . '</p></div>';
			} else {
				echo '<div class="notice notice-success is-dismissible"><p>' . __( 'The SMS sent successfully.', 'sms-for-woocommerce' ) . '</p></div>';
			}
			$this->data  = $this->get_data();
			$this->count = $this->get_total();

			$url = admin_url( 'admin.php?page=sms-for-woocommerce-outbox');  
                        
            $this->redirect(esc_url($url));
		}

	}

	function prepare_items() {

        /**
		 * First, lets decide how many records per page to show
		 */
		$per_page = $this->limit;

		/**
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array( $columns, $hidden, $sortable );

		/**
		 * Optional. You can handle your bulk actions however you see fit. In this
		 * case, we'll handle them within our package just to keep things clean.
		 */
		$this->process_bulk_action();
                
		/**
		 * Instead of querying a database, we're going to fetch the example data
		 * property we created for use in this plugin. This makes this example
		 * package slightly different than one you might build on your own. In
		 * this example, we'll be using array manipulation to sort and paginate
		 * our data. In a real-world implementation, you will probably want to
		 * use sort and pagination data to build a custom query instead, as you'll
		 * be able to use your precisely-queried data immediately.
		 */
		$data = $this->data;
                
		/**
		 * This checks for sorting input and sorts the data in our array accordingly.
		 *
		 * In a real-world situation involving a database, you would probably want
		 * to handle sorting by passing the 'orderby' and 'order' values directly
		 * to a custom query. The returned data will be pre-sorted, and this array
		 * sorting technique would be unnecessary.
		 */

		usort( $data, '\SFW_SMS_TO_WOO\SFW_SMS_TO_Outbox_List_Table::usort_reorder' );

		/**
		 * REQUIRED for pagination. Let's check how many items are in our data array.
		 * In real-world use, this would be the total number of items in your database,
		 * without filtering. We'll need this later, so you should always include it
		 * in your own package classes.
		 */
		$total_items = $this->count;

		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = $data;

		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
			'total_items' => $total_items,                  //WE have to calculate the total number of items
			'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
			'total_pages' => ceil( $total_items / $per_page )   //WE have to calculate the total number of pages
		) );
	}

	/**
	 * Usort Function
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return array
	 */
	function usort_reorder( $a, $b ) {
		$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_text_field($_REQUEST['orderby']) : 'date'; //If no sort, default to sender
		$order   = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_text_field($_REQUEST['order']) : 'desc'; //If no order, default to asc
		$result  = strcmp( $a[ $orderby ], $b[ $orderby ] ); //Determine sort order

		return ( $order === 'asc' ) ? $result : - $result; //Send final sort direction to usort
	}

	//set $per_page item as int number
    function get_data($query = '') {
        $page_number = ( $this->get_pagenum() - 1 ) * $this->limit;


        
        if (!$query) {
            $query = 'SELECT * FROM `' . $this->tb_prefix . 'msgowl_woo_send` ORDER BY date DESC LIMIT ' . $this->limit . ' OFFSET ' . $page_number;
        } else {
            $query .= ' LIMIT ' . $this->limit . ' OFFSET ' . $page_number;
        }
        $result = $this->db->get_results($query, ARRAY_A);

        return $result;
	}

	//get total items on different Queries
	function get_total( $query = '' ) {
            
            if (!$query) {
                $query = 'SELECT * FROM `' . $this->tb_prefix . 'msgowl_woo_send` ORDER BY date DESC ';
            }
            $result = $this->db->get_results($query, ARRAY_A);
            $result = count($result);

            return $result;
	}

}

// Outbox page class
class Outbox {
	/**
	 * Outbox sms admin page
	 */
	public function render_page() {
		include_once SFW_SMS_TO_WOO_DIR . 'includes/admin/outbox/class-smsforwoocommerce-outbox.php';

		//Create an instance of our package class...
		$list_table = new SFW_SMS_TO_Outbox_List_Table;

		//Fetch, prepare, sort, and filter our data...
		$list_table->prepare_items();
                
		include_once SFW_SMS_TO_WOO_DIR . "includes/admin/outbox/outbox.php";
	}
}