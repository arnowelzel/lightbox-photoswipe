<?php
/*
Plugin Name: Lightbox with PhotoSwipe
Plugin URI: https://wordpress.org/plugins/lightbox-photoswipe/
Description: Lightbox with PhotoSwipe
Version: 3.4.2
Author: Arno Welzel
Author URI: http://arnowelzel.de
Text Domain: lightbox-photoswipe
*/
namespace LightboxPhotoSwipe;

defined('ABSPATH') or die();

require 'options-manager.php';
require 'exif-helper.php';

/**
 * Lightbox with PhotoSwipe
 *
 * @package LightboxPhotoSwipe
 */
class LightboxPhotoSwipe
{
    const LIGHTBOX_PHOTOSWIPE_VERSION = '3.4.2';
    const CACHE_EXPIRE_IMG_DETAILS = 86400;

    private $gallery_id;
    private $ob_active;
    private $ob_level;
    private $optionsManager;
    private $exifHelper;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->optionsManager = new OptionsManager();
        $this->exifHelper = new ExifHelper();

        $this->enabled = true;
        $this->gallery_id = 1;
        $this->ob_active = false;
        $this->ob_level = 0;

        if (!is_admin()) {
            add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
            add_action('wp_footer', [$this, 'outputFooter']);
            add_action('wp_head', [$this, 'bufferStart'], 2050);
            if ($this->optionsManager->separate_galleries) {
                remove_shortcode('gallery');
                add_shortcode('gallery', [$this, 'shortcodeGallery'], 10, 1);
                add_filter('render_block', [$this, 'gutenbergBlock'], 10, 2);
            }
        } else {
            add_action( 'update_option_lightbox_photoswipe_use_cache', array( $this, 'update_option_use_cache' ), 10, 3 );
        }
        add_action('wpmu_new_blog', [$this, 'onCreateBlog'], 10, 6);
        add_filter('wpmu_drop_tables', [$this, 'onDeleteBlog']);
        add_action('plugins_loaded', [$this, 'init']);
        add_action('admin_menu', [$this, 'adminMenu']);
        add_action('admin_init', [$this, 'adminInit']);

        // Metabox handling
        if ('1' === $this->optionsManager->metabox) {
            add_action( 'add_meta_boxes', [$this, 'metaBox'] );
            add_action( 'save_post', [$this, 'metaBoxSave'] );
        }

