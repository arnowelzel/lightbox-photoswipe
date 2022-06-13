<?php

namespace LightboxPhotoSwipe;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Main class for the plugin
 */
class LightboxPhotoSwipe
{
    const LIGHTBOX_PHOTOSWIPE_VERSION = '4.0.0';
    const SLUG = 'lightbox-photoswipe';
    const CACHE_EXPIRE_IMG_DETAILS = 86400;
    const DB_VERSION = 33;

    private string $pluginFile;
    private OptionsManager $optionsManager;
    private ExifHelper $exifHelper;
    private Environment $twig;

    private bool $enabled;
    private int $galleryId;
    private bool $obActive;
    private int $obLevel;

    /**
     * Constructor
     */
    public function __construct($pluginFile)
    {
        $this->pluginFile = $pluginFile;

        // If possible, create a cache folder for Twig
        $twigOptions = [];
        $wpCacheFolder = sprintf('%s/cache', WP_CONTENT_DIR);
        $twigCacheFolder = sprintf('%s/%s/twig/', $wpCacheFolder, self::SLUG);
        if (is_writable(WP_CONTENT_DIR)) {
            if (!file_exists($wpCacheFolder)) {
                mkdir($wpCacheFolder);
            }
        }
        if (is_writable($wpCacheFolder)) {
            if (!file_exists($twigCacheFolder)) {
                mkdir($twigCacheFolder, 0777, true);
            }
        }
        if (is_writable($twigCacheFolder)) {
            if (!defined('SCRIPT_DEBUG') || !SCRIPT_DEBUG) {
                $twigOptions['cache'] = $twigCacheFolder;
            }
        }
        // Initialize plugin
        $this->optionsManager = new OptionsManager();
        $this->exifHelper = new ExifHelper();

        // Initialize Twig and extensions
        $this->twig = new Environment(
            new FilesystemLoader(sprintf('%s/%s/templates', WP_PLUGIN_DIR, self::SLUG)),
            $twigOptions
        );
        $this->twig->addExtension(new TwigExtension($this->optionsManager));

        $this->enabled = true;
        $this->galleryId = 1;
        $this->obActive = false;
        $this->obLevel = 0;

        if (!is_admin()) {
            add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
            add_action('wp_footer', [$this, 'outputFooter']);
            add_action('wp_head', [$this, 'bufferStart'], 2050);
            if ($this->optionsManager->getOption('separate_galleries')) {
                remove_shortcode('gallery');
                add_shortcode('gallery', [$this, 'shortcodeGallery'], 10, 1);
                add_filter('render_block', [$this, 'gutenbergBlock'], 10, 2);
            }
        } else {
            add_action('update_option_lightbox_photoswipe_use_cache', [$this, 'update_option_use_cache'], 10, 3);
        }
        add_action('wpmu_new_blog', [$this, 'onCreateBlog'], 10, 6);
        add_filter('wpmu_drop_tables', [$this, 'onDeleteBlog']);
        add_action('plugins_loaded', [$this, 'init']);
        add_action('admin_menu', [$this, 'adminMenu']);
        add_action('admin_init', [$this, 'adminInit']);

        // Metabox handling only if enabled in the settings
        if ('1' === $this->optionsManager->getOption('metabox')) {
            add_action( 'add_meta_boxes', [$this, 'metaBox'] );
            add_action( 'save_post', [$this, 'metaBoxSave'] );
        }

        register_activation_hook($pluginFile, [$this, 'onActivate']);
        register_deactivation_hook($pluginFile, [$this, 'onDeactivate']);
    }

    /**
     * Helper to get the plugin URL
     */
    public function getPluginUrl(): string
    {
        return plugin_dir_url(WP_PLUGIN_DIR.'/').self::SLUG.'/';
    }

