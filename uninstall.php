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
            delete_option('lightbox_photoswipe_share_facebook');
            delete_option('lightbox_photoswipe_share_pinterest');
            delete_option('lightbox_photoswipe_share_twitter');
            delete_option('lightbox_photoswipe_share_download');
            delete_option('lightbox_photoswipe_close_on_scroll');
            delete_option('lightbox_photoswipe_close_on_drag');
            delete_option('lightbox_photoswipe_show_counter');
            delete_option('lightbox_photoswipe_skin');
            delete_option('lightbox_photoswipe_show_zoom');
            delete_option('lightbox_photoswipe_show_caption');
            delete_option('lightbox_photoswipe_spacing');
            delete_option('lightbox_photoswipe_loop');
            delete_option('lightbox_photoswipe_pinchtoclose');
            delete_option('lightbox_photoswipe_usepostdata');
            delete_option('lightbox_photoswipe_show_fullscreen');
            restore_current_blog();
        }
    } else {
        lightboxPhotoswipeDeleteTables();
        delete_option('lightbox_photoswipe_db_version');
    }
}

lightboxPhotoswipeUninstall();