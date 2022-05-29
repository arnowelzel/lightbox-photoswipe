<?php

namespace LightboxPhotoSwipe;

use Twig\Environment;

/**
 * Options manager
 */
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

    protected Environment $twig;

    /**
     * Constructor
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
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
            'label_facebook' => __('Share on Facebook', LightboxPhotoSwipe::NAME),
            'label_twitter' => __('Tweet', LightboxPhotoSwipe::NAME),
            'label_pinterest' => __('Pin it', LightboxPhotoSwipe::NAME),
            'label_download' => __('Download image', LightboxPhotoSwipe::NAME),
            'label_copyurl' => __('Copy image URL', LightboxPhotoSwipe::NAME)
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
     * Output page for backend settings
     *
     * @return string
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function outputAdminSettingsPage()
    {
        global $wpdb;

        echo $this->twig->render('options.html.twig', [
            'optionsManager' => $this,
            'wpdb' => $wpdb,
            'hasSimpleXML' => function_exists('simplexml_load_file'),
            'hasExif' => function_exists('exif_read_data'),
        ]);
    }
}
