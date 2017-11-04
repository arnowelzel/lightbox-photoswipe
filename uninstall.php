<?php
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();

function lightbox_photoswipe_delete_tables() {
	global $wpdb;
	
	$table_name = $wpdb->prefix . 'lightbox_photoswipe_img'; 
	$sql = "DROP TABLE IF EXISTS $table_name";
	$wpdb->query($sql);
}

function lightbox_photoswipe_uninstall() {
	global $wpdb;

	if(is_multisite()) {
		$blog_ids = $wpdb->get_col('SELECT blog_id FROM '.$wpdb->blogs);
		foreach($blog_ids as $blog_id) {
			switch_to_blog($blog_id);
			lightbox_photoswipe_delete_tables();
			delete_option('lightbox_photoswipe_db_version');
			restore_current_blog();
		}
	} else {
		lightbox_photoswipe_delete_tables();
		delete_option('lightbox_photoswipe_db_version');
	}
}

lightbox_photoswipe_uninstall();