        register_activation_hook(__FILE__, [$this, 'onActivate']);
        register_deactivation_hook(__FILE__, [$this, 'onDeactivate']);
    }

    /**
     * Enqueue Scripts/CSS
     *
     * @return nothing
     */
    function enqueueScripts()
    {
        $id = get_the_ID();
        if (!is_home() && !is_404() && !is_archive() && !is_search()) {
            if (in_array($id, $this->optionsManager->disabled_post_ids)) $this->enabled = false;
            if (in_array(get_post_type(), $this->optionsManager->disabled_post_types)) $this->enabled = false;
        }
        $this->enabled = apply_filters('lbwps_enabled', $this->enabled, $id);
        if (!$this->enabled) {
            return;
        }

        if (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) {
            wp_enqueue_script(
                'lbwps-photoswipe',
                plugin_dir_url(__FILE__) . 'src/lib/photoswipe.js',
                [],
                self::LIGHTBOX_PHOTOSWIPE_VERSION,
                true
            );
            wp_enqueue_script(
                'lbwps-photoswipe-ui',
                plugin_dir_url(__FILE__) . 'src/lib/photoswipe-ui-default.js',
                [],
                self::LIGHTBOX_PHOTOSWIPE_VERSION,
                true
            );
            wp_enqueue_script(
                'lbwps',
                plugin_dir_url(__FILE__) . 'src/js/frontend.js',
                [],
                self::LIGHTBOX_PHOTOSWIPE_VERSION,
                true
            );
        } else {
            wp_enqueue_script(
                'lbwps',
                plugin_dir_url(__FILE__) . 'assets/scripts.js',
                [],
                self::LIGHTBOX_PHOTOSWIPE_VERSION,
                true
            );
        }
        $this->optionsManager->enqueueFrontendOptions();
        switch($this->optionsManager->skin) {
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
        if (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) {
            wp_enqueue_style(
                'lbwps-styles-photoswipe',
                plugin_dir_url(__FILE__) . 'src/lib/photoswipe.css',
                false,
                self::LIGHTBOX_PHOTOSWIPE_VERSION
            );
            wp_enqueue_style(
                'lbwps-styles',
                plugin_dir_url(__FILE__) . 'src/lib/skins/' . $skin . '/skin.css',
                false,
                self::LIGHTBOX_PHOTOSWIPE_VERSION
            );
        } else {
            wp_enqueue_style(
                'lbwps-styles',
                plugin_dir_url(__FILE__) . 'assets/styles/' . $skin . '.css',
                false,
                self::LIGHTBOX_PHOTOSWIPE_VERSION
            );
        }
    }

    /**
     * Footer in frontend with PhotoSwipe UI
     *
     * @return void
     */
    function outputFooter()
    {
        if (!$this->enabled) {
            return;
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
                <button class="pswp__button pswp__button--close wp-dark-mode-ignore" title="' . __('Close (Esc)', 'lightbox-photoswipe') . '"></button>
                <button class="pswp__button pswp__button--share wp-dark-mode-ignore" title="' . __('Share', 'lightbox-photoswipe') . '"></button>
                <button class="pswp__button pswp__button--fs wp-dark-mode-ignore" title="' . __('Toggle fullscreen', 'lightbox-photoswipe') . '"></button>
                <button class="pswp__button pswp__button--zoom wp-dark-mode-ignore" title="' . __('Zoom in/out', 'lightbox-photoswipe') . '"></button>
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
            <button class="pswp__button pswp__button--arrow--left wp-dark-mode-ignore" title="' . __('Previous (arrow left)', 'lightbox-photoswipe') . '"></button>
            <button class="pswp__button pswp__button--arrow--right wp-dark-mode-ignore" title="' . __('Next (arrow right)', 'lightbox-photoswipe') . '"></button>
            <div class="pswp__caption">
                <div class="pswp__caption__center"></div>
            </div>
        </div>
    </div>
</div>';
        $footer = apply_filters('lbwps_markup', $footer);
        echo $footer;

        if ($this->ob_active) {
            $this->ob_active = false;
            if($this->ob_level == ob_get_level()) {
                ob_end_flush();
            }
        }
    }

    /**
     * Callback to handle a single image link
     *
     * @param string $matches existing matches
     *
     * @return string modified HTML content
     */
    function callbackProperties($matches)
    {
        global $wpdb;

        $use = true;
        $attr = '';
        $baseurl_http = get_site_url(null, null, 'http');
        $baseurl_https = get_site_url(null, null, 'https');
        $url = $matches[2];

        // Remove parameters if any
        $urlparts = explode('?', $url);
        $file = $urlparts[0];

        // If URL is relative then add site URL
        if (substr($file, 0,  7) !== 'http://' && substr($file, 0, 8) !== 'https://') {
            $file = get_home_url() . $file;
        }

        $type = wp_check_filetype($file);
        $extension = strtolower($type['ext']);
        $captionCaption = '';
        $captionDescription = '';
        if (!in_array($extension, ['jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp', 'tif', 'tiff', 'ico', 'webp', 'svg'])) {
            // Ignore unknown image formats
            $use = false;
        } else {
            // Workaround for pictures served by Jetpack Photon CDN
            $file = preg_replace('/(i[0-2]\.wp.com\/)/s', '', $file);

            // Remove additional CDN URLs if defined
            $cdn_urls = explode(',', $this->optionsManager->cdn_url);
            if ('prefix' === $this->optionsManager->cdn_mode) {
                // Prefix mode: http://<cdn-url>/<website-url>

                foreach ($cdn_urls as $cdn_url) {
                    $length = strlen($cdn_url);
                    if ($length>0 && substr($file, 0, $length) == $cdn_url) {
                        $file = 'http://'.substr($file, $length);
                    }
                }
            } else {
                // Pull mode: http://<cdn-url>/<query path without domain>

                foreach ($cdn_urls as $cdn_url) {
                    $length = strlen($cdn_url);
                    if ($length>0 && substr($file, 0, $length) == $cdn_url) {
                        $file = $baseurl_http.'/'.ltrim(substr($file, $length),'/');
                    }
                }
            }

            if (substr($file, 0, strlen($baseurl_http)) == $baseurl_http || substr($file, 0, strlen($baseurl_https)) == $baseurl_https) {
                $is_local = true;
            } else {
                $is_local = false;
            }

            if (!$is_local && $this->optionsManager->ignore_external == '1') {
                // Ignore URL if it is an external URL and the respective option to ignore that is set
                $use = false;
            } else if (strpos($file, '#') !== false && $this->optionsManager->ignore_hash == '1') {
                // Ignore URL if it contains a hash the respective option to ignore that is set
                $use = false;
            }
        }

        if ($use) {
            // If image is served by the website itself, try to get caption for local file
            if ($is_local) {
                // Remove domain part
                $file = str_replace($baseurl_http.'/', '', $file);
                $file = str_replace($baseurl_https.'/', '', $file);

                // Remove leading slash
                $file = ltrim($file, '/');

                // Add local path only if the file is not an external URL
                if (substr($file, 0, 6) != 'ftp://' &&
                    substr($file, 0, 7) != 'http://' &&
                    substr($file, 0, 8) != 'https://') {
                    $upload_dir = wp_upload_dir(null, false)['basedir'];
                    $realFile = $this->str_replaceoverlap($upload_dir, $file);

                    // Using ABSPATH is not recommended, also see
                    // <https://github.com/arnowelzel/lightbox-photoswipe/issues/33>.
                    //
                    // However, there may be case where the image is not in the upload dir.
                    // So check if the file can be read and fall back to use ABSPATH if needed.

                    if ('' === $realFile || !is_readable($realFile)) {
                        $realFile = ABSPATH . $file;
                    }

                    $file = $realFile;
                }

                if ('1' == $this->optionsManager->use_postdata && '1' == $this->optionsManager->show_caption) {
                    // Fix provived by Emmanuel Liron - this will also cover scaled and rotated images
                    $basedir = wp_upload_dir()['basedir'];

                    // If the "fix image links" option is set, try to remove size parameters from the image link.
                    // For example: "image-1024x768.jpg" will become "image.jpg"
                    $sizeMatcher = '/(-[0-9]+x[0-9]+\.)(?:.(?!-[0-9]+x[0-9]+\.))+$/';
                    if ('1' === $this->optionsManager->fix_links) {
                        $fileFixed = preg_filter(
                                $sizeMatcher,
                                '.',
                                $file
                        );
                        if ($fileFixed !== null && $fileFixed !== $file) {
                            $file = $fileFixed . $extension;
                            $matches[2] = preg_filter($sizeMatcher, '.', $matches[2]) . $extension;
                        }
                    }
                    $shortfilename = str_replace ($basedir . '/', '', $file);
                    $imgid = $wpdb->get_col($wpdb->prepare('SELECT post_id FROM '.$wpdb->postmeta.' WHERE meta_key = "_wp_attached_file" and meta_value = %s;', $shortfilename));
                    if (isset($imgid[0])) {
                        $imgpost = get_post($imgid[0]);
                        $captionCaption = $imgpost->post_excerpt;
                        $captionTitle = $imgpost->post_title;
                        $captionDescription = $imgpost->post_content;
                    }
                }

                $imgMtime = @filemtime($file);
                if (false === $imgMtime) {
                    $imgMtime = 0;
                }
            } else {
                // For external files we don't try to get the modification time
                // as this can cause PHP warning messages in server logs
                $imgMtime = 0;
            }

            $imgkey = hash('md5', $file . $imgMtime);

            if ($this->optionsManager->use_cache) {
                $cache_key = "image:$imgkey";

                if (!$imgDetails = wp_cache_get($cache_key, 'lbwps')) {
                    $imageSize = $this->get_image_size($file, $extension);

                    if (false !== $imageSize && is_numeric($imageSize[0]) && is_numeric($imageSize[1]) && $imageSize[0] > 0 && $imageSize[1] > 0) {
                        $imgDetails = [
                            'imageSize'    => $imageSize,
                            'exifCamera'   => '',
                            'exifFocal'    => '',
                            'exifFstop'    => '',
                            'exifShutter'  => '',
                            'exifIso'      => '',
                            'exifDateTime' => '',
                        ];

                        if (in_array($extension, ['jpg', 'jpeg', 'jpe', 'tif', 'tiff']) && function_exists('exif_read_data')) {
                            $exif = @exif_read_data( $file, 'EXIF', true );
                            if (false !== $exif) {
                                $this->exifHelper->setExifData($exif);
                                $imgDetails['exifCamera']   = $this->exifHelper->getCamera();
                                $imgDetails['exifFocal']    = $this->exifHelper->getFocalLength();
                                $imgDetails['exifFstop']    = $this->exifHelper->getFstop();
                                $imgDetails['exifShutter']  = $this->exifHelper->getShutter();
                                $imgDetails['exifIso']      = $this->exifHelper->getIso();
                                $imgDetails['exifDateTime'] = $this->exifHelper->getDateTime();
                            }
                        }

                        wp_cache_add($cache_key, $imgDetails, 'lbwps', self::CACHE_EXPIRE_IMG_DETAILS);
                    }
                }

                if (is_array($imgDetails)) {
                    extract($imgDetails);
                }
            } else {
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
                    $imageSize = $this->get_image_size($file, $extension);
                    if (false !== $imageSize && is_numeric($imageSize[0]) && is_numeric($imageSize[1]) && $imageSize[0] > 0 && $imageSize[1] > 0) {
                        if (in_array($extension, ['jpg', 'jpeg', 'jpe', 'tif', 'tiff']) && function_exists('exif_read_data')) {
                            $exif = @exif_read_data($file, 'EXIF', true);
                            if (false !== $exif) {
                                $this->exifHelper->setExifData($exif);
                                $exifCamera = $this->exifHelper->getCamera();
                                $exifFocal = $this->exifHelper->getFocalLength();
                                $exifFstop = $this->exifHelper->getFstop();
                                $exifShutter = $this->exifHelper->getShutter();
                                $exifIso = $this->exifHelper->getIso();
                                $exifDateTime = $this->exifHelper->getDateTime();
                            }
                        }

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
            }

            $attr = '';
            if (is_array($imageSize) && isset($imageSize[0]) && isset($imageSize[1]) && 0 != $imageSize[0] && 0 != $imageSize[1]) {
                $width = $imageSize[0];
                $height = $imageSize[1];
                if ('svg' === $extension) {
                    $width = $width * $this->optionsManager->svg_scaling / 100;
                    $height = $height * $this->optionsManager->svg_scaling / 100;
                }
                $attr .= sprintf(' data-lbwps-width="%s" data-lbwps-height="%s"', $width, $height);

                if ('1' === $this->optionsManager->use_caption && $captionCaption != '') {
                    $attr .= sprintf(' data-lbwps-caption="%s"', htmlspecialchars(nl2br(wptexturize($captionCaption))));
                }

                if ('1' === $this->optionsManager->use_title && '' !== $captionTitle) {
                    $attr .= sprintf(' data-lbwps-title="%s"', htmlspecialchars(nl2br(wptexturize($captionTitle))));
                }

                if ('1' === $this->optionsManager->use_description && '' !== $captionDescription) {
                    $attr .= sprintf(' data-lbwps-description="%s"', htmlspecialchars(nl2br(wptexturize($captionDescription))));
                }

                if ('1' === $this->optionsManager->show_exif) {
                    $exifCaption = $this->exifHelper->buildCaptionString(
                        $exifFocal,
                        $exifFstop,
                        $exifShutter,
                        $exifIso,
                        $exifDateTime,
                        $exifCamera,
                        '1' === $this->optionsManager->show_exif_date
                    );
                    if ($exifCaption != '') {
                        $attr .= sprintf(' data-lbwps-exif="%s"', htmlspecialchars($exifCaption));
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
    function callbackLazyLoading($matches)
    {
        $replacement = $matches[4];
        if(false === strpos($replacement, 'loading="lazy"') && false === strpos($replacement, "loading='lazy'")
            && false === strpos($matches[0], 'loading="lazy"') && false === strpos($matches[0], "loading='lazy'")) {
            if ('/' === substr($replacement, -1)) {
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
    function callbackGalleryId($matches)
    {
        $attr = sprintf(' data-lbwps-gid="%s"', $this->gallery_id);
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
            [$this, 'callbackProperties'],
            $content
        );
        if ('1' === $this->optionsManager->add_lazyloading) {
            $content = preg_replace_callback(
                '/(<img.[^>]*src=["\'])(.[^"^\']*?)(["\'])([^>]*)(>)/sU',
                [$this, 'callbackLazyLoading'],
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

        ob_start([$this, 'filterOutput']);
        $this->ob_level = ob_get_level();
        $this->ob_active = true;
    }

    /**
     * Handler for gallery shortcode to add the gallery ID to the output
     *
     * @param array $attr Attributes passed to the shortcode
     *
     * @return array|string|string[]|null
     */
    function shortcodeGallery($attr)
    {
        $this->gallery_id++;
        $content = gallery_shortcode($attr);
        return preg_replace_callback(
            '/(<a.[^>]*href=["\'])(.[^"^\']*?)(["\'])([^>]*)(>)/sU',
            [$this, 'callbackGalleryId'],
            $content
        );
    }


    /**
     * Filter for Gutenberg blocks to add gallery ID to images
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
                [$this, 'callbackGalleryId'],
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
            [$this, 'settingsPage']
        );
    }

    /**
     * Initialization: Register settings
     *
     * @return void
     */
    function adminInit()
    {
        $this->optionsManager->registerOptions();
    }

    /**
     * Output settings page in backend
     *
     * @return void
     */
    function settingsPage()
    {
        $this->optionsManager->outputAdminSettingsPage();
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
                [$this, 'metaBoxOutputHtml'],
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
    function metaBoxOutputHtml($post)
    {
        wp_nonce_field( basename( __FILE__ ), 'lbwps_nonce' );

        $checked = '';
        if (in_array($post->ID, $this->optionsManager->disabled_post_ids)) {
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
            if (in_array($post_id, $this->optionsManager->disabled_post_ids)) {
                foreach ( $this->optionsManager->disabled_post_ids as $disabled_post_id ) {
                    if ((int)$post_id !== (int)$disabled_post_id) {
                        $disabled_post_ids[] = $disabled_post_id;
                    }
                }
                $this->optionsManager->disabled_post_ids = $disabled_post_ids;
                update_option( 'lightbox_photoswipe_disabled_post_ids', implode(',', $this->optionsManager->disabled_post_ids));
            }
        } else {
            if (!in_array($post_id, $this->optionsManager->disabled_post_ids)) {
                $this->optionsManager->disabled_post_ids[] = $post_id;
                update_option('lightbox_photoswipe_disabled_post_ids', implode(',', $this->optionsManager->disabled_post_ids));
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
            $this->optionsManager->setDefaultOptions();
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

        if (get_option('lightbox_photoswipe_use_cache') != '1') {
            $table_name = $wpdb->prefix . 'lightbox_photoswipe_img';
            $date = strftime('%Y-%m-%d %H:%M:%S', time() - 86400);
            $sql = "DELETE FROM $table_name where created<(\"$date\")";
            $wpdb->query($sql);
        }
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
            update_option('lightbox_photoswipe_disabled_post_ids', '');
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
        if (intval($db_version) < 25) {
            update_option('lightbox_photoswipe_disabled_post_types', '');
        }
        if (intval($db_version) < 26) {
            update_option('lightbox_photoswipe_use_cache', '0');
            update_option('lightbox_photoswipe_ignore_external', '0');
            update_option('lightbox_photoswipe_ignore_hash', '0');
        }
        if (intval($db_version) < 27) {
            update_option('lightbox_photoswipe_hide_scrollbars', '1');
        }
        if (intval($db_version) < 28) {
            update_option('lightbox_photoswipe_svg_scaling', '200');
        }
        if (intval($db_version) < 29) {
            update_option('lightbox_photoswipe_cdn_mode', 'prefix');
        }
        if (intval($db_version) < 30) {
            update_option('lightbox_photoswipe_fix_links', '1');
        }
        if (intval($db_version) < 31) {
            update_option('lightbox_photoswipe_usetitle', '0');
        }
        if (intval($db_version) < 32) {
            update_option('lightbox_photoswipe_usecaption', '1');
            update_option('lightbox_photoswipe_db_version', 32);
        }

        add_action('lbwps_cleanup', [$this, 'cleanupDatabase']);
    }

    /**
     * Helper to handle "use cache" option which deletes the cache tables if required.
     *
     * @param $old_value
     * @param $value
     * @param $option
     * @return void
     */
    function update_option_use_cache($old_value, $value, $option) {
        if (!$old_value && $value === '1' ) {
            $this->deleteTables();
        } else if ($old_value === '1' && !$value) {
            $this->createTables();
        }
    }

    /**
     * Helper to find strings overlapping
     *
     * @param $str1
     * @param $str2
     *
     * @return array|false
     */
    function str_findoverlap($str1, $str2){
        $return = array();
        $sl1 = strlen($str1);
        $sl2 = strlen($str2);
        $max = $sl1>$sl2?$sl2:$sl1;
        $i=1;
        while($i<=$max){
            $s1 = substr($str1, -$i);
            $s2 = substr($str2, 0, $i);
            if($s1 == $s2){
                $return[] = $s1;
            }
            $i++;
        }
        if(!empty($return)){
            return $return;
        }
        return false;
    }

    /**
     * Helper to replace strings overlapping
     *
     * @param $str1
     * @param $str2
     * @param $length
     *
     * @return false|string
     */
    function str_replaceoverlap($str1, $str2, $length = "long"){
        if($overlap = $this->str_findoverlap($str1, $str2)){
            switch($length){
                case "short":
                    $overlap = $overlap[0];
                    break;
                case "long":
                default:
                    $overlap = $overlap[count($overlap)-1];
                    break;
            }
            $str1 = substr($str1, 0, -strlen($overlap));
            $str2 = substr($str2, strlen($overlap));
            return $str1.$overlap.$str2;
        }
        return false;
    }

    /**
     * Helper to determine the size of an image
     *
     * @param $file
     * @param $extension
     *
     * @return array|false|int[]
     */
    function get_image_size($file, $extension) {
        $imageSize = [0, 0];
        if ($extension !== 'svg') {
            $imageSize = @getimagesize($file);
        } else {
            if (function_exists('simplexml_load_file')) {
                $svgContent = simplexml_load_file($file);
                if (false !== $svgContent) {
                    $svgAttributes = $svgContent->attributes();
                    if (isset($svgAttributes->width) && isset($svgAttributes->height)) {
                        $imageSize[0] = rtrim($svgAttributes->width, 'px');
                        $imageSize[1] = rtrim($svgAttributes->height, 'px');
                    } else {
                        $viewbox = false;
                        if(isset($svgAttributes->viewBox)) {
                            $viewbox = explode(' ', $svgAttributes->viewBox, 4);
                        } else if(isset($svgAttributes->viewbox)) {
                            $viewbox = explode(' ', $svgAttributes->viewbox, 4);
                        }
                        if ($viewbox !== false) {
                            $imageSize[0] = (int)($viewbox[2] - $viewbox[0]);
                            $imageSize[1] = (int)($viewbox[3] - $viewbox[1]);
                        }
                    }
                }
            }
        }

        return $imageSize;
    }
}

// Initialize plugin

$lightbox_photoswipe = new LightboxPhotoSwipe();