    /**
     * Enqueue Scripts/CSS
     */
    public function enqueueScripts(): void
    {
        $id = get_the_ID();
        if (!is_home() && !is_404() && !is_archive() && !is_search()) {
            if (in_array($id, $this->optionsManager->getOption('disabled_post_ids'))) {
                $this->enabled = false;
            }
            if (in_array(get_post_type(), $this->optionsManager->getOption('disabled_post_types'))) {
                $this->enabled = false;
            }
        }
        $this->enabled = apply_filters('lbwps_enabled', $this->enabled, $id);
        if (!$this->enabled) {
            return;
        }

        if (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) {
            wp_enqueue_script(
                'lbwps-photoswipe',
                sprintf('%ssrc/lib/photoswipe.js', $this->getPluginUrl()),
                [],
                self::LIGHTBOX_PHOTOSWIPE_VERSION,
                true
            );
            wp_enqueue_script(
                'lbwps-photoswipe-ui',
                sprintf('%ssrc/lib/photoswipe-ui-default.js', $this->getPluginUrl()),
                [],
                self::LIGHTBOX_PHOTOSWIPE_VERSION,
                true
            );
            wp_enqueue_script(
                'lbwps',
                sprintf('%ssrc/js/frontend.js', $this->getPluginUrl()),
                [],
                self::LIGHTBOX_PHOTOSWIPE_VERSION,
                true
            );
        } else {
            wp_enqueue_script(
                'lbwps',
                sprintf('%sassets/scripts.js', $this->getPluginUrl()),
                [],
                self::LIGHTBOX_PHOTOSWIPE_VERSION,
                true
            );
        }
        $this->enqueueFrontendOptions();
        switch ($this->optionsManager->getOption('skin')) {
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
                sprintf('%ssrc/lib/photoswipe.css', $this->getPluginUrl()),
                false,
                self::LIGHTBOX_PHOTOSWIPE_VERSION
            );
            wp_enqueue_style(
                'lbwps-styles',
                sprintf('%ssrc/lib/skins/%s/skin.css', $this->getPluginUrl(), $skin),
                false,
                self::LIGHTBOX_PHOTOSWIPE_VERSION
            );
        } else {
            wp_enqueue_style(
                'lbwps-styles',
                sprintf('%sassets/styles/%s.css', $this->getPluginUrl(), $skin),
                false,
                self::LIGHTBOX_PHOTOSWIPE_VERSION
            );
        }
    }

    /**
     * Output footer in frontend with PhotoSwipe UI
     */
    public function outputFooter(): void
    {
        if (!$this->enabled) {
            return;
        }

        $footer = $this->twig->render('frontend.html.twig');
        $footer = apply_filters('lbwps_markup', $footer);
        echo $footer;

        if ($this->obActive) {
            $this->obActive = false;
            if (ob_get_level() === $this->obLevel) {
                ob_end_flush();
            }
        }
    }

    /**
     * Callback to handle a single image link
     */
    public function callbackProperties(array $matches): string
    {
        global $wpdb;

        $use = true;
        $attr = '';
        $baseurlHttp = get_site_url(null, null, 'http');
        $baseurlHttps = get_site_url(null, null, 'https');
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
            $cdnUrls = explode(',', $this->optionsManager->getOption('cdn_url'));
            if ('prefix' === $this->optionsManager->getOption('cdn_mode')) {
                // Prefix mode: http://<cdn-url>/<website-url>

                foreach ($cdnUrls as $cdnUrl) {
                    $length = strlen($cdnUrl);
                    if ($length>0 && substr($file, 0, $length) === $cdnUrl) {
                        $file = 'http://'.substr($file, $length);
                    }
                }
            } else {
                // Pull mode: http://<cdn-url>/<query path without domain>

                foreach ($cdnUrls as $cdnUrl) {
                    $length = strlen($cdnUrl);
                    if ($length>0 && substr($file, 0, $length) === $cdnUrl) {
                        $file = $baseurlHttp.'/'.ltrim(substr($file, $length),'/');
                    }
                }
            }

            if (substr($file, 0, strlen($baseurlHttp)) === $baseurlHttp || substr($file, 0, strlen($baseurlHttps)) === $baseurlHttps) {
                $isLocal = true;
            } else {
                $isLocal = false;
            }

            if (!$isLocal && '1' === $this->optionsManager->getOption('ignore_external')) {
                // Ignore URL if it is an external URL and the respective option to ignore that is set
                $use = false;
            } else if (strpos($file, '#') !== false && '1' === $this->optionsManager->getOption('ignore_hash')) {
                // Ignore URL if it contains a hash the respective option to ignore that is set
                $use = false;
            }
        }

        if ($use) {
            // If image is served by the website itself, try to get caption for local file
            if ($isLocal) {
                // Remove domain part
                $file = str_replace($baseurlHttp.'/', '', $file);
                $file = str_replace($baseurlHttps.'/', '', $file);

                // Remove leading slash
                $file = ltrim($file, '/');

                // Add local path only if the file is not an external URL
                if (substr($file, 0, 6) != 'ftp://' &&
                    substr($file, 0, 7) != 'http://' &&
                    substr($file, 0, 8) != 'https://') {
                    $uploadDir = wp_upload_dir(null, false)['basedir'];
                    $realFile = $this->strReplaceOverlap($uploadDir, $file);

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

                if ('1' === $this->optionsManager->getOption('usepostdata') && '1' === $this->optionsManager->getOption('show_caption')) {
                    // Fix provived by Emmanuel Liron - this will also cover scaled and rotated images
                    $basedir = wp_upload_dir()['basedir'];

                    // If the "fix image links" option is set, try to remove size parameters from the image link.
                    // For example: "image-1024x768.jpg" will become "image.jpg"
                    $sizeMatcher = '/(-[0-9]+x[0-9]+\.)(?:.(?!-[0-9]+x[0-9]+\.))+$/';
                    if ('1' === $this->optionsManager->getOption('fix_links')) {
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

            if ($this->optionsManager->getOption('use_cache')) {
                $cacheKey = "image:$imgkey";

                if (!$imgDetails = wp_cache_get($cacheKey, 'lbwps')) {
                    $imageSize = $this->getImageSize($file, $extension);

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

                        wp_cache_add($cacheKey, $imgDetails, 'lbwps', self::CACHE_EXPIRE_IMG_DETAILS);
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
                    $imageSize = $this->getImageSize($file, $extension);
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
                    $width = $width * $this->optionsManager->getOption('svg_scaling') / 100;
                    $height = $height * $this->optionsManager->getOption('svg_scaling') / 100;
                }
                $attr .= sprintf(' data-lbwps-width="%s" data-lbwps-height="%s"', $width, $height);

                if ('1' === $this->optionsManager->getOption('usecaption') && $captionCaption != '') {
                    $attr .= sprintf(' data-lbwps-caption="%s"', htmlspecialchars(nl2br(wptexturize($captionCaption))));
                }

                if ('1' === $this->optionsManager->getOption('usetitle') && '' !== $captionTitle) {
                    $attr .= sprintf(' data-lbwps-title="%s"', htmlspecialchars(nl2br(wptexturize($captionTitle))));
                }

                if ('1' === $this->optionsManager->getOption('usedescription') && '' !== $captionDescription) {
                    $attr .= sprintf(' data-lbwps-description="%s"', htmlspecialchars(nl2br(wptexturize($captionDescription))));
                }

                if ('1' === $this->optionsManager->getOption('showexif')) {
                    $exifCaption = $this->exifHelper->buildCaptionString(
                        $exifFocal,
                        $exifFstop,
                        $exifShutter,
                        $exifIso,
                        $exifDateTime,
                        $exifCamera,
                        '1' === $this->optionsManager->getOption('showexif_date')
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
     */
    public function callbackLazyLoading(array $matches): string
    {
        $replacement = $matches[4];
        if (false === strpos($replacement, 'loading="lazy"') && false === strpos($replacement, "loading='lazy'")
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
     */
    public function callbackGalleryId(array $matches): string
    {
        $attr = sprintf(' data-lbwps-gid="%s"', $this->galleryId);
        return $matches[1].$matches[2].$matches[3].$matches[4].$attr.$matches[5];
    }

    /**
     * Output filter for post content
     */
    function filterOutput(string $content): string
    {
        $content = preg_replace_callback(
            '/(<a.[^>]*href=["\'])(.[^"^\']*?)(["\'])([^>]*)(>)/sU',
            [$this, 'callbackProperties'],
            $content
        );
        if ('1' === $this->optionsManager->getOption('add_lazyloading')) {
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
     */
    public function bufferStart(): void
    {
        if (!$this->enabled) {
            return;
        }

        ob_start([$this, 'filterOutput']);
        $this->obLevel = ob_get_level();
        $this->obActive = true;
    }

    /**
     * Handler for gallery shortcode to add the gallery ID to the output
     */
    public function shortcodeGallery(array $attr): string
    {
        $this->galleryId++;
        $content = gallery_shortcode($attr);
        return preg_replace_callback(
            '/(<a.[^>]*href=["\'])(.[^"^\']*?)(["\'])([^>]*)(>)/sU',
            [$this, 'callbackGalleryId'],
            $content
        );
    }


    /**
     * Filter for Gutenberg blocks to add gallery ID to images
     */
    public function gutenbergBlock(string $block_content, array $block): string
    {
        if ($block['blockName'] === 'core/gallery') {
            $this->galleryId++;
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
     */
    public function adminMenu(): void
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
     */
    public function adminInit(): void
    {
        $this->optionsManager->registerOptions();
    }

    /**
     * Output settings page in backend
     */
    public function settingsPage(): void
    {
        global $wpdb;

        echo $this->twig->render('options.html.twig', [
            'optionsManager' => $this,
            'wpdb' => $wpdb,
            'hasSimpleXML' => function_exists('simplexml_load_file'),
            'hasExif' => function_exists('exif_read_data'),
        ]);
    }

    /**
     * Add metabox for post editor
     */
    public function metaBox(): void
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
     */
    public function metaBoxOutputHtml($post): void
    {
        wp_nonce_field(basename( __FILE__ ), 'lbwps_nonce');

        $checked = '';
        if (in_array($post->ID, $this->optionsManager->getOption('disabled_post_ids'))) {
            $checked = 'checked="checked" ';
        }
        echo '<label for="lbwps_disabled"><input type="checkbox" id="lbwps_disabled" name="lbwps_disabled" value="1"'.$checked.'/>';
        echo __('Disable', 'lightbox-photoswipe').'</label>';
    }

    /**
     * Save options from metabox
     */
    public function metaBoxSave($postId): void
    {
        // Only save options if this is not an autosave
        $is_autosave = wp_is_post_autosave($postId);
        $is_revision = wp_is_post_revision($postId);
        $is_valid_nonce = (isset($_POST['lbwps_nonce']) && wp_verify_nonce($_POST['lbwps_nonce' ], basename(__FILE__)))?'true':'false';

        if ($is_autosave || $is_revision || !$is_valid_nonce ) {
            return;
        }

        // Save post specific options
        $disabledPostIdsCurrent = $this->optionsManager->getOption('disabled_post_ids');
        if (!isset($_POST['lbwps_disabled']) || $_POST['lbwps_disabled']!='1') {
            $disabledPostIdsNew = [];
            if (in_array($postId, $disabledPostIdsCurrent)) {
                foreach ( $disabledPostIdsCurrent as $disabledPostIdCurrent ) {
                    if ((int)$postId !== (int)$disabledPostIdCurrent) {
                        $disabledPostIdsNew[] = $disabledPostIdCurrent;
                    }
                }
                $this->optionsManager->setOption('disabled_post_ids', $disabledPostIdsNew, true);
            }
        } else {
            if (!in_array($postId, $disabledPostIdsCurrent)) {
                $disabledPostIdsCurrent[] = $postId;
                $this->optionsManager->setOption('disabled_post_ids', $disabledPostIdsCurrent, true);
            }
        }
    }

    /**
     * Handler for creating a new blog
     */
    public function onCreateBlog($blog_id, $user_id, $domain, $path, $site_id, $meta): void
    {
        if (is_plugin_active_for_network('lightbox-photoswipe/lightbox-photoswipe.php')) {
            switch_to_blog($blog_id);
            $this->createTables();
            restore_current_blog();
        }
    }

    /**
     * Filter for deleting a blog
     */
    public function onDeleteBlog($tables): array
    {
        global $wpdb;

        $tables[] = $wpdb->prefix . 'lightbox_photoswipe_img';

        return $tables;
    }

    /**
     * Hook for plugin activation
     */
    public function onActivate(): void
    {
        $this->addCleanupJob();
    }

    /**
     * Hook for plugin deactivation
     */
    public function onDeactivate(): void
    {
        // Remove scheduled clean up job
        wp_clear_scheduled_hook('lbwps_cleanup');
    }

    /**
     * Scheduled job for database cleanup
     * This will remove cached image data which is older than 24 hours
     */
    public function cleanupDatabase(): void
    {
        global $wpdb;

        if ($this->optionsManager->getOption('use_cache') != '1') {
            $table_name = $wpdb->prefix . 'lightbox_photoswipe_img';
            $date = strftime('%Y-%m-%d %H:%M:%S', time() - 86400);
            $sql = "DELETE FROM $table_name where created<(\"$date\")";
            $wpdb->query($sql);
        }
    }

    /**
     * Plugin initialization, will be called after all plugins have been loaded
     */
    public function init(): void
    {
        load_plugin_textdomain('lightbox-photoswipe', false, 'lightbox-photoswipe/languages/');

        $dbVersion = $this->optionsManager->getOption('db_version');

        if (intval($dbVersion) < 3) {
            delete_option('disabled_post_ids');
        }
        if (intval($dbVersion) < 10) {
            $this->onActivate();
        }
        if (intval($dbVersion) < 22) {
            $this->deleteTables();
            $this->createTables();
        }
        if (intval($dbVersion) < 33) {
            // After changing the plugin to a class structure, the
            // hooks for activation and deactivation did not get called
            // any longer :-(
            //
            // Therefore we need to make sure, that the clean up job
            // is activated which usually is done for activation only.
            $this->addCleanupJob();
        }

        if ((int)$dbVersion !== self::DB_VERSION) {
            $this->cleanupTwigCache();
            $this->optionsManager->setOption('db_version', self::DB_VERSION, true);
        }

        add_action('lbwps_cleanup', [$this, 'cleanupDatabase']);
    }

    /**
     * Helper to handle "use cache" option which deletes the cache tables if required.
     */
    public function update_option_use_cache($old_value, $value, $option): void
    {
        if (!$old_value && $value === '1' ) {
            $this->deleteTables();
        } else if ($old_value === '1' && !$value) {
            $this->createTables();
        }
    }

    /**
     * Enqueue options for frontend script
     */
    protected function enqueueFrontendOptions(): void
    {
        $translation_array = [
            'label_facebook' => __('Share on Facebook', LightboxPhotoSwipe::SLUG),
            'label_twitter' => __('Tweet', LightboxPhotoSwipe::SLUG),
            'label_pinterest' => __('Pin it', LightboxPhotoSwipe::SLUG),
            'label_download' => __('Download image', LightboxPhotoSwipe::SLUG),
            'label_copyurl' => __('Copy image URL', LightboxPhotoSwipe::SLUG)
        ];
        $boolOptions = [
            'share_facebook',
            'share_twitter',
            'share_pinterest',
            'share_download',
            'share_direct',
            'share_copyurl',
            'close_on_drag',
            'history',
            'show_counter',
            'show_fullscreen',
            'show_zoom',
            'show_caption',
            'loop',
            'pinchtoclose',
            'taptotoggle',
            'close_on_click',
            'fulldesktop',
            'use_alt',
            'usecaption',
            'desktop_slider',
        ];
        foreach($boolOptions as $boolOption) {
            $translation_array[$boolOption] = $this->optionsManager->getOption($boolOption) === '1' ? '1' : '0';
        }
        $customLink = ('' === $this->optionsManager->getOption('share_custom_link'))?'{{raw_image_url}}':$this->optionsManager->getOption('share_custom_link');
        $translation_array['share_custom_label'] = ($this->optionsManager->getOption('share_custom') == '1')?htmlspecialchars($this->optionsManager->getOption('share_custom_label')):'';
        $translation_array['share_custom_link'] = ($this->optionsManager->getOption('share_custom') == '1')?htmlspecialchars($customLink):'';
        $translation_array['wheelmode'] = htmlspecialchars($this->optionsManager->getOption('wheelmode'));
        $translation_array['spacing'] = intval($this->optionsManager->getOption('spacing'));
        $translation_array['idletime'] = intval($this->optionsManager->getOption('idletime'));
        $translation_array['hide_scrollbars'] = intval($this->optionsManager->getOption('hide_scrollbars'));
        wp_localize_script('lbwps', 'lbwpsOptions', $translation_array);
    }

    /**
     * Create custom database tables
     */
    protected function createTables(): void
    {
        global $wpdb;

        $tableName = $wpdb->prefix . 'lightbox_photoswipe_img';
        $charsetCollate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $tableName (
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
        ) $charsetCollate;";
        include_once ABSPATH.'wp-admin/includes/upgrade.php';
        $wpdb->query($sql);
    }

    /**
     * Delete custom database tables
     */
    protected function deleteTables(): void
    {
        global $wpdb;

        $table_name = $wpdb->prefix.'lightbox_photoswipe_img';
        $sql = "DROP TABLE IF EXISTS $table_name";
        $wpdb->query($sql);
    }

    /**
     * Add cleanup job if needed
     *
     * @return void
     */
    protected function addCleanupJob(): void
    {
        if (!wp_next_scheduled('lbwps_cleanup')) {
            wp_schedule_event(time(), 'hourly', 'lbwps_cleanup');
        }
    }

    /**
     * Helper to find strings overlapping
     */
    protected function strFindOverlap(string $str1, string $str2)
    {
        $return = [];
        $sl1 = strlen($str1);
        $sl2 = strlen($str2);
        $max = $sl1>$sl2?$sl2:$sl1;
        $i=1;
        while($i<=$max){
            $s1 = substr($str1, -$i);
            $s2 = substr($str2, 0, $i);
            if ($s1 === $s2){
                $return[] = $s1;
            }
            $i++;
        }
        if (!empty($return)){
            return $return;
        }
        return false;
    }

    /**
     * Helper to replace strings overlapping
     */
    protected function strReplaceOverlap(string $str1, string $str2, string $length = "long")
    {
        if ($overlap = $this->strFindOverlap($str1, $str2)){
            switch ($length) {
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
     */
    protected function getImageSize($file, $extension)
    {
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
                        $viewBox = false;
                        if (isset($svgAttributes->viewBox)) {
                            $viewBox = explode(' ', $svgAttributes->viewBox, 4);
                        } else if (isset($svgAttributes->viewbox)) {
                            $viewBox = explode(' ', $svgAttributes->viewbox, 4);
                        }
                        if ($viewBox !== false) {
                            $imageSize[0] = (int)($viewBox[2] - $viewBox[0]);
                            $imageSize[1] = (int)($viewBox[3] - $viewBox[1]);
                        }
                    }
                }
            }
        }

        return $imageSize;
    }

    /**
     * Clean up Twig cache
     */
    protected function cleanupTwigCache(): void
    {
        // Clean up Twig cache if needed
        $cacheFolder = WP_CONTENT_DIR.'/cache/'.self::SLUG;
        if (is_writable($cacheFolder)) {
            $path = $cacheFolder;
            $it = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new \RecursiveIteratorIterator($it,
                \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                if ($file->isDir()){
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            rmdir($path);
        }
    }
}
