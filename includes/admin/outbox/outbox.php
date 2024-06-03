<div class="wrap">
    <h2><?php _e( 'Reports', 'wp-sms-to-woo' ); ?></h2>
    <?php $list_table->views(); ?>
    <form id="outbox-filter" method="get">
        <input type="hidden" name="page" value="<?php echo esc_html( $_REQUEST['page'] ); ?>"/>
        <?php $list_table->search_box( __( 'Search', 'wp-sms-to-woo' ), 'search_id' ); ?>
        <?php $list_table->display(); ?>
        <input type="hidden" id="ajax-url" value="<?php echo admin_url('admin-ajax.php'); ?>">
    </form>
</div>