<?php
/*
Plugin Name: Lightbox with PhotoSwipe
Plugin URI: https://wordpress.org/plugins/lightbox-photoswipe/
Description: Lightbox with PhotoSwipe
Version: 2.8
Author: Arno Welzel
Author URI: http://arnowelzel.de
Text Domain: lightbox-photoswipe
*/
defined('ABSPATH') or die();

require_once ABSPATH . '/wp-admin/includes/image.php';

/**
 * Lightbox with PhotoSwipe
 * 
 * @package LightboxPhotoSwipe
 */
class LightboxPhotoSwipe
{
    const LIGHTBOX_PHOTOSWIPE_VERSION = '2.8';
    var $disabled_post_ids;
    var $share_facebook;
    var $share_pinterest;
    var $share_twitter;
    var $share_download;
    var $share_direct;
    var $close_on_scroll;
    var $close_on_drag;
    var $history;
    var $show_counter;
    var $skin;
    var $usepostdata;
    var $enabled;
    var $close_on_click;
    var $fulldesktop;
    var $use_alt;
    var $show_exif;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->disabled_post_ids = explode(',', get_option('lightbox_photoswipe_disabled_post_ids'));
        $this->share_facebook = get_option('lightbox_photoswipe_share_facebook');
        $this->share_pinterest = get_option('lightbox_photoswipe_share_pinterest');
        $this->share_twitter = get_option('lightbox_photoswipe_share_twitter');
        $this->share_download = get_option('lightbox_photoswipe_share_download');
        $this->share_direct = get_option('lightbox_photoswipe_share_direct');
        $this->close_on_scroll = get_option('lightbox_photoswipe_close_on_scroll');
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
        $this->usepostdata = get_option('lightbox_photoswipe_usepostdata');
        $this->close_on_click = get_option('lightbox_photoswipe_close_on_click');
        $this->fulldesktop = get_option('lightbox_photoswipe_fulldesktop');
        $this->use_alt = get_option('lightbox_photoswipe_use_alt');
        $this->show_exif = get_option('lightbox_photoswipe_showexif');

        $this->enabled = true;
        
        if (!is_admin()) {
            add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
            add_action('wp_footer', array($this, 'footer'));
            add_action('template_redirect', array($this, 'outputFilter'), PHP_INT_MAX);
        }
        add_action('wpmu_new_blog', array($this, 'onCreateBlog'), 10, 6);
        add_filter('wpmu_drop_tables', array($this, 'onDeleteBlog'));
        add_action('plugins_loaded', array($this, 'init'));
        add_action('admin_menu', array($this, 'adminMenu'));

