<?php
/*
Plugin Name: Lightbox with PhotoSwipe
Plugin URI: https://wordpress.org/plugins/lightbox-photoswipe/
Description: Lightbox with PhotoSwipe
Version: 2.96
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
    const LIGHTBOX_PHOTOSWIPE_VERSION = '2.96';

    var $disabled_post_ids;
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
    var $usepostdata;
    var $usedescription;
    var $enabled;
    var $close_on_click;
    var $fulldesktop;
    var $use_alt;
    var $show_exif;
    var $separate_galleries;
    var $desktop_slider;
    var $idletime;
    var $add_lazyloading;
    var $gallery_id;
    var $ob_active;
    var $ob_level;

    /**
     * Constructor
     */
    public function __construct()
    {
        $disabled_post_ids = trim(get_option('lightbox_photoswipe_disabled_post_ids'));
        if ('' !== $disabled_post_ids) {
            $this->disabled_post_ids = explode( ',', $disabled_post_ids );
        } else {
            $this->disabled_post_ids = [];
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
        $this->usepostdata = get_option('lightbox_photoswipe_usepostdata');
        $this->usedescription = get_option('lightbox_photoswipe_usedescription');
        $this->close_on_click = get_option('lightbox_photoswipe_close_on_click');
        $this->fulldesktop = get_option('lightbox_photoswipe_fulldesktop');
        $this->use_alt = get_option('lightbox_photoswipe_use_alt');
        $this->show_exif = get_option('lightbox_photoswipe_showexif');
        $this->show_exif_date = get_option('lightbox_photoswipe_showexif_date');
        $this->separate_galleries = get_option('lightbox_photoswipe_separate_galleries');
        $this->desktop_slider = get_option('lightbox_photoswipe_desktop_slider');
        $this->idletime = get_option('lightbox_photoswipe_idletime');
        $this->add_lazyloading = get_option('lightbox_photoswipe_add_lazyloading');

        $this->enabled = true;
        $this->gallery_id = 1;
        $this->ob_active = false;
        $this->ob_level = 0;

        if (!is_admin()) {
            add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
            add_action('wp_footer', array($this, 'footer'));
            add_action('wp_head', array($this, 'bufferStart'), 2000);
            if ($this->separate_galleries) {
                remove_shortcode('gallery');
                add_shortcode('gallery', array($this, 'shortcodeGallery'), 10, 1);
                add_filter('render_block', array($this, 'gutenbergBlock'), 10, 2);
            }
        }
        add_action('wpmu_new_blog', array($this, 'onCreateBlog'), 10, 6);
        add_filter('wpmu_drop_tables', array($this, 'onDeleteBlog'));
        add_action('plugins_loaded', array($this, 'init'));
        add_action('admin_menu', array($this, 'adminMenu'));

        // Metabox handling
        if ('1' === $this->metabox) {
            add_action( 'add_meta_boxes', [ $this, 'metaBox' ] );
            add_action( 'save_post', [ $this, 'metaBoxSave' ] );
        }

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
            'lbwps-lib',
            plugin_dir_url(__FILE__) . 'lib/photoswipe.min.js',
            array(),
            self::LIGHTBOX_PHOTOSWIPE_VERSION
        );
        wp_enqueue_script(
            'lbwps-ui-default',
            plugin_dir_url(__FILE__) . 'lib/photoswipe-ui-default.min.js',
            array('lbwps-lib'),
            self::LIGHTBOX_PHOTOSWIPE_VERSION
        );

        wp_enqueue_script(
            'lbwps-frontend',
            plugin_dir_url(__FILE__) . 'js/frontend.min.js',
            array('lbwps-lib', 'lbwps-ui-default'),
            self::LIGHTBOX_PHOTOSWIPE_VERSION
        );
        $translation_array = array(
            'label_facebook' => __('Share on Facebook', 'lightbox-photoswipe'),
            'label_twitter' => __('Tweet', 'lightbox-photoswipe'),
            'label_pinterest' => __('Pin it', 'lightbox-photoswipe'),
            'label_download' => __('Download image', 'lightbox-photoswipe'),
            'label_copyurl' => __('Copy image URL', 'lightbox-photoswipe')
        );
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
        $translation_array['desktop_slider'] = ($this->desktop_slider == '1')?'1':'0';
        $translation_array['idletime'] =intval($this->idletime);
        wp_localize_script('lbwps-frontend', 'lbwps_options', $translation_array);
        
        wp_enqueue_style(
            'lbwps-lib',
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
        if (!$this->enabled) return;

        if (is_404() || !in_array(get_the_ID(), $this->disabled_post_ids)) {
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
                <button class="pswp__button pswp__button--close" title="' . __('Close (Esc)', 'lightbox-photoswipe') . '"></button>
                <button class="pswp__button pswp__button--share" title="' . __('Share', 'lightbox-photoswipe') . '"></button>
                <button class="pswp__button pswp__button--fs" title="' . __('Toggle fullscreen', 'lightbox-photoswipe') . '"></button>
                <button class="pswp__button pswp__button--zoom" title="' . __('Zoom in/out', 'lightbox-photoswipe') . '"></button>
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
            <button class="pswp__button pswp__button--arrow--left" title="' . __('Previous (arrow left)', 'lightbox-photoswipe') . '"></button>
            <button class="pswp__button pswp__button--arrow--right" title="' . __('Next (arrow right)', 'lightbox-photoswipe') . '"></button>
            <div class="pswp__caption">
                <div class="pswp__caption__center"></div>
            </div>
        </div>
    </div>
</div>';
            $footer = apply_filters('lbwps_markup', $footer);
            echo $footer;
        }

        if ($this->ob_active) {
            $this->ob_active = false;
            if($this->ob_level == ob_get_level()) {
                ob_end_flush();
            }
        }
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
     * Helper to get the date taken from an EXIF array
     *
     * @param $exif    The EXIF array containing the original value
     *
     * @return string  The date taken in local date format
     */
    function exifGetDateTime(&$exif)
    {
        $result = '';

        if (isset($exif['EXIF']['DateTimeOriginal'])) {
            $exifDate = $exif['EXIF']['DateTimeOriginal'];
            $date = substr($exifDate, 0, 4).'-'.substr($exifDate, 5, 2 ).'-'.substr($exifDate, 8, 2).
                    ' '.substr($exifDate, 11, 2).':'.substr($exifDate, 14, 2 ).':'.substr($exifDate, 17, 2);
            return $date;
        }

        return $result;
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
    function exifAddOutput(&$output, $detail, $cssclass)
    {
        if('' === $detail) {
            return;
        }
        if ('' !== $output) {
            $output .= ', ';
        }
        $output .= sprintf('<span class="pswp__caption__exif_%s">%s</span>', $cssclass, $detail);
    }

    /**
     * Callback to handle a single image link
     * 
     * @param string $matches existing matches
     * 
     * @return string modified HTML content
     */
    function outputCallbackProperties($matches)
    {
        global $wpdb;
        
        $attr = '';
        $baseurl_http = get_site_url(null, null, 'http');
        $baseurl_https = get_site_url(null, null, 'https');
        $url = $matches[2];

        // Workaround for pictures served by Jetpack Photon CDN
        $file = preg_replace('/(i[0-2]\.wp.com\/)/s', '', $url);

        // Remove parameters if any
        $fileparts = explode('?', $file);
        $file = $fileparts[0];

        $type = wp_check_filetype($file);
        $caption = '';
        $description = '';

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

                $url_http = '';
                $url_https = '';

                // Add local path only if the file is not an external URL
                if (substr($file, 0, 6) != 'ftp://' &&
                    substr($file, 0, 7) != 'http://' &&
                    substr($file, 0, 8) != 'https://') {

                    // Normalized URLs to retrieve the image caption
                    $url_http = $baseurl_http.'/'.$file;
                    $url_https = $baseurl_https.'/'.$file;

                    $file = ABSPATH . $file;
                } else {
                    // The image is an external URL, then use this
                    $url_http = $file;
                    $url_https = $file;
                }
           
                if ('1' == $this->usepostdata && '1' == $this->show_caption) {
                    $imgid = $wpdb->get_col($wpdb->prepare('SELECT ID FROM '.$wpdb->posts.' WHERE guid="%s" or guid="%s";', $url_http, $url_https)); 
                    if (isset($imgid[0])) {
                        $imgpost = get_post($imgid[0]);
                        $caption = $imgpost->post_excerpt;
                        $description = $imgpost->post_content;
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
            $exifDateTime = '';
            $tableImg = $wpdb->prefix . 'lightbox_photoswipe_img';
            $entry = $wpdb->get_row("SELECT width, height, exif_camera, exif_focal, exif_fstop, exif_shutter, exif_iso, exif_datetime FROM $tableImg where imgkey='$imgkey'");
            if (null != $entry) {
                $imageSize[0] = $entry->width;
                $imageSize[1] = $entry->height;
                $exifCamera = $entry->exif_camera;
                $exifFocal = $entry->exif_focal;
                $exifFstop  = $entry->exif_fstop;
                $exifShutter = $entry->exif_shutter;
                $exifIso = $entry->exif_iso;
                $exifDateTime = $entry->exif_datetime;
            } else {
                $imageSize = @getimagesize($file);

                if (function_exists('exif_read_data')) {
                    $exif = @exif_read_data($file, 'EXIF', true);
                    if ($exif !== false) {
                        $exifCamera = $this->getExifCamera($exif);
                        $exifFocal = $this->exifGetFocalLength($exif);
                        $exifFstop = $this->exifGetFstop($exif);
                        $exifShutter = $this->exifGetShutter($exif);
                        $exifIso = $this->exifGetIso($exif);
                        $exifDateTime = $this->exifGetDateTime($exif);
                    }
                }

                if (is_numeric($imageSize[0]) && is_numeric($imageSize[1])) {
                    $created = strftime('%Y-%m-%d %H:%M:%S');
                    $sql = sprintf(
                    'INSERT INTO %s (imgkey, created, width, height, exif_camera, exif_focal, exif_fstop, exif_shutter, exif_iso, exif_datetime)'.
                        ' VALUES ("%s", "%s", "%d", "%d", "%s", "%s", "%s", "%s", "%s", "%s")',
                        $tableImg,
                        $imgkey,
                        $created,
                        $imageSize[0],
                        $imageSize[1],
                        $exifCamera,
                        $exifFocal,
                        $exifFstop,
                        $exifShutter,
                        $exifIso,
                        $exifDateTime
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

                if ($this->usedescription == '1' && $description != '') {
                    $attr .= sprintf(' data-description="%s"', htmlspecialchars(nl2br(wptexturize($description))));
                }

                $exifOutput = '';

                if ($this->show_exif) {
                    $this->exifAddOutput($exifOutput, $exifFocal, 'focal');
                    $this->exifAddOutput($exifOutput, $exifFstop, 'fstop');
                    $this->exifAddOutput($exifOutput, $exifShutter, 'shutter');
                    $this->exifAddOutput($exifOutput, $exifIso, 'iso');
                    if ($this->show_exif_date) {
                        $exitDateTimeValue = date_create_from_format('Y-m-d H:i:s', $exifDateTime);
                        if (false !== $exitDateTimeValue) {
                            $exifDateTimeOutput = date_i18n( get_option( 'date_format' ), $exitDateTimeValue->getTimestamp());
                            $this->exifAddOutput( $exifOutput, $exifDateTimeOutput, 'datetime');
                        }
                    }
                    if ($exifCamera != '') {
                        $exifOutput = sprintf('%s (%s)', $exifCamera, $exifOutput);
                    }

                    if ($exifOutput != '') {
                        $attr .= sprintf(' data-exif="%s"', htmlspecialchars($exifOutput));
                    }
                }
            }
        }

        return $matches[1] . $matches[2] . $matches[3] . $matches[4] . $attr . $matches[5];
    }

    /**
     * Callback to add the "lazy loading" attribute to an image
     *
     * @param string $matches existing matches
     *
     * @return string modified HTML content
     */
    function outputCallbackLazyLoading($matches)
    {
        $replacement = $matches[4];
        if(false === strpos($replacement, 'loading="')) {
            if('/' === substr($replacement, -1)) {
                $replacement = substr($replacement, 0, strlen($replacement) - 1) . ' loading="lazy" /';
            } else {
                $replacement .= ' loading="lazy"';
            }
        }
        return $matches[1] . $matches[2] . $matches[3] . $replacement . $matches[5];
    }

    /**
     * Callback to add current gallery id to a single image
     *
     * @param string $matches existing matches
     *
     * @return string modified HTML content
     */
    function outputCallbackGalleryId($matches)
    {
        $attr = sprintf(' data-gallery-id="%s"', $this->gallery_id);
        return $matches[1].$matches[2].$matches[3].$matches[4].$attr.$matches[5];
    }

    /**
     * Output filter for post content
     *
     * @param string $content current HTML content
     *
     * @return void modified HTML content
     */
    function filterOutput($content)
    {
        $content = preg_replace_callback(
            '/(<a.[^>]*href=["\'])(.[^"^\']*?)(["\'])([^>]*)(>)/sU',
            array($this, 'outputCallbackProperties'),
            $content
        );
        if ('1' === $this->add_lazyloading) {
            $content = preg_replace_callback(
                '/(<img.[^>]*src=["\'])(.[^"^\']*?)(["\'])([^>]*)(>)/sU',
                array($this, 'outputCallbackLazyLoading'),
                $content
            );
        }
        return $content;
    }

    /**
     * Output filter for post content
     *
     * @return void
     */
    function bufferStart()
    {
        if (!$this->enabled) {
            return;
        }

        ob_start(array($this, 'filterOutput'));
        $this->ob_level = ob_get_level();
        $this->ob_active = true;
    }

    function shortcodeGallery($attr)
    {
        $this->gallery_id++;
        $content = gallery_shortcode($attr);
        return preg_replace_callback(
            '/(<a.[^>]*href=["\'])(.[^"^\']*?)(["\'])([^>]*)(>)/sU',
            array($this, 'outputCallbackGalleryId'),
            $content
        );
        return $content;
    }


    /**
     * Filter for Gutenberg blocks to add gallery id to images
     *
     * @param string $block_content current HTML content
     * @param array $block block information
     *
     * @return string modified HTML content
     */
    function gutenbergBlock($block_content, $block)
    {
        if ($block['blockName'] == 'core/gallery') {
            $this->gallery_id++;
            return preg_replace_callback(
                '/(<a.[^>]*href=["\'])(.[^"^\']*?)(["\'])([^>]*)(>)/sU',
                array($this, 'outputCallbackGalleryId'),
                $block_content
            );
        }
        return $block_content;
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
            array($this, 'settingsPage')
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
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_close_on_click');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_fulldesktop');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_use_alt');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_showexif');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_showexif_date');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_separate_galleries');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_desktop_slider');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_idletime');
        register_setting('lightbox-photoswipe-settings-group', 'lightbox_photoswipe_add_lazyloading');
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
        echo '<script>
function lbwpsUpdateDescriptionCheck(checkbox) {
    var useDescription = document.getElementById("lightbox_photoswipe_usedescription");
    if (checkbox.checked) {
        useDescription.disabled = false;
    } else {
        useDescription.disabled = true;
    }
}

function lbwpsUpdateExifDateCheck(checkbox) {
    var showExifDate = document.getElementById("lightbox_photoswipe_showexif_date");
    console.log(showExifDate);
    if (checkbox.checked) {
        showExifDate.disabled = false;
    } else {
        showExifDate.disabled = true;
    }
}
</script>';
        echo '<table class="form-table"><tr>
            <th scope="row"><label for="lightbox_photoswipe_disabled_post_ids">'.__('Excluded pages/posts', 'lightbox-photoswipe').'</label></th>
            <td><input id="lightbox_photoswipe_disabled_post_ids" class="regular-text" type="text" name="lightbox_photoswipe_disabled_post_ids" value="' . esc_attr(get_option('lightbox_photoswipe_disabled_post_ids')) . '" />
            <p class="description">'.__('Enter a comma separated list with the numerical IDs of the pages/posts where the lightbox should not be used. This can also be changed in the page/post itself.', 'lightbox-photoswipe').'</p>
            <p><label for="lightbox_photoswipe_metabox"><input id="lightbox_photoswipe_metabox" type="checkbox" name="lightbox_photoswipe_metabox" value="1"'; if(get_option('lightbox_photoswipe_metabox')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Show this setting as checkbox in page/post editor', 'lightbox-photoswipe').'</label></p></td>
            </tr>
            <tr>
            <th scope="row">'.__('Visible sharing options', 'lightbox-photoswipe').'</th>
            <td>
            <label for="lightbox_photoswipe_share_facebook"><input id="lightbox_photoswipe_share_facebook" type="checkbox" name="lightbox_photoswipe_share_facebook" value="1"'; if(get_option('lightbox_photoswipe_share_facebook')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Share on Facebook', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_share_twitter"><input id="lightbox_photoswipe_share_twitter" type="checkbox" name="lightbox_photoswipe_share_twitter" value="1" '; if(get_option('lightbox_photoswipe_share_twitter')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Tweet', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_share_direct"><input id="lightbox_photoswipe_share_direct" type="checkbox" name="lightbox_photoswipe_share_direct" value="1"'; if(get_option('lightbox_photoswipe_share_direct')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Use URL of images instead of lightbox on Facebook and Twitter', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_share_pinterest"><input id="lightbox_photoswipe_share_pinterest" type="checkbox" name="lightbox_photoswipe_share_pinterest" value="1" '; if(get_option('lightbox_photoswipe_share_pinterest')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Pin it', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_share_download"><input id="lightbox_photoswipe_share_download" type="checkbox" name="lightbox_photoswipe_share_download" value="1"'; if(get_option('lightbox_photoswipe_share_download')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Download image', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_share_copyurl"><input id="lightbox_photoswipe_share_copyurl" type="checkbox" name="lightbox_photoswipe_share_copyurl" value="1"'; if(get_option('lightbox_photoswipe_share_copyurl')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Copy image URL', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_share_custom"><input id="lightbox_photoswipe_share_custom" type="checkbox" name="lightbox_photoswipe_share_custom" value="1"'; if(get_option('lightbox_photoswipe_share_custom')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Custom link', 'lightbox-photoswipe').'</label>
            </tr>
            <tr>
            <th scope="row">'.__('Custom sharing label/link', 'lightbox-photoswipe').'</th>
            <td>
            <input id="lightbox_photoswipe_share_custom_label" class="regular-text" type="text" name="lightbox_photoswipe_share_custom_label" placeholder="'.__('Your label here', 'lightbox-photoswipe').'" value="'.htmlspecialchars(get_option('lightbox_photoswipe_share_custom_label')).'" />
            <input id="lightbox_photoswipe_share_custom_link" class="regular-text" type="text" name="lightbox_photoswipe_share_custom_link" placeholder="{{raw_image_url}}" value="'.htmlspecialchars(get_option('lightbox_photoswipe_share_custom_link')).'" />
            <p class="description">'.__('Placeholders for the link: {{raw_url}}&nbsp;&ndash;&nbsp;URL of the lightbox, {{url}}&nbsp;&ndash;&nbsp;encoded URL of the lightbox, {{raw_image_url}}&nbsp;&ndash;&nbsp;URL of the image, {{image_url}}&nbsp;&ndash;&nbsp;encoded URL of the image, {{text}}&nbsp;&ndash;&nbsp;image caption.', 'lightbox-photoswipe').'</p>
            </td>
            </tr>
            <tr>
            <th scope="row">'.__('Visible elements', 'lightbox-photoswipe').'</th>
            <td>
            <label for="lightbox_photoswipe_show_counter"><input id="lightbox_photoswipe_show_counter" type="checkbox" name="lightbox_photoswipe_show_counter" value="1"'; if(get_option('lightbox_photoswipe_show_counter')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Show picture counter', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_show_fullscreen"><input id="lightbox_photoswipe_show_fullscreen" type="checkbox" name="lightbox_photoswipe_show_fullscreen" value="1"'; if(get_option('lightbox_photoswipe_show_fullscreen')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Show fullscreen button', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_show_zoom"><input id="lightbox_photoswipe_show_zoom" type="checkbox" name="lightbox_photoswipe_show_zoom" value="1"'; if(get_option('lightbox_photoswipe_show_zoom')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Show zoom button if available', 'lightbox-photoswipe').'</label><br />
            </td>
            </tr>
            <tr>
            <th scope="row">'.__('Captions', 'lightbox-photoswipe').'</th>
            <td>
            <label for="lightbox_photoswipe_show_caption"><input id="lightbox_photoswipe_show_caption" type="checkbox" name="lightbox_photoswipe_show_caption" value="1"'; if(get_option('lightbox_photoswipe_show_caption')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Show caption if available', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_usepostdata"><input id="lightbox_photoswipe_usepostdata" type="checkbox" name="lightbox_photoswipe_usepostdata" value="1"'; if(get_option('lightbox_photoswipe_usepostdata')=='1') echo ' checked="checked"'; echo ' onClick="lbwpsUpdateDescriptionCheck(this)" />&nbsp;'.__('Get the image captions from the database (this may cause delays on slower servers)', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_usedescription"><input id="lightbox_photoswipe_usedescription" type="checkbox" name="lightbox_photoswipe_usedescription" value="1"'; if(get_option('lightbox_photoswipe_usedescription')=='1') echo ' checked="checked"'; echo ' />&nbsp;... '.__('also use description if available', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_use_alt"><input id="lightbox_photoswipe_use_alt" type="checkbox" name="lightbox_photoswipe_use_alt" value="1"'; if(get_option('lightbox_photoswipe_use_alt')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Use alternative text of images as captions if needed', 'lightbox-photoswipe').'</label><br />';
        echo '<label for="lightbox_photoswipe_showexif"><input id="lightbox_photoswipe_showexif" type="checkbox" name="lightbox_photoswipe_showexif" value="1"'; if(get_option('lightbox_photoswipe_showexif')=='1') echo ' checked="checked"'; echo ' onClick="lbwpsUpdateExifDateCheck(this)" />&nbsp;'.__('Show EXIF data if available', 'lightbox-photoswipe');
        if (!function_exists('exif_read_data')) {
            echo ' (';
            echo __('<a href="https://www.php.net/manual/en/book.exif.php" target="_blank">the PHP EXIF extension</a> is missing on this server!', 'lightbox-photoswipe');
            echo ')';
        }
        echo '</label></br><label for="lightbox_photoswipe_showexif_date"><input id="lightbox_photoswipe_showexif_date" type="checkbox" name="lightbox_photoswipe_showexif_date" value="1"'; if(get_option('lightbox_photoswipe_showexif_date')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Show date in EXIF data if available', 'lightbox-photoswipe');
        echo '</label>
            </td>
            </tr>
            <tr>
            <th scope="row">'.__('Other options', 'lightbox-photoswipe').'</th>
            <td>
            <label for="lightbox_photoswipe_fulldesktop"><input id="lightbox_photoswipe_fulldesktop" type="checkbox" name="lightbox_photoswipe_fulldesktop" value="1"'; if(get_option('lightbox_photoswipe_fulldesktop')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Full picture size in desktop view', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_desktop_slider"><input id="lightbox_photoswipe_desktop_slider" type="checkbox" name="lightbox_photoswipe_desktop_slider" value="1"'; if(get_option('lightbox_photoswipe_desktop_slider')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Use slide animation when switching images in desktop view', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_close_on_drag"><input id="lightbox_photoswipe_close_on_drag" type="checkbox" name="lightbox_photoswipe_close_on_drag" value="1"'; if(get_option('lightbox_photoswipe_close_on_drag')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Close with vertical drag in mobile view', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_pinchtoclose"><input id="lightbox_photoswipe_pinchtoclose" type="checkbox" name="lightbox_photoswipe_pinchtoclose" value="1"'; if(get_option('lightbox_photoswipe_pinchtoclose')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Enable pinch to close gesture on mobile devices', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_taptotoggle"><input id="lightbox_photoswipe_taptotoggle" type="checkbox" name="lightbox_photoswipe_taptotoggle" value="1"'; if(get_option('lightbox_photoswipe_taptotoggle')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Enable tap to toggle controls on mobile devices', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_separate_galleries"><input id="lightbox_photoswipe_separate_galleries" type="checkbox" name="lightbox_photoswipe_separate_galleries" value="1"'; if(get_option('lightbox_photoswipe_separate_galleries')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Show WordPress galleries and Gutenberg gallery blocks in separate lightboxes', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_close_on_click"><input id="lightbox_photoswipe_close_on_click" type="checkbox" name="lightbox_photoswipe_close_on_click" value="1"'; if(get_option('lightbox_photoswipe_close_on_click')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Close the lightbox by clicking outside the image', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_history"><input id="lightbox_photoswipe_history" type="checkbox" name="lightbox_photoswipe_history" value="1"'; if(get_option('lightbox_photoswipe_history')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Update browser history (going back in the browser will first close the lightbox)', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_loop"><input id="lightbox_photoswipe_loop" type="checkbox" name="lightbox_photoswipe_loop" value="1"'; if(get_option('lightbox_photoswipe_loop')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Allow infinite loop', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_add_lazyloading"><input id="lightbox_photoswipe_add_lazyloading" type="checkbox" name="lightbox_photoswipe_add_lazyloading" value="1"'; if(get_option('lightbox_photoswipe_add_lazyloading')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Add native lazy loading to images', 'lightbox-photoswipe').'</label><br />
            </tr>
            <tr>
            <th scope="row">'.__('Mouse wheel function', 'lightbox-photoswipe').'</th>
            <td>
            <label for="lightbox_photoswipe_wheel_scroll"><input id="lightbox_photoswipe_wheel_scroll" type="radio" name="lightbox_photoswipe_wheelmode" value="scroll"'; if(get_option('lightbox_photoswipe_wheelmode')=='scroll') echo ' checked="checked"'; echo ' />&nbsp;'.__('Scroll zoomed image otherwise do nothing', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_wheel_close"><input id="lightbox_photoswipe_wheel_close" type="radio" name="lightbox_photoswipe_wheelmode" value="close"'; if(get_option('lightbox_photoswipe_wheelmode')=='close') echo ' checked="checked"'; echo ' />&nbsp;'.__('Scroll zoomed image or close lightbox if not zoomed', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_wheel_zoom"><input id="lightbox_photoswipe_wheel_zoom" type="radio" name="lightbox_photoswipe_wheelmode" value="zoom"'; if(get_option('lightbox_photoswipe_wheelmode')=='zoom') echo ' checked="checked"'; echo ' />&nbsp;'.__('Zoom in/out', 'lightbox-photoswipe').'</label><br />
            <label for="lightbox_photoswipe_wheel_switch"><input id="lightbox_photoswipe_wheel_switch" type="radio" name="lightbox_photoswipe_wheelmode" value="switch"'; if(get_option('lightbox_photoswipe_wheelmode')=='switch') echo ' checked="checked"'; echo ' />&nbsp;'.__('Switch to next/previous picture', 'lightbox-photoswipe').'</label><br />
            </tr>';
        echo '<tr><th scope="row">'.__('Spacing between pictures', 'lightbox-photoswipe').'</th>';
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
        echo '<tr></tr><th scope="row">'.__('Idle time for controls', 'lightbox-photoswipe').'</th>';
        echo '<td><label for="lightbox_photoswipe_idletime"><select id="lightbox_photoswipe_idletime" name="lightbox_photoswipe_idletime">';
        for ($idletime = 1000; $idletime <= 10000; $idletime+=1000) {
            echo '<option value="'.$idletime.'"';
            if (get_option('lightbox_photoswipe_idletime')==$idletime) echo ' selected="selected"';
            echo '>'.($idletime/1000).' '._n('second','seconds', $idletime/1000, 'lightbox-photoswipe');
            if ($idletime == 4000) echo ' ('.__('Default', 'lightbox-photoswipe').')';
            echo '</option>';
        }
        echo '</select></label><p class="description">'.__('Time until the on screen controls will disappear automatically in desktop view.', 'lightbox-photoswipe').'</p>';
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
        echo '</table>';
        submit_button();
        echo '</form>';
        echo '<p><b>'.__('If you like my WordPress plugins and want to support my work you can send a donation via PayPal.', 'lightbox-photoswipe').'</b><p>';
        echo '<p><b><a href="https://paypal.me/ArnoWelzel">https://paypal.me/ArnoWelzel</a></b></p>';
        echo '<p><b>'.__('Thank you :-)', 'lightbox-photoswipe').'</b><p>';
        echo '</div>';
        echo '<script>lbwpsUpdateDescriptionCheck(document.getElementById("lightbox_photoswipe_usepostdata"));lbwpsUpdateExifDateCheck(document.getElementById("lightbox_photoswipe_showexif"))</script>';

    }

    /**
     * Add metabox for post editor
     *
     * @return void
     */
    function metaBox()
    {
        $types = ['post', 'page'];
        foreach ($types as $type) {
            add_meta_box(
                'lightbox-photoswipe',
                __('Lightbox with PhotoSwipe', 'lightbox-photoswipe'),
                [$this, 'metaBoxHtml'],
                $type,
                'side'
            );
        }
    }

    /**
     * Metabox HTML output
     *
     * @return void
     */
    function metaBoxHtml($post)
    {
        wp_nonce_field( basename( __FILE__ ), 'lbwps_nonce' );

        $checked = '';
        if (in_array($post->ID, $this->disabled_post_ids)) {
            $checked = 'checked="checked" ';
        }
        echo '<label for="lbwps_disabled"><input type="checkbox" id="lbwps_disabled" name="lbwps_disabled" value="1"'.$checked.'/>';
        echo __('Disable', 'lightbox-photoswipe').'</label>';
    }

    /**
     * Save options from metabox
     *
     * @return void
     */
    function metaBoxSave($post_id)
    {
        // Only save options if this is not an autosave
        $is_autosave = wp_is_post_autosave( $post_id );
        $is_revision = wp_is_post_revision( $post_id );
        $is_valid_nonce = (isset($_POST['lbwps_nonce']) && wp_verify_nonce($_POST['lbwps_nonce' ], basename(__FILE__)))?'true':'false';

        if ($is_autosave || $is_revision || !$is_valid_nonce ) {
            return;
        }

        // Save post specific options
        $disabled_post_ids = [];
        if(!isset($_POST['lbwps_disabled']) || $_POST['lbwps_disabled']!='1') {
            if (in_array($post_id, $this->disabled_post_ids)) {
                foreach ( $this->disabled_post_ids as $disabled_post_id ) {
                    if ((int)$post_id !== (int)$disabled_post_id) {
                        $disabled_post_ids[] = $disabled_post_id;
                    }
                }
                $this->disabled_post_ids = $disabled_post_ids;
                update_option( 'lightbox_photoswipe_disabled_post_ids', implode(',', $this->disabled_post_ids));
            }
        } else {
            if (!in_array($post_id, $this->disabled_post_ids)) {
                $this->disabled_post_ids[] = $post_id;
                update_option('lightbox_photoswipe_disabled_post_ids', implode(',', $this->disabled_post_ids));
            }
        }
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
          exif_datetime varchar(255),
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
            update_option('lightbox_photoswipe_use_alt', '0');
            update_option('lightbox_photoswipe_showexif', '0');
            update_option('lightbox_photoswipe_separate_galleries', '0');
            update_option('lightbox_photoswipe_desktop_slider', '1');
            update_option('lightbox_photoswipe_idletime', '4000');
            update_option('lightbox_photoswipe_add_lazyloading', '1');
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

        $table_name = $wpdb->prefix . 'lightbox_photoswipe_img';
        $date = strftime( '%Y-%m-%d %H:%M:%S', time() - 86400 );
        $sql  = "DELETE FROM $table_name where created<(\"$date\")";
        $wpdb->query( $sql );
    }            
    
    /**
     * Plugin initialization
     * 
     * @return void
     */
    function init()
    {
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
        if (intval($db_version) < 17) {
            update_option('lightbox_photoswipe_history', '1');
            update_option('lightbox_photoswipe_separate_galleries', '0');
        }
        if (intval($db_version) < 18) {
            update_option( 'lightbox_photoswipe_desktop_slider', '1' );
        }
        if (intval($db_version) < 19) {
            update_option( 'lightbox_photoswipe_idletime', '4000' );
            update_option( 'lightbox_photoswipe_add_lazyloading', '1' );
            update_option( 'lightbox_photoswipe_usedescription', '0' );
        }
        if (intval($db_version) < 20) {
            update_option( 'lightbox_photoswipe_add_lazyloading', '0' );
        }
        if (intval($db_version) < 22) {
            $this->deleteTables();
            $this->createTables();
        }
        if (intval($db_version) < 23) {
            $wheelmode = 'zoom';
            if (get_option('lightbox_photoswipe_close_on_scroll') == '1') {
                $wheelmode = 'close';
            }
            update_option('lightbox_photoswipe_wheelmode', $wheelmode);
            delete_option('lightbox_photoswipe_close_on_scroll');
            update_option('lightbox_photoswipe_share_copyurl', '0');
            update_option('lightbox_photoswipe_share_custom', '0');
            update_option('lightbox_photoswipe_share_custom_label', '');
            update_option('lightbox_photoswipe_share_custom_link', '');
        }
        if (intval($db_version) < 24) {
            update_option('lightbox_photoswipe_metabox', '1');
        }
        add_action('lbwps_cleanup', array($this, 'cleanupDatabase'));
        update_option('lightbox_photoswipe_db_version', 24);
    }
}

$lightbox_photoswipe = new LightboxPhotoSwipe();
