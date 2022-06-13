<?php
namespace LightboxPhotoSwipe;

use LightboxPhotoSwipe\OptionsManager;

require(__DIR__ . '/vendor/autoload.php');

if(!defined('WP_UNINSTALL_PLUGIN')) exit();

/**
 * Make sure the old caching tables are removed when uninstalling the plugin
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

    $optionsManager = new OptionsManager();

    if (is_multisite()) {
        $blog_ids = $wpdb->get_col('SELECT blog_id FROM '.$wpdb->blogs);
        foreach ($blog_ids as $blog_id) {
            switch_to_blog($blog_id);
            lightboxPhotoswipeDeleteTables();
            $optionsManager->deleteOptions();
            wp_clear_scheduled_hook('lbwps_cleanup');
            restore_current_blog();
        }
    } else {
        lightboxPhotoswipeDeleteTables();
        wp_clear_scheduled_hook('lbwps_cleanup');
        $optionsManager->deleteOptions();
    }
}

lightboxPhotoswipeUninstall();