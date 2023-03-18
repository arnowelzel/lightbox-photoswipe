<?php

namespace LightboxPhotoSwipe;

/**
 * Options manager
 */
class OptionsManager
{
    const OPTIONS = [
        'db_version' => [ 'default' => '0' ],
        'cdn_url' => [ 'default' => '' ],
        'disabled_post_ids' => [ 'default' => '', 'type' => 'list' ],
        'share_facebook' => [ 'default' => '1' ],
        'share_twitter' => [ 'default' => '1' ],
        'share_pinterest' => [ 'default' => '1' ],
        'share_download' => [ 'default' => '1' ],
        'close_on_drag' => [ 'default' => '1' ],
        'show_counter' => [ 'default' => '1' ],
        'skin' => [ 'default' => '3' ],
        'spacing' => [ 'default' => '12' ],
        'show_zoom' => [ 'default' => '1' ],
        'show_caption' => [ 'default' => '1' ],
        'caption_type' => [ 'default' => 'overlay' ],
        'usepostdata' => [ 'default' => '0' ],
        'loop' => [ 'default' => '1' ],
        'pinchtoclose' => [ 'default' => '1' ],
        'show_fullscreen' => [ 'default' => '1' ],
        'taptotoggle' => [ 'default' => '1' ],
        'share_direct' => [ 'default' => '0' ],
        'close_on_click' => [ 'default' => '1' ],
        'fulldesktop' => [ 'default' => '0' ],
        'use_alt' => [ 'default' => '0' ],
        'showexif' => [ 'default' => '0' ],
        'showexif_date' => [ 'default' => '0' ],
        'history' => [ 'default' => '1' ],
        'separate_galleries' => [ 'default' => '0' ],
        'desktop_slider' => [ 'default' => '1' ],
        'idletime' => [ 'default' => '4000' ],
        'usedescription' => [ 'default' => '0' ],
        'wheelmode' => [ 'default' => 'zoom' ],
        'share_copyurl' => [ 'default' => '0' ],
        'share_custom' => [ 'default' => '' ],
        'share_custom_label' => [ 'default' => '' ],
        'share_custom_link' => [ 'default' => '' ],
        'metabox' => [ 'default' => '1' ],
        'disabled_post_types' => [ 'default' => '', 'type' => 'list' ],
        'ignore_external' => [ 'default' => '0' ],
        'ignore_hash' => [ 'default' => '0' ],
        'hide_scrollbars' => [ 'default' => '1' ],
        'svg_scaling' => [ 'default' => '200' ],
        'cdn_mode' => [ 'default' => 'prefix' ],
        'fix_links' => [ 'default' => '1' ],
        'fix_scaled' => [ 'default' => '1' ],
        'usetitle' => [ 'default' => '0' ],
        'usecaption' => [ 'default' => '1' ],
        'version' => [ 'default' => '5' ],
        'bg_opacity' => [ 'default' => '100' ],
        'padding_left' => [ 'default' => '0' ],
        'padding_top' => [ 'default' => '0' ],
        'padding_right' => [ 'default' => '0' ],
        'padding_bottom' => [ 'default' => '0' ],
    ];

    public $options;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->loadOptions();
    }

    /**
     * Register options
     */
    public function registerOptions()
    {
        foreach (self::OPTIONS as $optionName => $option) {
            register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_'.$optionName);
        }
    }

    /**
     * Delete options
     */
    public function deleteOptions()
    {
        foreach (self::OPTIONS as $optionName => $option) {
            delete_option('lightbox_photoswipe_'.$optionName);
            $this->setOption($optionName, '');
        }
    }

    /**
     * Set option
     */
    public function setOption(string $name, $value, bool $save = false)
    {
        $this->options[$name] = $value;
        if ($save) {
            switch($this->getOptionType($name)) {
                case 'list':
                    $option = implode(',', $value);
                    break;
                default:
                    $option = $value;
                    break;
            }
            update_option('lightbox_photoswipe_'.$name, $option);
        }
    }

    /**
     * Get option
     */
    public function getOption(string $name)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        switch ($this->getOptionType($name)) {
            case 'list':
                return [];
            default:
                return '';
        }
    }

    /**
     * Get option type
     */
    public function getOptionType($name)
    {
        $option = self::OPTIONS[$name];
        if (isset(self::OPTIONS[$name]['type'])) {
            return self::OPTIONS[$name]['type'];
        }

        return 'string';
    }

    /**
     * Load options
     */
    public function loadOptions()
    {
        foreach (self::OPTIONS as $nameValue => $option) {
            $nameOption = 'lightbox_photoswipe_'.$nameValue;
            $default = $option['default'];

            switch ($this->getOptionType($nameValue)) {
                case 'list':
                    $value = trim(get_option($nameOption, $default));
                    if ('' === $value) {
                        $this->setOption($nameValue, []);
                    } else {
                        $this->setOption($nameValue, explode(',', $value));
                    }
                    break;
                default:
                    $value = get_option($nameOption, $default);
                    $this->setOption($nameValue, $value);
                    break;
            }
        }
    }
}
