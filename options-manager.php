<?php

namespace LightboxPhotoSwipe;

class OptionsManager
{
    var $disabled_post_ids;
    var $disabled_post_types;
    var $metabox;
    var $share_facebook;
    var $share_pinterest;
    var $share_twitter;
    var $share_download;
    var $share_direct;
    var $share_copyurl;
    var $share_custom;
    var $share_custom_link;
    var $share_custom_label;
    var $wheelmode;
    var $close_on_drag;
    var $history;
    var $show_counter;
    var $skin;
    var $use_postdata;
    var $use_description;
    var $use_title;
    var $use_caption;
    var $enabled;
    var $close_on_click;
    var $fulldesktop;
    var $use_alt;
    var $show_exif;
    var $separate_galleries;
    var $desktop_slider;
    var $idletime;
    var $add_lazyloading;
    var $use_cache;
    var $ignore_external;
    var $ignore_hash;
    var $cdn_url;
    var $cdn_mode;
    var $hide_scrollbars;
    var $fix_links;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->loadOptions();
    }

    public function registerOptions()
    {
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_disabled_post_ids');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_disabled_post_types');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_metabox');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_share_facebook');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_share_twitter');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_share_pinterest');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_share_download');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_share_direct');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_share_copyurl');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_share_custom');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_share_custom_label');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_share_custom_link');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_wheelmode');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_close_on_drag');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_history');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_show_counter');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_show_fullscreen');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_show_zoom');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_show_caption');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_loop');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_pinchtoclose');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_taptotoggle');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_skin');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_spacing');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_usepostdata');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_usedescription');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_usetitle');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_usecaption');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_close_on_click');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_fulldesktop');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_use_alt');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_showexif');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_showexif_date');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_separate_galleries');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_desktop_slider');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_idletime');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_add_lazyloading');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_use_cache');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_ignore_external');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_ignore_hash');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_cdn_url');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_cdn_mode');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_hide_scrollbars');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_svg_scaling');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_fix_links');
    }

    /**
     * Load options
     *
     * @return void
     */
    public function loadOptions()
    {
        $disabled_post_ids = trim(get_option('lightbox_photoswipe_disabled_post_ids'));
        if ('' !== $disabled_post_ids) {
            $this->disabled_post_ids = explode(',', $disabled_post_ids);
        } else {
            $this->disabled_post_ids = [];
        }
        $disabled_post_types = trim(get_option('lightbox_photoswipe_disabled_post_types'));
        if ('' !== $disabled_post_types) {
            $this->disabled_post_types = explode(',', $disabled_post_types);
        } else {
            $this->disabled_post_types = [];
        }
        $this->metabox = get_option('lightbox_photoswipe_metabox');
        $this->share_facebook = get_option('lightbox_photoswipe_share_facebook');
        $this->share_pinterest = get_option('lightbox_photoswipe_share_pinterest');
        $this->share_twitter = get_option('lightbox_photoswipe_share_twitter');
        $this->share_download = get_option('lightbox_photoswipe_share_download');
        $this->share_direct = get_option('lightbox_photoswipe_share_direct');
        $this->share_copyurl = get_option('lightbox_photoswipe_share_copyurl');
        $this->share_custom = get_option('lightbox_photoswipe_share_custom');
        $this->share_custom_link = get_option('lightbox_photoswipe_share_custom_link');
        $this->share_custom_label = get_option('lightbox_photoswipe_share_custom_label');
        $this->wheelmode = get_option('lightbox_photoswipe_wheelmode');
        $this->close_on_drag = get_option('lightbox_photoswipe_close_on_drag');
        $this->history = get_option('lightbox_photoswipe_history');
        $this->show_counter = get_option('lightbox_photoswipe_show_counter');
        $this->show_fullscreen = get_option('lightbox_photoswipe_show_fullscreen');
        $this->show_zoom = get_option('lightbox_photoswipe_show_zoom');
        $this->show_caption = get_option('lightbox_photoswipe_show_caption');
        $this->loop = get_option('lightbox_photoswipe_loop');
        $this->pinchtoclose = get_option('lightbox_photoswipe_pinchtoclose');
        $this->taptotoggle = get_option('lightbox_photoswipe_taptotoggle');
        $this->spacing = get_option('lightbox_photoswipe_spacing');
        $this->skin = get_option('lightbox_photoswipe_skin');
        $this->use_postdata = get_option('lightbox_photoswipe_usepostdata');
        $this->use_description = get_option('lightbox_photoswipe_usedescription');
        $this->use_title = get_option('lightbox_photoswipe_usetitle');
        $this->use_caption = get_option('lightbox_photoswipe_usecaption');
        $this->close_on_click = get_option('lightbox_photoswipe_close_on_click');
        $this->fulldesktop = get_option('lightbox_photoswipe_fulldesktop');
        $this->use_alt = get_option('lightbox_photoswipe_use_alt');
        $this->show_exif = get_option('lightbox_photoswipe_showexif');
        $this->show_exif_date = get_option('lightbox_photoswipe_showexif_date');
        $this->separate_galleries = get_option('lightbox_photoswipe_separate_galleries');
        $this->desktop_slider = get_option('lightbox_photoswipe_desktop_slider');
        $this->idletime = get_option('lightbox_photoswipe_idletime');
        $this->add_lazyloading = get_option('lightbox_photoswipe_add_lazyloading');
        $this->use_cache = get_option('lightbox_photoswipe_use_cache');
        $this->ignore_external = get_option('lightbox_photoswipe_ignore_external');
        $this->ignore_hash = get_option('lightbox_photoswipe_ignore_hash');
        $this->cdn_url = get_option('lightbox_photoswipe_cdn_url');
        $this->cdn_mode = get_option('lightbox_photoswipe_cdn_mode');
        $this->hide_scrollbars = get_option('lightbox_photoswipe_hide_scrollbars');
        $this->svg_scaling = get_option('lightbox_photoswipe_svg_scaling');
        $this->fix_links = get_option('lightbox_photoswipe_fix_links');
    }

    /**
     * Set default options for new blog
     *
     * @return void
     */
    public function setDefaultOptions()
    {
        update_option('lightbox_photoswipe_share_facebook', '1');
        update_option('lightbox_photoswipe_share_pinterest', '1');
        update_option('lightbox_photoswipe_share_twitter', '1');
        update_option('lightbox_photoswipe_share_download', '1');
        update_option('lightbox_photoswipe_share_direct', '0');
        update_option('lightbox_photoswipe_share_copyurl', '0');
        update_option('lightbox_photoswipe_close_on_scroll', '1');
        update_option('lightbox_photoswipe_close_on_drag', '1');
        update_option('lightbox_photoswipe_show_counter', '1');
        update_option('lightbox_photoswipe_show_fullscreen', '1');
        update_option('lightbox_photoswipe_show_zoom', '1');
        update_option('lightbox_photoswipe_show_caption', '1');
        update_option('lightbox_photoswipe_loop', '1');
        update_option('lightbox_photoswipe_pinchtoclose', '1');
        update_option('lightbox_photoswipe_taptotoggle', '1');
        update_option('lightbox_photoswipe_skin', '3');
        update_option('lightbox_photoswipe_spacing', '12');
        update_option('lightbox_photoswipe_close_on_click', '1');
        update_option('lightbox_photoswipe_fulldesktop', '0');
        update_option('lightbox_photoswipe_usecaption', '1');
        update_option('lightbox_photoswipe_usetitle', '0');
        update_option('lightbox_photoswipe_use_alt', '0');
        update_option('lightbox_photoswipe_showexif', '0');
        update_option('lightbox_photoswipe_separate_galleries', '0');
        update_option('lightbox_photoswipe_desktop_slider', '1');
        update_option('lightbox_photoswipe_idletime', '4000');
        update_option('lightbox_photoswipe_add_lazyloading', '1');
        update_option('lightbox_photoswipe_use_cache', '0');
        update_option('lightbox_photoswipe_ignore_external', '0');
        update_option('lightbox_photoswipe_ignore_hash', '0');
        update_option('lightbox_photoswipe_cdn_url', '');
        update_option('lightbox_photoswipe_cdn_mode', 'prefix');
        update_option('lightbox_photoswipe_hide_scrollbars', '1');
        update_option('lightbox_photoswipe_fix_links', '1');
        update_option('lightbox_photoswipe_svg_scaling', '200');
    }

    /**
     * Enqueue options for frontend script
     *
     * @return void
     */
    public function enqueueFrontendOptions()
    {
        $translation_array = [
            'label_facebook' => __('Share on Facebook', 'lightbox-photoswipe'),
            'label_twitter' => __('Tweet', 'lightbox-photoswipe'),
            'label_pinterest' => __('Pin it', 'lightbox-photoswipe'),
            'label_download' => __('Download image', 'lightbox-photoswipe'),
            'label_copyurl' => __('Copy image URL', 'lightbox-photoswipe')
        ];
        $translation_array['share_facebook'] = ($this->share_facebook == '1')?'1':'0';
        $translation_array['share_twitter'] = ($this->share_twitter == '1')?'1':'0';
        $translation_array['share_pinterest'] = ($this->share_pinterest == '1')?'1':'0';
        $translation_array['share_download'] = ($this->share_download == '1')?'1':'0';
        $translation_array['share_direct'] = ($this->share_direct == '1')?'1':'0';
        $translation_array['share_copyurl'] = ($this->share_copyurl == '1')?'1':'0';
        $customlink = ('' === $this->share_custom_link)?'{{raw_image_url}}':$this->share_custom_link;
        $translation_array['share_custom_label'] = ($this->share_custom == '1')?htmlspecialchars($this->share_custom_label):'';
        $translation_array['share_custom_link'] = ($this->share_custom == '1')?htmlspecialchars($customlink):'';
        $translation_array['wheelmode'] = htmlspecialchars($this->wheelmode);
        $translation_array['close_on_drag'] = ($this->close_on_drag == '1')?'1':'0';
        $translation_array['history'] = ($this->history == '1')?'1':'0';
        $translation_array['show_counter'] = ($this->show_counter == '1')?'1':'0';
        $translation_array['show_fullscreen'] = ($this->show_fullscreen == '1')?'1':'0';
        $translation_array['show_zoom'] = ($this->show_zoom == '1')?'1':'0';
        $translation_array['show_caption'] = ($this->show_caption == '1')?'1':'0';
        $translation_array['loop'] = ($this->loop == '1')?'1':'0';
        $translation_array['pinchtoclose'] = ($this->pinchtoclose == '1')?'1':'0';
        $translation_array['taptotoggle'] = ($this->taptotoggle == '1')?'1':'0';
        $translation_array['spacing'] = intval($this->spacing);
        $translation_array['close_on_click'] = ($this->close_on_click == '1')?'1':'0';
        $translation_array['fulldesktop'] = ($this->fulldesktop == '1')?'1':'0';
        $translation_array['use_alt'] = ($this->use_alt == '1')?'1':'0';
        $translation_array['use_caption'] = ($this->use_caption == '1')?'1':'0';
        $translation_array['desktop_slider'] = ($this->desktop_slider == '1')?'1':'0';
        $translation_array['idletime'] = intval($this->idletime);
        $translation_array['hide_scrollbars'] = intval($this->hide_scrollbars);
        wp_localize_script('lbwps', 'lbwpsOptions', $translation_array);
    }

    /**
     * Outout page for backend settings
     *
     * @return void
     */
    public function outputAdminSettingsPage()
    {
        global $wpdb;
?>
        <style>
            .lbwps_text {
                font-size:14px;
            }
            .lbwps_text:first-child {
                padding-top:15px;
            }
        </style>
        <div class="wrap"><h1><?php echo __('Lightbox with PhotoSwipe', 'lightbox-photoswipe'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('lightbox-photoswipe-settings-group'); ?>
                <script>
                    function lbwpsUpdateDescriptionCheck(checkbox)
                    {
                        let useDescription = document.getElementById("lightbox_photoswipe_usedescription");
                        if (checkbox.checked) {
                            useDescription.disabled = false;
                        } else {
                            useDescription.disabled = true;
                        }
                    }

                    function lbwpsUpdateExifDateCheck(checkbox)
                    {
                        let showExifDate = document.getElementById("lightbox_photoswipe_showexif_date");
                        if (checkbox.checked) {
                            showExifDate.disabled = false;
                        } else {
                            showExifDate.disabled = true;
                        }
                    }

                    function lbwpsSwitchTab(tab)
                    {
                        let num=1;
                        while (num < 8) {
                            if (tab == num) {
                                document.getElementById('lbwps-switch-'+num).classList.add('nav-tab-active');
                                document.getElementById('lbwps-tab-'+num).style.display = 'block';
                            } else {
                                document.getElementById('lbwps-switch-'+num).classList.remove('nav-tab-active');
                                document.getElementById('lbwps-tab-'+num).style.display = 'none';
                            }
                            num++;
                        }
                        document.getElementById('lbwps-switch-'+tab).blur();
                        if (tab == 1 && ("pushState" in history)) {
                            history.pushState("", document.title, window.location.pathname+window.location.search);
                        } else {
                            location.hash = 'tab-' + tab;
                        }
                        let referrer = document.getElementsByName('_wp_http_referer');
                        if (referrer[0]) {
                            let parts = referrer[0].value.split('#');
                            if (tab>1) {
                                referrer[0].value = parts[0] + '#tab-' + tab;
                            } else {
                                referrer[0].value = parts[0];
                            }
                        }
                    }

                    function lbwpsUpdateCurrentTab()
                    {
                        if(location.hash == '') {
                            lbwpsSwitchTab(1);
                        } else {
                            let num = 1;
                            while (num < 8) {
                                if (location.hash == '#tab-' + num) lbwpsSwitchTab(num);
                                num++;
                            }
                        }
                    }
                </script>
                <nav class="nav-tab-wrapper" aria-label="<?php echo __('Secondary menu'); ?>">
                    <a href="#" id="lbwps-switch-1" class="nav-tab nav-tab-active" onclick="lbwpsSwitchTab(1);return false;"><?php echo __('General', 'lightbox-photoswipe'); ?></a>
                    <a href="#" id="lbwps-switch-2" class="nav-tab" onclick="lbwpsSwitchTab(2);return false;"><?php echo __('Theme', 'lightbox-photoswipe'); ?></a>
                    <a href="#" id="lbwps-switch-3" class="nav-tab" onclick="lbwpsSwitchTab(3);return false;"><?php echo __('Captions', 'lightbox-photoswipe'); ?></a>
                    <a href="#" id="lbwps-switch-4" class="nav-tab" onclick="lbwpsSwitchTab(4);return false;"><?php echo __('Sharing', 'lightbox-photoswipe'); ?></a>
                    <a href="#" id="lbwps-switch-5" class="nav-tab" onclick="lbwpsSwitchTab(5);return false;"><?php echo __('Desktop', 'lightbox-photoswipe'); ?></a>
                    <a href="#" id="lbwps-switch-6" class="nav-tab" onclick="lbwpsSwitchTab(6);return false;"><?php echo __('Mobile', 'lightbox-photoswipe'); ?></a>
                    <a href="#" id="lbwps-switch-7" class="nav-tab" onclick="lbwpsSwitchTab(7);return false;"><?php echo __('Info', 'lightbox-photoswipe'); ?></a>
                </nav>

                <table id="lbwps-tab-1" class="form-table">
                    <tr>
                        <th scope="row"><label for="lightbox_photoswipe_disabled_post_ids"><?php echo __('Excluded pages/posts', 'lightbox-photoswipe'); ?></label></th>
                        <td>
                            <input id="lightbox_photoswipe_disabled_post_ids" class="regular-text" type="text" name="lightbox_photoswipe_disabled_post_ids" value="<?php echo esc_attr(implode(',', $this->disabled_post_ids)); ?>" />
                            <p class="description"><?php echo __('Enter a comma separated list with the numerical IDs of the pages/posts where the lightbox should not be used. This can also be changed in the page/post itself.', 'lightbox-photoswipe'); ?></p>
                            <p><label for="lightbox_photoswipe_metabox"><input id="lightbox_photoswipe_metabox" type="checkbox" name="lightbox_photoswipe_metabox" value="1" <?php if($this->metabox === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Show this setting as checkbox in page/post editor', 'lightbox-photoswipe'); ?></label></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="lightbox_photoswipe_disabled_post_types"><?php echo __('Excluded post types', 'lightbox-photoswipe'); ?></label></th>
                        <td>
                            <input id="lightbox_photoswipe_disabled_post_types" class="regular-text" type="text" name="lightbox_photoswipe_disabled_post_types" value="<?php echo esc_attr(implode(',', $this->disabled_post_types)); ?>" />
                            <p class="description"><?php echo __('Enter a comma separated list of post types where the lightbox should not be used.', 'lightbox-photoswipe'); ?><br />
                                <?php echo __('Available post types on this site', 'lightbox-photoswipe');
                                $sep = ': ';
                                $post_types = get_post_types();
                                foreach ($post_types as $post_type) {
                                    echo $sep.htmlspecialchars($post_type);
                                    $sep = ', ';
                                }
                                ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Visible elements', 'lightbox-photoswipe'); ?></th>
                        <td>
                            <label><input id="lightbox_photoswipe_show_counter" type="checkbox" name="lightbox_photoswipe_show_counter" value="1"<?php if($this->show_counter === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Show picture counter', 'lightbox-photoswipe'); ?></label><br />
                            <label><input id="lightbox_photoswipe_show_fullscreen" type="checkbox" name="lightbox_photoswipe_show_fullscreen" value="1"<?php if($this->show_fullscreen === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Show fullscreen button', 'lightbox-photoswipe'); ?></label><br />
                            <label><input id="lightbox_photoswipe_show_zoom" type="checkbox" name="lightbox_photoswipe_show_zoom" value="1"<?php if($this->show_zoom === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Show zoom button if available', 'lightbox-photoswipe'); ?></label><br />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Other options', 'lightbox-photoswipe'); ?></th>
                        <td>
                            <label><input id="lightbox_photoswipe_history" type="checkbox" name="lightbox_photoswipe_history" value="1"<?php if($this->history === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Update browser history (going back in the browser will first close the lightbox)', 'lightbox-photoswipe'); ?></label><br />
                            <label><input id="lightbox_photoswipe_loop" type="checkbox" name="lightbox_photoswipe_loop" value="1"<?php if($this->loop === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Allow infinite loop', 'lightbox-photoswipe'); ?></label><br />
                            <label><input id="lightbox_photoswipe_separate_galleries" type="checkbox" name="lightbox_photoswipe_separate_galleries" value="1"<?php if($this->separate_galleries === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Show WordPress galleries and Gutenberg gallery blocks in separate lightboxes', 'lightbox-photoswipe'); ?></label><br />
                            <label><input id="lightbox_photoswipe_add_lazyloading" type="checkbox" name="lightbox_photoswipe_add_lazyloading" value="1"<?php if($this->add_lazyloading  === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Add native lazy loading to images', 'lightbox-photoswipe'); ?></label><br />
                            <label><input id="lightbox_photoswipe_use_cache" type="checkbox" name="lightbox_photoswipe_use_cache" value="1"<?php if($this->use_cache === '1') echo ' checked="checked"'; ?> />&nbsp;<?php printf( esc_html__( 'Use WordPress cache instead of the database table %slightbox_photoswipe_img (use this option if you use caching plugins like "Redis Object Cache")', 'lightbox-photoswipe' ), $wpdb->prefix ); ?></label><br />
                            <label><input id="lightbox_photoswipe_ignore_external" type="checkbox" name="lightbox_photoswipe_ignore_external" value="1"<?php if($this->ignore_external === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __( 'Ignore links to images on other sites', 'lightbox-photoswipe' ); ?></label><br />
                            <label><input id="lightbox_photoswipe_ignore_hash" type="checkbox" name="lightbox_photoswipe_ignore_hash" value="1"<?php if($this->ignore_hash === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __( 'Ignore links to images which contain a hash (#)', 'lightbox-photoswipe' ); ?></label><br />
                            <label><input id="lightbox_photoswipe_hide_scrollbars" type="checkbox" name="lightbox_photoswipe_hide_scrollbars" value="1"<?php if($this->hide_scrollbars === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __( 'Hide scrollbars when opening the lightbox (this may not work with your theme)', 'lightbox-photoswipe' ); ?></label><br />
                            <label><input id="lightbox_photoswipe_fix_links" type="checkbox" name="lightbox_photoswipe_fix_links" value="1"<?php if($this->fix_links === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __( 'Try to fix links to images which include image sizes (e.g. "image-1024x768.jpg" instead of "image.jpg")', 'lightbox-photoswipe' ); ?></label><br />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="lightbox_photoswipe_svg_scaling"><?php echo __('SVG scaling factor', 'lightbox-photoswipe'); ?></label></th>
                        <td>
                            <select id="lightbox_photoswipe_svg_scaling" name="lightbox_photoswipe_svg_scaling"><?php
                                for ($scaling = 100; $scaling < 550; $scaling += 50) {
                                    echo '<option value="'.$scaling.'"';
                                    if ($this->svg_scaling == $scaling) echo ' selected="selected"';
                                    echo '>'.($scaling).'%';
                                    if ($scaling == 2) echo ' ('.__('Default', 'lightbox-photoswipe').')';
                                    echo '</option>';
                                } ?></select>
                            <p class="description"><?php echo __('Factor by which SVG images get scaled when displayed in the lightbox.', 'lightbox-photoswipe');
                                if (!function_exists('simplexml_load_file')) {
                                    echo '<br>(';
                                    echo __('<a href="https://www.php.net/manual/en/ref.simplexml.php" target="_blank">The PHP SimpleXML extension</a> is missing on this server! SVG images can not be displayed!', 'lightbox-photoswipe');
                                    echo ')';
                                } ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="lightbox_photoswipe_cdn_url"><?php echo __('CDN URL prefix', 'lightbox-photoswipe'); ?></label></th>
                        <td>
                            <input id="lightbox_photoswipe_cdn_url" class="regular-text" type="text" name="lightbox_photoswipe_cdn_url" value="<?php echo esc_attr($this->cdn_url); ?>" />
                            <p class="description"><?php echo __('If you use the JetPack CDN you can leave this setting empty – JetPack is already supported!', 'lightbox-photoswipe'); ?><br />
                                <?php echo __('If you use a CDN plugin which adds an URL prefix in front of the image link, you can add this prefix (including "http://" or "https://") here. You can enter multiple prefixes separated by comma. The image meta data can then be retrieved from the local file and without loading the image from the CDN. You also need this if you want to use image captions from the WordPress database but serve images using a CDN.', 'lightbox-photoswipe'); ?><br />
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="lightbox_photoswipe_cdn_mode"><?php echo __('CDN mode', 'lightbox-photoswipe'); ?></label></th>
                        <td>
                            <label style="margin-right:0.5em"><input type="radio" name="lightbox_photoswipe_cdn_mode" value="prefix" <?php if ($this->cdn_mode === 'prefix') echo ' checked="checked"'; ?>><?php echo __('Prefix', 'lightbox-photoswipe')?></label> <label><input type="radio" name="lightbox_photoswipe_cdn_mode" value="pull" <?php if ($this->cdn_mode === 'pull') echo ' checked="checked"'; ?>><?php echo __('Pull', 'lightbox-photoswipe')?></label>
                            <p class="description"><?php echo __('CDNs usually use "prefix mode" which adds the CDN domain in front of the whole URL. Some CDNs like ExactDN use "pull mode" which means only the domain of the website is replaced by the CDN domain. If images don\'t show up with the CDN active try another mode.', 'lightbox-photoswipe'); ?></p>
                        </td>
                    </tr>
                </table>

                <table id="lbwps-tab-2" class="form-table" style="display:none;">
                    <tr>
                        <th scope="row"><?php echo __('Skin', 'lightbox-photoswipe'); ?></th>
                        <td>
                            <label><input type="radio" name="lightbox_photoswipe_skin" value="1" <?php if ($this->skin === '1') echo ' checked="checked"'; ?>><?php echo __('Original', 'lightbox-photoswipe')?></label><br />
                            <label><input type="radio" name="lightbox_photoswipe_skin" value="2" <?php if ($this->skin === '2') echo ' checked="checked"'; ?>><?php echo __('Original with solid background', 'lightbox-photoswipe')?></label><br />
                            <label><input type="radio" name="lightbox_photoswipe_skin" value="3" <?php if ($this->skin === '3') echo ' checked="checked"'; ?>><?php echo __('New share symbol', 'lightbox-photoswipe')?></label><br />
                            <label><input type="radio" name="lightbox_photoswipe_skin" value="4" <?php if ($this->skin === '4') echo ' checked="checked"'; ?>><?php echo __('New share symbol with solid background', 'lightbox-photoswipe')?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Spacing between pictures', 'lightbox-photoswipe'); ?></th>
                        <td>
                            <label><select id="lightbox_photoswipe_spacing" name="lightbox_photoswipe_spacing"><?php
                                    for ($spacing = 0; $spacing < 13; $spacing++) {
                                        echo '<option value="'.$spacing.'"';
                                        if ($this->spacing == $spacing) echo ' selected="selected"';
                                        echo '>'.$spacing.'%';
                                        if ($spacing === 12) echo ' ('.__('Default', 'lightbox-photoswipe').')';
                                        echo '</option>';
                                    } ?></select></label>
                            <p class="description"><?php echo __('Space between pictures relative to screenwidth.', 'lightbox-photoswipe'); ?></p>
                        </td>
                    </tr>
                </table>

                <table id="lbwps-tab-3" class="form-table" style="display:none;">
                    <tr>
                        <th scope="row"><?php echo __('General', 'lightbox-photoswipe'); ?></th>
                        <td>
                            <label><input id="lightbox_photoswipe_show_caption" type="checkbox" name="lightbox_photoswipe_show_caption" value="1"<?php if($this->show_caption === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Show caption if available', 'lightbox-photoswipe'); ?></label><br />
                            <label><input id="lightbox_photoswipe_usepostdata" type="checkbox" name="lightbox_photoswipe_usepostdata" value="1"<?php if($this->use_postdata === '1') echo ' checked="checked"'; ?>onClick="lbwpsUpdateDescriptionCheck(this)" />&nbsp;<?php echo __('Get the image captions from the database (this may cause delays on slower servers)', 'lightbox-photoswipe'); ?></label><br />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Used elements', 'lightbox-photoswipe'); ?></th>
                        <td>
                            <label><input id="lightbox_photoswipe_usetitle" type="checkbox" name="lightbox_photoswipe_usetitle" value="1"<?php if($this->use_title === '1') echo ' checked="checked"'; ?> /> <?php echo __('Title', 'lightbox-photoswipe'); ?></label><br />
                            <label><input id="lightbox_photoswipe_usecaption" type="checkbox" name="lightbox_photoswipe_usecaption" value="1"<?php if($this->use_caption === '1') echo ' checked="checked"'; ?> /> <?php echo __('Caption', 'lightbox-photoswipe'); ?></label><br />
                            <label><input id="lightbox_photoswipe_usedescription" type="checkbox" name="lightbox_photoswipe_usedescription" value="1"<?php if($this->use_description === '1') echo ' checked="checked"'; ?> /> <?php echo __('Description', 'lightbox-photoswipe'); ?></label><br />
                            <label><input id="lightbox_photoswipe_use_alt" type="checkbox" name="lightbox_photoswipe_use_alt" value="1"<?php if($this->use_alt === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Alternative text', 'lightbox-photoswipe'); ?></label><br />
                            <label><input id="lightbox_photoswipe_showexif" type="checkbox" name="lightbox_photoswipe_showexif" value="1"<?php if($this->show_exif === '1') echo ' checked="checked"'; ?> onClick="lbwpsUpdateExifDateCheck(this)" />&nbsp;<?php echo __('EXIF data if available', 'lightbox-photoswipe');
                                if (!function_exists('exif_read_data')) {
                                    echo ' (';
                                    echo __('<a href="https://www.php.net/manual/en/book.exif.php" target="_blank">the PHP EXIF extension</a> is missing on this server!', 'lightbox-photoswipe');
                                    echo ')';
                                } ?></label><br/>
                            <label for="lightbox_photoswipe_showexif_date"><input id="lightbox_photoswipe_showexif_date" type="checkbox" name="lightbox_photoswipe_showexif_date" value="1"<?php if($this->show_exif_date === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Show date in EXIF data if available', 'lightbox-photoswipe'); ?></label>
                        </td>
                    </tr>
                </table>

                <table id="lbwps-tab-4" class="form-table" style="display:none;">
                    <tr>
                        <th scope="row"><?php echo __('Visible sharing options', 'lightbox-photoswipe'); ?></th>
                        <td>
                            <label><input id="lightbox_photoswipe_share_facebook" type="checkbox" name="lightbox_photoswipe_share_facebook" value="1"<?php if($this->share_facebook === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Share on Facebook', 'lightbox-photoswipe') ?></label><br />
                            <label><input id="lightbox_photoswipe_share_twitter" type="checkbox" name="lightbox_photoswipe_share_twitter" value="1"<?php if($this->share_twitter === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Tweet', 'lightbox-photoswipe'); ?></label><br />
                            <label><input id="lightbox_photoswipe_share_direct" type="checkbox" name="lightbox_photoswipe_share_direct" value="1"<?php if($this->share_direct === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Use URL of images instead of lightbox on Facebook and Twitter', 'lightbox-photoswipe') ?></label><br />
                            <label><input id="lightbox_photoswipe_share_pinterest" type="checkbox" name="lightbox_photoswipe_share_pinterest" value="1"<?php if($this->share_pinterest === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Pin it', 'lightbox-photoswipe'); ?></label><br />
                            <label><input id="lightbox_photoswipe_share_download" type="checkbox" name="lightbox_photoswipe_share_download" value="1"<?php if($this->share_download === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Download image', 'lightbox-photoswipe'); ?></label><br />
                            <label><input id="lightbox_photoswipe_share_copyurl" type="checkbox" name="lightbox_photoswipe_share_copyurl" value="1"<?php if($this->share_copyurl === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Copy image URL', 'lightbox-photoswipe'); ?></label><br />
                            <label><input id="lightbox_photoswipe_share_custom" type="checkbox" name="lightbox_photoswipe_share_custom" value="1"<?php if($this->share_custom === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Custom link', 'lightbox-photoswipe'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Custom link, label', 'lightbox-photoswipe'); ?></th>
                        <td>
                            <input id="lightbox_photoswipe_share_custom_label" class="regular-text" type="text" name="lightbox_photoswipe_share_custom_label" placeholder="<?php echo __('Your label here', 'lightbox-photoswipe'); ?>" value="<?php echo htmlspecialchars($this->share_custom_label); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Custom link, URL', 'lightbox-photoswipe'); ?></th>
                        <td>
                            <input id="lightbox_photoswipe_share_custom_link" class="regular-text" type="text" name="lightbox_photoswipe_share_custom_link" placeholder="{{raw_image_url}}" value="<?php echo htmlspecialchars($this->share_custom_link); ?>" />
                            <p class="description">
                                <?php echo __('Placeholders for the link:<br />{{raw_url}}&nbsp;&ndash;&nbsp;URL of the lightbox<br />{{url}}&nbsp;&ndash;&nbsp;encoded URL of the lightbox<br />{{raw_image_url}}&nbsp;&ndash;&nbsp;URL of the image<br />{{image_url}}&nbsp;&ndash;&nbsp;encoded URL of the image<br />{{text}}&nbsp;&ndash;&nbsp;image caption.', 'lightbox-photoswipe'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <table id="lbwps-tab-5" class="form-table" style="display:none">
                    <tr>
                        <th scope="row"><?php echo __('General', 'lightbox-photoswipe'); ?></th>
                        <td>
                            <label><input id="lightbox_photoswipe_fulldesktop" type="checkbox" name="lightbox_photoswipe_fulldesktop" value="1"<?php if($this->fulldesktop === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Full picture size in desktop view', 'lightbox-photoswipe'); ?></label><br />
                            <label><input id="lightbox_photoswipe_desktop_slider" type="checkbox" name="lightbox_photoswipe_desktop_slider" value="1"<?php if($this->desktop_slider === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Use slide animation when switching images in desktop view', 'lightbox-photoswipe'); ?></label><br />
                            <label><input id="lightbox_photoswipe_close_on_click" type="checkbox" name="lightbox_photoswipe_close_on_click" value="1"<?php if($this->close_on_click === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Close the lightbox by clicking outside the image', 'lightbox-photoswipe'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Mouse wheel function', 'lightbox-photoswipe'); ?></th>
                        <td>
                            <label><input id="lightbox_photoswipe_wheel_scroll" type="radio" name="lightbox_photoswipe_wheelmode" value="scroll"<?php if($this->wheelmode === 'scroll') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Scroll zoomed image otherwise do nothing', 'lightbox-photoswipe'); ?></label><br />
                            <label><input id="lightbox_photoswipe_wheel_close" type="radio" name="lightbox_photoswipe_wheelmode" value="close"<?php if($this->wheelmode === 'close') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Scroll zoomed image or close lightbox if not zoomed', 'lightbox-photoswipe'); ?></label><br />
                            <label><input id="lightbox_photoswipe_wheel_zoom" type="radio" name="lightbox_photoswipe_wheelmode" value="zoom"<?php if($this->wheelmode === 'zoom') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Zoom in/out', 'lightbox-photoswipe'); ?></label><br />
                            <label><input id="lightbox_photoswipe_wheel_switch" type="radio" name="lightbox_photoswipe_wheelmode" value="switch"<?php if($this->wheelmode === 'switch') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Switch to next/previous picture', 'lightbox-photoswipe'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Idle time for controls', 'lightbox-photoswipe'); ?></th>
                        <td>
                            <select id="lightbox_photoswipe_idletime" name="lightbox_photoswipe_idletime"><?php
                                for ($idletime = 1000; $idletime <= 10000; $idletime+=1000) {
                                    echo '<option value="'.$idletime.'"';
                                    if ($this->idletime == $idletime) echo ' selected="selected"';
                                    echo '>'.($idletime/1000).' '._n('second','seconds', $idletime/1000, 'lightbox-photoswipe');
                                    if ($idletime == 4000) echo ' ('.__('Default', 'lightbox-photoswipe').')';
                                    echo '</option>';
                                } ?></select>
                            <p class="description"><?php echo __('Time until the on screen controls will disappear automatically in desktop view.', 'lightbox-photoswipe'); ?></p>
                        </td>
                    </tr>
                </table>

                <table id="lbwps-tab-6" class="form-table" style="display:none;">
                    <tr>
                        <th scope="row"><?php echo __('General', 'lightbox-photoswipe'); ?></th>
                        <td>
                            <label for="lightbox_photoswipe_close_on_drag"><input id="lightbox_photoswipe_close_on_drag" type="checkbox" name="lightbox_photoswipe_close_on_drag" value="1"<?php if($this->close_on_drag === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Close with vertical drag in mobile view', 'lightbox-photoswipe'); ?></label><br />
                            <label for="lightbox_photoswipe_pinchtoclose"><input id="lightbox_photoswipe_pinchtoclose" type="checkbox" name="lightbox_photoswipe_pinchtoclose" value="1"<?php if($this->pinchtoclose === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Enable pinch to close gesture on mobile devices', 'lightbox-photoswipe'); ?></label><br />
                            <label for="lightbox_photoswipe_taptotoggle"><input id="lightbox_photoswipe_taptotoggle" type="checkbox" name="lightbox_photoswipe_taptotoggle" value="1"<?php if($this->taptotoggle === '1') echo ' checked="checked"'; ?> />&nbsp;<?php echo __('Enable tap to toggle controls on mobile devices', 'lightbox-photoswipe'); ?></label><br />
                        </td>
                    </tr>
                </table>

                <div id="lbwps-tab-7" style="display:none">
                    <p class="lbwps_text"><?php echo __('Plugin version', 'lightbox-photoswipe') ?>: <?php echo LightboxPhotoSwipe::LIGHTBOX_PHOTOSWIPE_VERSION; ?></p>
                    <p class="lbwps_text"><?php echo __('This plugin shows all linked pictures in a lightbox based on an extended version of Photoswipe. If the lightbox does not open, make sure that images are linked to the media and not to the attachment page. Also make sure that no other lightbox is in use (some themes or gallery plugins bring their own lightbox which needs to be disabled). ', 'lightbox-photoswipe'); ?></p>
                    <p class="lbwps_text"><?php echo __('For documentation about hooks, styling etc. please see FAQ', 'lightbox-photoswipe'); ?>: <a href="https://wordpress.org/plugins/lightbox-photoswipe/#faq" target="_blank">https://wordpress.org/plugins/lightbox-photoswipe/#faq</a>.</p>
                    <p class="lbwps_text"><b><?php echo __('If you like my WordPress plugins and want to support my work I would be very happy about a donation via PayPal.', 'lightbox-photoswipe'); ?></b></p>
                    <p class="lbwps_text"><b><a href="https://paypal.me/ArnoWelzel">https://paypal.me/ArnoWelzel</a></b></p>
                    <p class="lbwps_text"><b><?php echo __('Thank you :-)', 'lightbox-photoswipe'); ?></b></p>
                </div>
                <?php submit_button(); ?>
            </form>
        </div>
        <script>
            lbwpsUpdateDescriptionCheck(document.getElementById("lightbox_photoswipe_usepostdata"));
            lbwpsUpdateExifDateCheck(document.getElementById("lightbox_photoswipe_showexif"));
            lbwpsUpdateCurrentTab()
            window.addEventListener('popstate', (event) => {
                console.log(document.location);
                lbwpsUpdateCurrentTab();
            });
        </script>
<?php
    }
}