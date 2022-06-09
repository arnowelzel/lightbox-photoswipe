<?php

namespace LightboxPhotoSwipe;

use Twig\Environment;

/**
 * Options manager
 */
class OptionsManager
{
    public ?string $cdn_url;
    public array $disabled_post_ids = [];
    public ?string $share_facebook;
    public ?string $share_twitter;
    public ?string $share_pinterest;
    public ?string $share_download;
    public ?string $close_on_drag;
    public ?string $show_counter;
    public ?string $skin;
    public ?string $spacing;
    public ?string $show_zoom;
    public ?string $show_caption;
    public ?string $usepostdata;
    public ?string $loop;
    public ?string $pinchtoclose;
    public ?string $show_fullscreen;
    public ?string $taptotoggle;
    public ?string $share_direct;
    public ?string $close_on_click;
    public ?string $fulldesktop;
    public ?string $use_alt;
    public ?string $showexif;
    public ?string $showexif_date;
    public ?string $history;
    public ?string $separate_galleries;
    public ?string $desktop_slider;
    public ?string $idletime;
    public ?string $usedescription;
    public ?string $add_lazyloading;
    public ?string $wheelmode;
    public ?string $share_copyurl;
    public ?string $share_custom;
    public ?string $share_custom_label;
    public ?string $share_custom_link;
    public ?string $metabox;
    public array $disabled_post_types = [];
    public ?string $use_cache;
    public ?string $ignore_external;
    public ?string $ignore_hash;
    public ?string $hide_scrollbars;
    public ?string $svg_scaling;
    public ?string $cdn_mode;
    public ?string $fix_links;
    public ?string $usetitle;
    public ?string $usecaption;

    const OPTIONS = [
        [ 'name' => 'cdn_url', 'default' => '' ],
        [ 'name' => 'disabled_post_ids', 'default' => '', 'type' => 'list' ],
        [ 'name' => 'share_facebook', 'default' => '1' ],
        [ 'name' => 'share_twitter', 'default' => '1' ],
        [ 'name' => 'share_pinterest', 'default' => '1' ],
        [ 'name' => 'share_download', 'default' => '1' ],
        [ 'name' => 'close_on_drag', 'default' => '1' ],
        [ 'name' => 'show_counter', 'default' => '1' ],
        [ 'name' => 'skin', 'default' => '3' ],
        [ 'name' => 'spacing', 'default' => '12' ],
        [ 'name' => 'show_zoom', 'default' => '1' ],
        [ 'name' => 'show_caption', 'default' => '1' ],
        [ 'name' => 'usepostdata', 'default' => '0' ],
        [ 'name' => 'loop', 'default' => '1' ],
        [ 'name' => 'pinchtoclose', 'default' => '1' ],
        [ 'name' => 'show_fullscreen', 'default' => '1' ],
        [ 'name' => 'taptotoggle', 'default' => '1' ],
        [ 'name' => 'share_direct', 'default' => '0' ],
        [ 'name' => 'close_on_click', 'default' => '1' ],
        [ 'name' => 'fulldesktop', 'default' => '0' ],
        [ 'name' => 'use_alt', 'default' => '0' ],
        [ 'name' => 'showexif', 'default' => '0' ],
        [ 'name' => 'showexif_date', 'default' => '0' ],
        [ 'name' => 'history', 'default' => '1' ],
        [ 'name' => 'separate_galleries', 'default' => '0' ],
        [ 'name' => 'desktop_slider', 'default' => '1' ],
        [ 'name' => 'idletime', 'default' => '4000' ],
        [ 'name' => 'usedescription', 'default' => '0' ],
        [ 'name' => 'add_lazyloading', 'default' => '0' ],
        [ 'name' => 'wheelmode', 'default' => 'zoom' ],
        [ 'name' => 'share_copyurl', 'default' => '0' ],
        [ 'name' => 'share_custom', 'default' => '' ],
        [ 'name' => 'share_custom_label', 'default' => '' ],
        [ 'name' => 'share_custom_link', 'default' => '' ],
        [ 'name' => 'metabox', 'default' => '1' ],
        [ 'name' => 'disabled_post_types', 'default' => '', 'type' => 'list' ],
        [ 'name' => 'use_cache', 'default' => '0' ],
        [ 'name' => 'ignore_external', 'default' => '0' ],
        [ 'name' => 'ignore_hash', 'default' => '0' ],
        [ 'name' => 'hide_scrollbars', 'default' => '1' ],
        [ 'name' => 'svg_scaling', 'default' => '200' ],
        [ 'name' => 'cdn_mode', 'default' => 'prefix' ],
        [ 'name' => 'fix_links', 'default' => '1' ],
        [ 'name' => 'usetitle', 'default' => '0' ],
        [ 'name' => 'usecaption', 'default' => '1' ],
    ];
    
    protected Environment $twig;

    /**
     * Constructor
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
        $this->loadOptions();
    }

    /**
     * Register options
     */
    public function registerOptions(): void
    {
        foreach (self::OPTIONS as $option) {
            register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_'.$option['name']);
        }
    }

    /**
     * Load options
     */
    public function loadOptions(): void
    {
        foreach (self::OPTIONS as $option) {
            $nameValue = $option['name'];
            $nameOption = 'lightbox_photoswipe_'.$nameValue;
            $default = $option['default'];
            if (isset($option['type'])) {
                $type = $option['type'];
            } else {
                $type = 'string';
            }
            switch ($type) {
                case 'list':
                    $value = trim(get_option($nameOption, $default));
                    if ('' === $value) {
                        $this->$nameValue = [];
                    } else {
                        $this->$nameValue = explode(',', $value);
                    }
                    break;
                default:
                    $value = get_option($nameOption, $default);
                    $this->$nameValue = $value;
                    break;
            }
        }
    }

    /**
     * Enqueue options for frontend script
     */
    public function enqueueFrontendOptions(): void
    {
        $translation_array = [
            'label_facebook' => __('Share on Facebook', LightboxPhotoSwipe::SLUG),
            'label_twitter' => __('Tweet', LightboxPhotoSwipe::SLUG),
            'label_pinterest' => __('Pin it', LightboxPhotoSwipe::SLUG),
            'label_download' => __('Download image', LightboxPhotoSwipe::SLUG),
            'label_copyurl' => __('Copy image URL', LightboxPhotoSwipe::SLUG)
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
        $translation_array['use_caption'] = ($this->usecaption == '1')?'1':'0';
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
