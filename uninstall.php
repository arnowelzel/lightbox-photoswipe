<?php
if(!defined('WP_UNINSTALL_PLUGIN')) exit();

/**
 * Delete tables when uninstalling the plugin
 * 
 * @return void
 */
function lightboxPhotoswipeDeleteTables()
{
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'lightbox_photoswipe_img'; 
    $sql = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query($sql);
}

/**
 * Cleanup when uninstalling the plugin
 * 
 * @return void
 */
function lightboxPhotoswipeUninstall()
{
    global $wpdb;

    if (is_multisite()) {
        $blog_ids = $wpdb->get_col('SELECT blog_id FROM '.$wpdb->blogs);
        foreach ($blog_ids as $blog_id) {
            switch_to_blog($blog_id);
            lightboxPhotoswipeDeleteTables();
            delete_option('lightbox_photoswipe_db_version');
            restore_current_blog();
        }
    } else {
        lightboxPhotoswipeDeleteTables();
        delete_option('lightbox_photoswipe_db_version');
    }
}

lightboxPhotoswipeUninstall();