        register_activation_hook(__FILE__, array($this, 'onActivate'));
        register_deactivation_hook(__FILE__, array($this, 'onDeactivate'));
    }
    
    /**
     * Scripts/CSS
     * 
     * @return nothing
     */
    function enqueueScripts()
    {
        $id = get_the_ID();

        if (!is_404()) {
            if (in_array($id, $this->disabled_post_ids)) $this->enabled = false;
            $this->enabled = apply_filters('lbwps_enabled', $this->enabled, $id);

            if (!$this->enabled) return;
        }

        wp_enqueue_script(
            'photoswipe-lib',
            plugin_dir_url(__FILE__) . 'lib/photoswipe.min.js',
            array(),
            self::LIGHTBOX_PHOTOSWIPE_VERSION
        );
        wp_enqueue_script(
            'photoswipe-ui-default',
            plugin_dir_url(__FILE__) . 'lib/photoswipe-ui-default.min.js',
            array('photoswipe-lib'),
            self::LIGHTBOX_PHOTOSWIPE_VERSION
        );

        wp_enqueue_script(
            'photoswipe-frontend',
            plugin_dir_url(__FILE__) . 'js/frontend.min.js',
            array('photoswipe-lib', 'photoswipe-ui-default', 'jquery'),
            self::LIGHTBOX_PHOTOSWIPE_VERSION
        );
        $translation_array = array(
            'label_facebook' => __('Share on Facebook', 'lightbox-photoswipe'),
            'label_twitter' => __('Tweet', 'lightbox-photoswipe'),
            'label_pinterest' => __('Pin it', 'lightbox-photoswipe'),
            'label_download' => __('Download image', 'lightbox-photoswipe')
        );
        $translation_array['share_facebook'] = ($this->share_facebook == '1')?'1':'0';
        $translation_array['share_twitter'] = ($this->share_twitter == '1')?'1':'0';
        $translation_array['share_pinterest'] = ($this->share_pinterest == '1')?'1':'0';
        $translation_array['share_download'] = ($this->share_download == '1')?'1':'0';
        $translation_array['share_direct'] = ($this->share_direct == '1')?'1':'0';
        $translation_array['close_on_scroll'] = ($this->close_on_scroll != '1')?'1':'0';
        $translation_array['close_on_drag'] = ($this->close_on_drag != '1')?'1':'0';
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
        wp_localize_script('photoswipe-frontend', 'lbwps_options', $translation_array);
        
        wp_enqueue_style(
            'photoswipe-lib',
            plugin_dir_url(__FILE__) . 'lib/photoswipe.css',
            false,
            self::LIGHTBOX_PHOTOSWIPE_VERSION
        );

        switch($this->skin) {
        case '2':
            $skin = 'classic-solid';
            break;
        case '3':
            $skin = 'default';
            break;
        case '4':
            $skin = 'default-solid';
            break;
        default:
            $skin = 'classic';
            break;
        }
        wp_enqueue_style(
            'photoswipe-skin',
            plugin_dir_url(__FILE__) . 'lib/skins/' . $skin . '/skin.css',
            false,
            self::LIGHTBOX_PHOTOSWIPE_VERSION
        );
    }

    /**
     * Footer in frontend with PhotoSwipe UI
     * 
     * @return void
     */
    function footer()
    {
        if (!is_404()) {
            if (in_array(get_the_ID(), $this->disabled_post_ids) || !$this->enabled) return;
        }
        
        $footer = '<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="pswp__bg"></div>
    <div class="pswp__scroll-wrap">
        <div class="pswp__container">
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
        </div>
        <div class="pswp__ui pswp__ui--hidden">
            <div class="pswp__top-bar">
                <div class="pswp__counter"></div>
                <button class="pswp__button pswp__button--close" title="'.__('Close (Esc)', 'lightbox-photoswipe').'"></button>
                <button class="pswp__button pswp__button--share" title="'.__('Share', 'lightbox-photoswipe').'"></button>
                <button class="pswp__button pswp__button--fs" title="'.__('Toggle fullscreen', 'lightbox-photoswipe').'"></button>
                <button class="pswp__button pswp__button--zoom" title="'.__('Zoom in/out', 'lightbox-photoswipe').'"></button>
                <div class="pswp__preloader">
                    <div class="pswp__preloader__icn">
                      <div class="pswp__preloader__cut">
                        <div class="pswp__preloader__donut"></div>
                      </div>
                    </div>
                </div>
            </div>
            <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                <div class="pswp__share-tooltip">
                </div> 
            </div>
            <button class="pswp__button pswp__button--arrow--left" title="'.__('Previous (arrow left)', 'lightbox-photoswipe').'"></button>
            <button class="pswp__button pswp__button--arrow--right" title="'.__('Next (arrow right)', 'lightbox-photoswipe').'"></button>
            <div class="pswp__caption">
                <div class="pswp__caption__center"></div>
            </div>
        </div>
    </div>
</div>';
        $footer = apply_filters('lbwps_markup', $footer);
        echo $footer;
    }

    /**
     * Helper to get the camera model from an EXIF array
     *
     * @param $exif array  The EXIF array containing the original value
     *
     * @return string  The camera model as readable text
     */
    function getExifCamera(&$exif)
    {
        $make = '';
        if (isset($exif['IFD0']['Make'])) {
            $make = $exif['IFD0']['Make'];
        }

        $model = '';
        if (isset($exif['IFD0']['Model'])) {
            $model .= $exif['IFD0']['Model'];
        }

        $camera = '';
        if (strlen($make)>0) {
            if (substr($model, 0, strlen($make)) == $make) {
                $camera = $model;
            } else {
                $camera = $make . ' ' . $model;
            }
        } else {
            $camera = $model;
        }

        return $camera;
    }

    /**
     * Helper to get a float value from an EXIF value
     *
     * @param $value string  The value to work with (e.g. "10/40")
     *
     * @return float|int
     */
    function exifGetFloat($value)
    {
        $pos = strpos($value, '/');
        if ($pos === false) {
            return (float) $value;
        }
        $a = (float) substr($value, 0, $pos);
        $b = (float) substr($value, $pos+1);
        return ($b == 0) ? ($a) : ($a / $b);
    }

    /**
     * Helper to get the focal length from an EXIF array
     *
     * @param $exif array  The EXIF array containing the original value
     *
     * @return string      The focal length as readable text (e.h. "100mm")
     */
    function exifGetFocalLength(&$exif)
    {
        $focal = '';
        if (isset($exif['EXIF']['FocalLengthIn35mmFilm'])) {
            $focal = $exif['EXIF']['FocalLengthIn35mmFilm'];
        } else if (isset($exif['EXIF']['FocalLength'])) {
            $focal = $exif['EXIF']['FocalLength'];
        } else {
            return '';
        }
        $focalLength = $this->exifGetFloat($focal);
        return round($focalLength) . 'mm';
    }

    /**
     * Helper to get the shutter speed from an EXIF array
     *
     * @param $exif array  The EXIF array containing the original value
     *
     * @return string      The shutter speed as readable text (e.h. "1/250s")
     */
    function exifGetShutter(&$exif)
    {
        if (isset($exif['EXIF']['ExposureTime'])) {
            return $exif['EXIF']['ExposureTime'].'s';
        }
        if (!isset($exif['EXIF']['ShutterSpeedValue'])) {
            return '';
        }
        $apex = $this->exifGetFloat($exif['EXIF']['ShutterSpeedValue']);
        $shutter = pow(2, -$apex);
        if ($shutter == 0) {
            return '';
        }
        if ($shutter >= 1) {
            return round($shutter) . 's';
        }
        return '1/' . round(1 / $shutter) . 's';
    }

    /**
     * Helper to get the ISO speed rating from an EXIF array
     *
     * @param $exif    The EXIF array containing the original value
     *
     * @return string  The ISO speed rating as readable text
     */
    function exifGetIso(&$exif)
    {
        if (!isset($exif['EXIF']['ISOSpeedRatings'])) {
            return '';
        }
        return 'ISO' . $exif['EXIF']['ISOSpeedRatings'];
    }

    /**
     * Helper to get the f-stop from an EXIF array
     *
     * @param $exif array  The EXIF array containing the original value
     *
     * @return string      The f-stop value as readable text (e.g. "f/3.5")
     */
    function exifGetFstop(&$exif)
    {
        $aperture = '';
        if (isset($exif['EXIF']['ApertureValue'])) {
            $aperture = $exif['EXIF']['ApertureValue'];
        } else if (isset($exif['EXIF']['FNumber'])) {
            $aperture = isset($exif['EXIF']['FNumber']);
        } else {
            return '';
        }
        $apex  = $this->exifGetFloat($aperture);
        $fstop = pow(2, $apex/2);
        if ($fstop == 0) return '';
        return 'f/' . round($fstop,1);
    }

    /**
     * Helper to add some detail to the EXIF output
     *
     * @param $output  Existing output
     * @param $detail  Detail to add
     */
    function exifAddOutput(&$output, $detail)
    {
        if ($output != '') {
            $output .= ', ';
        }
        $output .= $detail;
    }

    /**
     * Callback to handle a single image
     * 
     * @param string $matches existing matches
     * 
     * @return string modified HTML code 
     */
    function outputCallback($matches)
    {
        global $wpdb;
        
        $attr = '';
        $baseurl_http = get_site_url(null, null, 'http');
        $baseurl_https = get_site_url(null, null, 'https');
        $url = $matches[2];
        
        // Workaround for pictures served by Jetpack Photon
        $file = preg_replace('/(i[0-2]\.wp.com\/)/s', '', $url);

        $type = wp_check_filetype($file);
        $caption = '';

        // Only work on known image formats
        if (in_array($type['ext'], array('jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp', 'tif', 'tiff', 'ico', 'webp'))) {
            // If image is served by the website itself, try to get caption for local file
            if (substr($file, 0, strlen($baseurl_http)) == $baseurl_http || substr($file, 0, strlen($baseurl_https)) == $baseurl_https
                || substr($file, 0, 7) !== 'http://' || substr($file, 0, 8) !== 'https://') {
                // Remove domain part
                $file = str_replace($baseurl_http.'/', '', $file);
                $file = str_replace($baseurl_https.'/', '', $file);

                // Remove leading slash
                $file = ltrim($file, '/');

                // Normalized URLs to retrieve the image caption
                $url_http = $baseurl_http.'/'.$file;
                $url_https = $baseurl_https.'/'.$file;
            
                $file = ABSPATH . $file;
           
                if ('1' == $this->usepostdata && '1' == $this->show_caption) {
                    $imgid = $wpdb->get_col($wpdb->prepare('SELECT ID FROM '.$wpdb->posts.' WHERE guid="%s" or guid="%s";', $url_http, $url_https)); 
                    if (isset($imgid[0])) {
                        $imgpost = get_post($imgid[0]);
                        $caption = $imgpost->post_excerpt;
                    } else {
                        $caption = '';
                    }
                }
            }
            
            $imgdate = @filemtime($file);
            if (false == $imgdate) {
                $imgdate = 0;
            }
            $imgkey = md5($file) . '-'. $imgdate;
            $imageSize[0] = 0;
            $imageSize[1] = 0;
            $exifCamera = '';
            $exifFocal = '';
            $exifFstop = '';
            $exifShutter = '';
            $exifIso = '';
            $tableImg = $wpdb->prefix . 'lightbox_photoswipe_img';
            $entry = $wpdb->get_row("SELECT width, height, exif_camera, exif_focal, exif_fstop, exif_shutter, exif_iso FROM $tableImg where imgkey='$imgkey'");
            if (null != $entry) {
                $imageSize[0] = $entry->width;
                $imageSize[1] = $entry->height;
                $exifCamera = $entry->exif_camera;
                $exifFocal = $entry->exif_focal;
                $exifFstop  = $entry->exif_fstop;
                $exifShutter = $entry->exif_shutter;
                $exifIso = $entry->exif_iso;
            } else {
                $imageSize = @getimagesize($file);

                if (function_exists('exif_read_data')) {
                    $exif = exif_read_data($file, 'EXIF', true);
                    $exifCamera = $this->getExifCamera($exif);
                    $exifFocal = $this->exifGetFocalLength($exif);
                    $exifFstop = $this->exifGetFstop($exif);
                    $exifShutter = $this->exifGetShutter($exif);
                    $exifIso = $this->exifGetIso($exif);
                }

                if (is_numeric($imageSize[0]) && is_numeric($imageSize[1])) {
                    $created = strftime('%Y-%m-%d %H:%M:%S');
                    $sql = sprintf(
                    'INSERT INTO %s (imgkey, created, width, height, exif_camera, exif_focal, exif_fstop, exif_shutter, exif_iso)'.
                        ' VALUES ("%s", "%s", "%d", "%d", "%s", "%s", "%s", "%s", "%s")',
                        $tableImg,
                        $imgkey,
                        $created,
                        $imageSize[0],
                        $imageSize[1],
                        $exifCamera,
                        $exifFocal,
                        $exifFstop,
                        $exifShutter,
                        $exifIso
                    );
                    $wpdb->query($sql);
                } else {
                    $imageSize[0] = 0;
                    $imageSize[1] = 0;
                }
            }

            $attr = '';
            if (0!=$imageSize[0] && 0!=$imageSize[1]) {
                $attr = sprintf(' data-width="%s" data-height="%s"', $imageSize[0], $imageSize[1]);
            
                if ($caption != '') {
                    $attr .= sprintf(' data-caption="%s"', htmlspecialchars(nl2br(wptexturize($caption))));
                }

                $exifOutput = '';

                if ($this->show_exif) {
                    $this->exifAddOutput($exifOutput, $exifFocal);
                    $this->exifAddOutput($exifOutput, $exifFstop);
                    $this->exifAddOutput($exifOutput, $exifShutter);
                    $this->exifAddOutput($exifOutput, $exifIso);
                    if ($exifCamera != '') {
                        $exifOutput = sprintf('%s (%s)', $exifCamera, $exifOutput);
                    }

                    if ($exifOutput != '') {
                        $attr .= sprintf(' data-exif="%s"', htmlspecialchars($exifOutput));
                    }
                }
            }
        }

        $result = $matches[1].$matches[2].$matches[3].$matches[4].$attr.$matches[5];

        return $result;
    }

    /**
     * Output filter
     *
     * @param string $content Current HTML output
     *
     * @return string modified HTML output
     */
    function output($content)
    {
        $content = preg_replace_callback(
            '/(<a.[^>]*href=["\'])(.[^"^\']*?)(["\'])([^>]*)(>)/sU',
            array($this, 'outputCallback'),
            $content
        );
        return $content;
    }

    /**
     * Filter output of curent page/post
     * 
     * @param string $content Current HTML output
     * 
     * @return string filtered HTML output
     */
    function outputFilter($content)
    {
        if (!$this->enabled) return;

        ob_start(array($this, 'output'));
    }

    /**
     * Add admin menu in the backend
     * 
     * @return void
     */
    function adminMenu()
    {
        add_options_page(
            __('Lightbox with PhotoSwipe', 'lightbox-photoswipe'),
            __('Lightbox with PhotoSwipe', 'lightbox-photoswipe'),
            'administrator',
            'lightbox-photoswipe',
            array($this, 'settingsPage'),
            plugins_url('/images/icon.png', __FILE__)
        );

        add_action('admin_init', array($this, 'registerSettings'));
    }

    /**
     * Register settings
     * 
     * @return void
     */
    function registerSettings()
    {
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_disabled_post_ids');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_share_facebook');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_share_twitter');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_share_pinterest');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_share_download');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_share_direct');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_close_on_scroll');
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
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_close_on_click');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_fulldesktop');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_use_alt');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_showexif');
    }

    /**
     * Output settings page in backend
     * 
     * @return void
     */
    function settingsPage()
    {
        echo '<div class="wrap"><h1>' . __('Lightbox with PhotoSwipe', 'lightbox-photoswipe') . '</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields('lightbox-photoswipe-settings-group');
        // do_settings_sections( 'lightbox-photoswipe-settings-group' );
        echo '<table class="form-table"><tr>
            <th scope="row"><label for="lightbox_photoswipe_disabled_post_ids">'.__('Excluded pages/posts', 'lightbox-photoswipe').'</label></th>
            <td><input id="lightbox_photoswipe_disabled_post_ids" class="regular-text" type="text" name="lightbox_photoswipe_disabled_post_ids" value="' . esc_attr(get_option('lightbox_photoswipe_disabled_post_ids')) . '" /><p class="description">'.__('Enter a comma separated list with the numerical IDs of the pages/posts where the lightbox should not be used.', 'lightbox-photoswipe').'</p></td>
            </tr>
            <tr>
            <th scope="row">'.__('Visible sharing options', 'lightbox-photoswipe').'</th>
            <td>
            <label for="lightbox_photoswipe_share_facebook"><input id="lightbox_photoswipe_share_facebook" type="checkbox" name="lightbox_photoswipe_share_facebook" value="1"'; if(get_option('lightbox_photoswipe_share_facebook')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Share on Facebook', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_share_twitter"><input id="lightbox_photoswipe_share_twitter" type="checkbox" name="lightbox_photoswipe_share_twitter" value="1" '; if(get_option('lightbox_photoswipe_share_twitter')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Tweet', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_share_direct"><input id="lightbox_photoswipe_share_direct" type="checkbox" name="lightbox_photoswipe_share_direct" value="1"'; if(get_option('lightbox_photoswipe_share_direct')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Use URL of images instead of lightbox on Facebook and Twitter', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_share_pinterest"><input id="lightbox_photoswipe_share_pinterest" type="checkbox" name="lightbox_photoswipe_share_pinterest" value="1" '; if(get_option('lightbox_photoswipe_share_pinterest')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Pin it', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_share_download"><input id="lightbox_photoswipe_share_download" type="checkbox" name="lightbox_photoswipe_share_download" value="1"'; if(get_option('lightbox_photoswipe_share_download')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Download image', 'lightbox-photoswipe').'</label>
            </td>
            </tr>
            <tr>
            <th scope="row">'.__('Other options', 'lightbox-photoswipe').'</th>
            <td>
            <label for="lightbox_photoswipe_usepostdata"><input id="lightbox_photoswipe_usepostdata" type="checkbox" name="lightbox_photoswipe_usepostdata" value="1"'; if(get_option('lightbox_photoswipe_usepostdata')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Get the image captions from the database (this may cause delays on slower servers)', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_use_alt"><input id="lightbox_photoswipe_use_alt" type="checkbox" name="lightbox_photoswipe_use_alt" value="1"'; if(get_option('lightbox_photoswipe_use_alt')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Use alternative text of images as captions if needed', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_close_on_scroll"><input id="lightbox_photoswipe_close_on_scroll" type="checkbox" name="lightbox_photoswipe_close_on_scroll" value="1"'; if(get_option('lightbox_photoswipe_close_on_scroll')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Close when scrolling in desktop view', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_close_on_drag"><input id="lightbox_photoswipe_close_on_drag" type="checkbox" name="lightbox_photoswipe_close_on_drag" value="1"'; if(get_option('lightbox_photoswipe_close_on_drag')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Close with vertical drag in mobile view', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_history"><input id="lightbox_photoswipe_history" type="checkbox" name="lightbox_photoswipe_history" value="1"'; if(get_option('lightbox_photoswipe_history')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Activate browser history', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_show_counter"><input id="lightbox_photoswipe_show_counter" type="checkbox" name="lightbox_photoswipe_show_counter" value="1"'; if(get_option('lightbox_photoswipe_show_counter')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Show picture counter', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_show_fullscreen"><input id="lightbox_photoswipe_show_fullscreen" type="checkbox" name="lightbox_photoswipe_show_fullscreen" value="1"'; if(get_option('lightbox_photoswipe_show_fullscreen')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Show fullscreen button', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_show_zoom"><input id="lightbox_photoswipe_show_zoom" type="checkbox" name="lightbox_photoswipe_show_zoom" value="1"'; if(get_option('lightbox_photoswipe_show_zoom')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Show zoom button if available', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_show_caption"><input id="lightbox_photoswipe_show_caption" type="checkbox" name="lightbox_photoswipe_show_caption" value="1"'; if(get_option('lightbox_photoswipe_show_caption')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Show caption if available', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_loop"><input id="lightbox_photoswipe_loop" type="checkbox" name="lightbox_photoswipe_loop" value="1"'; if(get_option('lightbox_photoswipe_loop')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Allow infinite loop', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_pinchtoclose"><input id="lightbox_photoswipe_pinchtoclose" type="checkbox" name="lightbox_photoswipe_pinchtoclose" value="1"'; if(get_option('lightbox_photoswipe_pinchtoclose')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Enable pinch to close gesture on mobile devices', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_taptotoggle"><input id="lightbox_photoswipe_taptotoggle" type="checkbox" name="lightbox_photoswipe_taptotoggle" value="1"'; if(get_option('lightbox_photoswipe_taptotoggle')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Enable tap to toggle controls on mobile devices', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_close_on_click"><input id="lightbox_photoswipe_close_on_click" type="checkbox" name="lightbox_photoswipe_close_on_click" value="1"'; if(get_option('lightbox_photoswipe_close_on_click')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Close the lightbox by clicking outside the image', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_fulldesktop"><input id="lightbox_photoswipe_fulldesktop" type="checkbox" name="lightbox_photoswipe_fulldesktop" value="1"'; if(get_option('lightbox_photoswipe_fulldesktop')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Full picture size in desktop view', 'lightbox-photoswipe').'</label>
            <tr>';
        echo '<th scope="row">'.__('EXIF data', 'lightbox-photoswipe').'</th><td>';
        echo '<label for="lightbox_photoswipe_showexif"><input id="lightbox_photoswipe_showexif" type="checkbox" name="lightbox_photoswipe_showexif" value="1"'; if(get_option('lightbox_photoswipe_showexif')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Show EXIF data if available', 'lightbox-photoswipe').'</label>';
        if (!function_exists('exif_read_data')) {
            echo '<p><em>';
            echo __('Please note: <a href="https://www.php.net/manual/en/book.exif.php" target="_blank">The PHP EXIF extension</a> is missing!', 'lightbox-photoswipe');
            echo '</em></p>';
        }
        echo '</td></tr>';
        echo '<th scope="row">'.__('Spacing between pictures', 'lightbox-photoswipe').'</th>';
        echo '<td><label for="lightbox_photoswipe_spacing"><select id="lightbox_photoswipe_spacing" name="lightbox_photoswipe_spacing">';
        for ($spacing = 0; $spacing < 13; $spacing++) {
            echo '<option value="'.$spacing.'"';
            if (get_option('lightbox_photoswipe_spacing')==$spacing) echo ' selected="selected"';
            echo '>'.$spacing.'%';
            if ($spacing == 12) echo ' ('.__('Default', 'lightbox-photoswipe').')';
            echo '</option>';
        }
        echo '</select></label><p class="description">'.__('Space between pictures relative to screenwidth.', 'lightbox-photoswipe').'</p>';
        echo '</td></tr>';
        echo '<tr><th scope="row">'.__('Skin', 'lightbox-photoswipe').'</th>
            <td><label for="lightbox_photoswipe_skin"><select id="lightbox_photoswipe_skin" name="lightbox_photoswipe_skin">';
        echo '<option value="1"';
        if (get_option('lightbox_photoswipe_skin')=='1') echo ' selected="selected"';
        echo '>'.__('Original', 'lightbox-photoswipe').'</option>';
        echo '<option value="2"';
        if (get_option('lightbox_photoswipe_skin')=='2') echo ' selected="selected"';
        echo '>'.__('Original with solid background', 'lightbox-photoswipe').'</option>';
        echo '<option value="3"';
        if (get_option('lightbox_photoswipe_skin')=='3') echo ' selected="selected"';
        echo '>'.__('New share symbol', 'lightbox-photoswipe').'</option>';
        echo '<option value="4"';
        if (get_option('lightbox_photoswipe_skin')=='4') echo ' selected="selected"';
        echo '>'.__('New share symbol with solid background', 'lightbox-photoswipe').'</option>';
        echo '</select></label>';
        echo '</td></tr>';
        echo '    </table>';
        submit_button();
        echo '</form></div>';
    }

    /**
     * Create custom database tables
     * 
     * @return void
     */
    function createTables()
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lightbox_photoswipe_img'; 
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
          imgkey char(64) DEFAULT '' NOT NULL,
          created datetime,
          width mediumint(7),
          height mediumint(7),
          exif_camera varchar(255),
          exif_focal varchar(255),
          exif_fstop varchar(255),
          exif_shutter varchar(255),
          exif_iso varchar(255),
          PRIMARY KEY (imgkey),
          INDEX idx_created (created)
        ) $charset_collate;";
        include_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $wpdb->query($sql);
    }

    /**
     * Delete custom database tables
     * 
     * @return void
     */
    function deleteTables()
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lightbox_photoswipe_img'; 
        $sql = "DROP TABLE IF EXISTS $table_name";
        $wpdb->query($sql);
    }

    /**
     * Handler for creating a new blog
     * 
     * @param mixed $blog_id ID of the blog
     * @param mixed $user_id ID of the user
     * @param mixed $domain  Domain of the blog
     * @param mixed $path    Path inside the domain
     * @param mixed $site_id ID of the site
     * @param mixed $meta    Metadata
     *
     * @return void
     */
    function onCreateBlog($blog_id, $user_id, $domain, $path, $site_id, $meta)
    {
        if (is_plugin_active_for_network('lightbox-photoswipe/lightbox-photoswipe.php')) {
            switch_to_blog($blog_id);
            $this->createTables();
            update_option('lightbox_photoswipe_share_facebook', '1');
            update_option('lightbox_photoswipe_share_pinterest', '1');
            update_option('lightbox_photoswipe_share_twitter', '1');
            update_option('lightbox_photoswipe_share_download', '1');
            update_option('lightbox_photoswipe_share_direct', '0');
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
            update_option('lightbox_photoswipe_use_alt', '0');
            update_option('lightbox_photoswipe_showexif', '0');
            restore_current_blog();
        }
    }

    /**
     * Filter for deleting a blog
     * 
     * @param array[] $tables list of tables to be deleted
     * 
     * @return array[] list of tables to be deleted
     */
    function onDeleteBlog($tables)
    {
        global $wpdb;
        
        $tables[] = $wpdb->prefix . 'lightbox_photoswipe_img';
        
        return $tables;
    }

    /**
     * Hook for plugin activation
     *
     * @return void
     */
    function onActivate()
    {
        if (!wp_next_scheduled('lbwps_cleanup')) {
            wp_schedule_event(time(), 'hourly', 'lbwps_cleanup');
        }
    }

    /**
     * Hook for plugin deactivation
     *
     * @return void
     */
    function onDeactivate()
    {
        wp_clear_scheduled_hook('lbwps_cleanup');
    }        

    /**
     * Scheduled job for database cleanup
     * This will remove cached image data which is older than 24 hours
     *
     * @return void
     */
    function cleanupDatabase()
    {
        global $wpdb;

        $table_img = $wpdb->prefix . 'lightbox_photoswipe_img';
        $date = strftime('%Y-%m-%d %H:%M:%S', time()-86400);
        $sql = "DELETE FROM $table_img where created<(\"$date\")";
        $wpdb->query($sql);
    }            
    
    /**
     * Plugin initialization
     * 
     * @return void
     */
    function init()
    {
        global $wpdb;

        load_plugin_textdomain('lightbox-photoswipe', false, 'lightbox-photoswipe/languages/');

        $db_version = get_option('lightbox_photoswipe_db_version');
        
        if ($db_version == '' || intval($db_version) < 2) {
            $this->deleteTables();
            $this->createTables();
        }
        if (intval($db_version) < 3) {
            update_option('lightbox_photoswipe_disabled_post_ids', get_option('disabled_post_ids'));
            delete_option('disabled_post_ids');
            update_option('lightbox_photoswipe_share_facebook', '1');
            update_option('lightbox_photoswipe_share_pinterest', '1');
            update_option('lightbox_photoswipe_share_twitter', '1');
            update_option('lightbox_photoswipe_share_download', '1');
        }
        if (intval($db_version) < 4) {
            update_option('lightbox_photoswipe_close_on_scroll', '1');
            update_option('lightbox_photoswipe_close_on_drag', '1');
            update_option('lightbox_photoswipe_show_counter', '1');
        }
        if (intval($db_version) < 5) {
            update_option('lightbox_photoswipe_skin', '3');
        }
        if (intval($db_version) < 6) {
            update_option('lightbox_photoswipe_show_zoom', '1');
            update_option('lightbox_photoswipe_show_caption', '1');
            update_option('lightbox_photoswipe_spacing', '12');
        }
        if (intval($db_version) < 7) {
            update_option('lightbox_photoswipe_loop', '1');
            update_option('lightbox_photoswipe_pinchtoclose', '1');
            update_option('lightbox_photoswipe_usepostdata', '1');
        }
        if (intval($db_version) < 9) {
            update_option('lightbox_photoswipe_show_fullscreen', '1');
        }
        if (intval($db_version) < 10) {
            $this->onActivate();
        }
        if (intval($db_version) < 11) {
            update_option('lightbox_photoswipe_taptotoggle', '1');
        }
        if (intval($db_version) < 12) {
            update_option('lightbox_photoswipe_share_direct', '0');
        }
        if (intval($db_version) < 13) {
            update_option('lightbox_photoswipe_close_on_click', '1');
        }
        if (intval($db_version) < 14) {
            update_option('lightbox_photoswipe_fulldesktop', '0');
        }
        if (intval($db_version) < 15) {
            update_option('lightbox_photoswipe_use_alt', '0');
        }
        if (intval($db_version) < 16) {
            update_option('lightbox_photoswipe_showexif', '0');
            $this->deleteTables();
            $this->createTables();
        }
        add_action('lbwps_cleanup', array($this, 'cleanupDatabase'));
        update_option('lightbox_photoswipe_db_version', 16);
    }
}

$lightbox_photoswipe = new LightboxPhotoSwipe();
