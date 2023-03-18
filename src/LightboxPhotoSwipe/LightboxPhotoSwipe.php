<?php

namespace LightboxPhotoSwipe;

/**
 * Main class for the plugin
 */
class LightboxPhotoSwipe
{
    const VERSION = '5.0.23';
    const SLUG = 'lightbox-photoswipe';
    const META_VERSION = '12';
    const CACHE_EXPIRE_IMG_DETAILS = 86400;
    const DB_VERSION = 36;
    const BASEPATH = WP_PLUGIN_DIR.'/'.self::SLUG.'/';

    private $pluginFile;
    private $optionsManager;
    private $exifHelper;
    private $imageSizes;

    private $enabled;
    private $galleryId;
    private $obActive;
    private $obLevel;

    /**
     * Constructor
     */
    public function __construct($pluginFile)
    {
        $this->pluginFile = $pluginFile;

        // Initialize plugin
        $this->optionsManager = new OptionsManager();
        $this->exifHelper = new ExifHelper();

        $this->enabled = true;
        $this->galleryId = 1;
        $this->obActive = false;
        $this->obLevel = 0;

        if (!is_admin()) {
            add_filter('script_loader_tag', [$this,'addScriptModule'] , 10, 3);
            add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
            add_action('wp_footer', [$this, 'outputFooter']);
            add_action('wp_head', [$this, 'bufferStart'], 2050);
            if ($this->optionsManager->getOption('separate_galleries')) {
                remove_shortcode('gallery');
                add_shortcode('gallery', [$this, 'shortcodeGallery'], 10, 1);
                add_filter('render_block', [$this, 'gutenbergBlock'], 10, 2);
            }
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
    public function getPluginUrl()
    {
        return plugin_dir_url(WP_PLUGIN_DIR.'/').self::SLUG.'/';
    }

    /**
     * Add module attribute to PhotoSwipe 5 script
     */
    function addScriptModule($tag, $handle, $src)
    {
        if ( 'lbwps-photoswipe5' !== $handle ) {
            return $tag;
        }
        $tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
        return $tag;
    }

    /**
     * Enqueue Scripts/CSS
     */
    public function enqueueScripts()
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

        $version = apply_filters('lbwps_version', $this->optionsManager->getOption('version'), get_the_ID());
        if (5 === (int)$version) {
            $handle = 'lbwps-photoswipe5';
            if (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) {
                wp_enqueue_script(
                    'lbwps-photoswipe5',
                    sprintf('%sassets/ps5/frontend.js', $this->getPluginUrl()),
                    [],
                    self::VERSION,
                    true
                );
                wp_enqueue_style(
                    'lbwps-styles-photoswipe5-local',
                    sprintf('%sassets/ps5/lib/photoswipe-local.css', $this->getPluginUrl()),
                    false,
                    self::VERSION
                );
                wp_enqueue_style(
                    'lbwps-styles-photoswipe5-dynamic-caption',
                    sprintf(
                        '%sassets/ps5/dynamic-caption/photoswipe-dynamic-caption-plugin.css',
                        $this->getPluginUrl()
                    ),
                    false,
                    self::VERSION
                );
            } else {
                wp_enqueue_script(
                    'lbwps-photoswipe5',
                    sprintf('%sassets/ps5/frontend.min.js', $this->getPluginUrl()),
                    [],
                    self::VERSION,
                    true
                );
                wp_enqueue_style(
                    'lbwps-styles-photoswipe5-main',
                    sprintf('%sassets/ps5/styles/main.css', $this->getPluginUrl()),
                    false,
                    self::VERSION
                );
            }
        } else {
            $handle = 'lbwps';
            if (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) {
                wp_enqueue_script(
                    'lbwps-photoswipe',
                    sprintf('%ssrc/lib/photoswipe.js', $this->getPluginUrl()),
                    [],
                    self::VERSION,
                    true
                );
                wp_enqueue_script(
                    'lbwps-photoswipe-ui',
                    sprintf('%ssrc/lib/photoswipe-ui-default.js', $this->getPluginUrl()),
                    [],
                    self::VERSION,
                    true
                );
                wp_enqueue_script(
                    'lbwps',
                    sprintf('%ssrc/js/frontend.js', $this->getPluginUrl()),
                    [],
                    self::VERSION,
                    true
                );
            } else {
                wp_enqueue_script(
                    'lbwps',
                    sprintf('%sassets/ps4/scripts.js', $this->getPluginUrl()),
                    [],
                    self::VERSION,
                    true
                );
            }
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
                    self::VERSION
                );
                wp_enqueue_style(
                    'lbwps-styles',
                    sprintf('%ssrc/lib/skins/%s/skin.css', $this->getPluginUrl(), $skin),
                    false,
                    self::VERSION
                );
            } else {
                wp_enqueue_style(
                    'lbwps-styles',
                    sprintf('%sassets/ps4/styles/%s.css', $this->getPluginUrl(), $skin),
                    false,
                    self::VERSION
                );
            }
        }
        $this->enqueueFrontendOptions($handle);
    }

    /**
     * Output footer in frontend with PhotoSwipe UI
     */
    public function outputFooter()
    {
        if (!$this->enabled) {
            return;
        }

        $version = apply_filters('lbwps_version', $this->optionsManager->getOption('version'), get_the_ID());
        if (4 !== (int)$version) {
            return;
        }

        ob_start();
        include(self::BASEPATH.'templates/frontend.inc.php');
        $footer = ob_get_clean();

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
    public function callbackProperties(array $matches)
    {
        global $wpdb;

        $use = true;
        $attr = '';
        $baseurlHttp = get_site_url(null, null, 'http');
        $baseurlHttps = get_site_url(null, null, 'https');
        $url = $matches[2];

        // Remove fragments and parameters from URL
        $hasFragment = false;
        $params = '';
        $urlParts = explode('#', $url);
        if (count($urlParts) > 1) {
            $file = $urlParts[0];
            $hasFragment = true;
            $urlParts = explode('?', $urlParts[1]);
        } else {
            $urlParts = explode('?', $url);
            $file = $urlParts[0];
        }
        if (count($urlParts) > 1) {
            $params = '?'.$urlParts[1];
        }

        // If URL is relative then add site URL
        if (substr($file, 0,  7) !== 'http://' && substr($file, 0, 8) !== 'https://') {
            $file = get_home_url() . $file;
        }

        $type = wp_check_filetype($file);
        $extension = strtolower($type['ext']);
        $captionCaption = '';
        $captionDescription = '';
        $captionTitle = '';
        $isLocal = false;
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
                $params = '';
            }

            if (!$isLocal && '1' === $this->optionsManager->getOption('ignore_external')) {
                // Ignore URL if it is an external URL and the respective option to ignore that is set
                $use = false;
            } else if ($hasFragment && '1' === $this->optionsManager->getOption('ignore_hash')) {
                // Ignore URL if it contains a hash the respective option to ignore that is set
                $use = false;
            }
        }

        if ($use) {
            $uploadDir = wp_upload_dir()['basedir'];
            $uploadUrl = wp_upload_dir()['baseurl'];

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
                    $localDir = wp_upload_dir(null, false)['basedir'];
                    $realFile = $this->strReplaceOverlap($localDir, $file);

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

                // Keep original file name for metadata retrieval
                $fileOriginal = $file;

                // If the "fix image links" option is set, try to remove size parameters from the image link.
                // For example: "image-1024x768.jpg" will become "image.jpg"
                $sizeMatcher = '/(-[0-9]+x[0-9]+\.)(?:.(?!-[0-9]+x[0-9]+\.)).+$/';
                if ('1' === $this->optionsManager->getOption('fix_links')) {
                    $fileFixed = preg_filter($sizeMatcher, '.', $file);
                    if ($fileFixed !== null && $fileFixed !== $file) {
                        $file = $fileFixed . $extension;
                        $matches[2] = preg_filter($sizeMatcher, '.', $matches[2]) . $extension;
                    }
                }
                // If the "fix scaled image links" option is set, try to remove "-scaled" from the image link.
                // For example: "image-scaled.jpg" will become "image.jpg"
                $scaledMatcher = '/(-scaled\.).+$/';
                if ('1' === $this->optionsManager->getOption('fix_scaled')) {
                    $fileFixed = preg_filter($scaledMatcher, '.', $file);
                    if ($fileFixed !== null && $fileFixed !== $file) {
                        $file = $fileFixed . $extension;
                        $matches[2] = preg_filter($scaledMatcher, '.', $matches[2]) . $extension;
                    }
                }

                // Try to get metadata from database
                if ('1' === $this->optionsManager->getOption('usepostdata') && '1' === $this->optionsManager->getOption('show_caption')) {
                    $imgId = $wpdb->get_col(
                        $wpdb->prepare(
                            'SELECT post_id FROM '.$wpdb->postmeta.' WHERE meta_key = "_wp_attached_file" and meta_value = %s;',
                            str_replace ($uploadDir . '/', '', $fileOriginal)
                        )
                    );
                    if (isset($imgId[0])) {
                        $imgPost = get_post($imgId[0]);
                        $captionCaption = $imgPost->post_excerpt;
                        $captionTitle = $imgPost->post_title;
                        $captionDescription = $imgPost->post_content;
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

            $cacheKey = sprintf('%s-%s-image-%s',self::META_VERSION, self::SLUG, hash('md5', $file.$imgMtime));
            if (!$imgDetails = get_transient($cacheKey)) {
                $imageSize = $this->getImageSize($file . $params, $extension);
                if (false !== $imageSize && is_numeric($imageSize[0]) && is_numeric($imageSize[1]) && $imageSize[0] > 0 && $imageSize[1] > 0) {
                    $pathInfo = pathinfo($file);
                    $fileSmall = $file;
                    if ($isLocal) {
                        for ($n=-1; $n<2; $n++) {
                            if ($imageSize[0] > $imageSize[1]) {
                                // portrait
                                $fileSmallTest = sprintf(
                                    '%s/%s-%dx%d.%s',
                                    $pathInfo['dirname'],
                                    $pathInfo['filename'],
                                    $this->imageSizes[0]['width'],
                                    $imageSize[1] / $imageSize[0] * $this->imageSizes[0]['width'] + $n,
                                    $pathInfo['extension']
                                );
                            } else {
                                // landscape
                                $fileSmallTest = sprintf(
                                    '%s/%s-%dx%d.%s',
                                    $pathInfo['dirname'],
                                    $pathInfo['filename'],
                                    $imageSize[0] / $imageSize[1] * $this->imageSizes[0]['height'] + $n,
                                    $this->imageSizes[0]['height'],
                                    $pathInfo['extension']
                                );
                            }
                            if (file_exists($fileSmallTest)) {
                                $fileSmall = $fileSmallTest;
                            }
                        }
                    }
                    if ($uploadDir && $uploadUrl) {
                        $fileSmall = str_replace($uploadDir, $uploadUrl, $fileSmall);
                    }
                    if (substr($fileSmall, 0, 1) === '/') {
                        $fileSmall = '';
                    }
                    $imgDetails = [
                        'imageSize'       => $imageSize,
                        'fileSmall'       => $fileSmall,
                        'exifCamera'      => '',
                        'exifFocal'       => '',
                        'exifFstop'       => '',
                        'exifShutter'     => '',
                        'exifIso'         => '',
                        'exifDateTime'    => '',
                        'exifOrientation' => '',
                    ];
                    if (in_array($extension, ['jpg', 'jpeg', 'jpe', 'tif', 'tiff']) && function_exists('exif_read_data')) {
                        $exif = @exif_read_data( $file . $params, 'EXIF', true );
                        if (false !== $exif) {
                            $this->exifHelper->setExifData($exif);
                            $imgDetails['exifCamera']   = $this->exifHelper->getCamera();
                            $imgDetails['exifFocal']    = $this->exifHelper->getFocalLength();
                            $imgDetails['exifFstop']    = $this->exifHelper->getFstop();
                            $imgDetails['exifShutter']  = $this->exifHelper->getShutter();
                            $imgDetails['exifIso']      = $this->exifHelper->getIso();
                            $imgDetails['exifDateTime'] = $this->exifHelper->getDateTime();
                            if (isset($exif['IFD0']['Orientation'])) {
                                $imgDetails['exifOrientation'] = $exif['IFD0']['Orientation'];
                            }
                            // If the image is rotated, width and height may need to be swapped
                            if (in_array($imgDetails['exifOrientation'], [5, 6, 7, 8])) {
                                $swap = $imgDetails['imageSize'][0];
                                $imgDetails['imageSize'][0] = $imgDetails['imageSize'][1];
                                $imgDetails['imageSize'][1] = $swap;
                            }
                        }
                    }
                    set_transient($cacheKey, $imgDetails, self::CACHE_EXPIRE_IMG_DETAILS);
                }
            }

            if (is_array($imgDetails)) {
                extract($imgDetails);
            }

            $attr = '';
            if (is_array($imageSize) && isset($imageSize[0]) && isset($imageSize[1]) && 0 != $imageSize[0] && 0 != $imageSize[1]) {
                $id = get_the_ID();
                $width = $imageSize[0];
                $height = $imageSize[1];
                if ('svg' === $extension) {
                    $width = $width * $this->optionsManager->getOption('svg_scaling') / 100;
                    $height = $height * $this->optionsManager->getOption('svg_scaling') / 100;
                }
                $attr .= sprintf(' data-lbwps-width="%s" data-lbwps-height="%s"', $width, $height);
                // TODO: make using preview size configurable
                if (isset($imgDetails['fileSmall']) && '' !== $imgDetails['fileSmall']) {
                    $attr .= sprintf(' data-lbwps-srcsmall="%s"', $imgDetails['fileSmall']);
                }
                if ('1' === $this->optionsManager->getOption('usecaption') && $captionCaption != '') {
                    $captionCaption = apply_filters('lbwps_caption_caption', $captionCaption, $id);
                    $attr .= sprintf(' data-lbwps-caption="%s"', htmlspecialchars(nl2br(wptexturize($captionCaption))));
                }
                if ('1' === $this->optionsManager->getOption('usetitle') && '' !== $captionTitle) {
                    $captionTitle = apply_filters('lbwps_caption_title', $captionTitle, $id);
                    $attr .= sprintf(' data-lbwps-title="%s"', htmlspecialchars(nl2br(wptexturize($captionTitle))));
                }
                if ('1' === $this->optionsManager->getOption('usedescription') && '' !== $captionDescription) {
                    $captionDescription = apply_filters('lbwps_caption_description', $captionDescription, $id);
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
                        $exifCaption = apply_filters('lbwps_caption_exif', $exifCaption, $id);
                        $attr .= sprintf(' data-lbwps-exif="%s"', htmlspecialchars($exifCaption));
                    }
                }
            }
        }

        return $matches[1] . $matches[2] . $matches[3] . $matches[4] . $attr . $matches[5];
    }

    /**
     * Callback to add current gallery id to a single image
     */
    public function callbackGalleryId(array $matches)
    {
        $attr = sprintf(' data-lbwps-gid="%s"', $this->galleryId);
        return $matches[1].$matches[2].$matches[3].$matches[4].$attr.$matches[5];
    }

    /**
     * Output filter for post content
     */
    public function filterOutput(string $content)
    {
        return preg_replace_callback(
            '/(<a.[^>]*href=["\'])(.[^"^\']*?)(["\'])([^>]*)(>)/sU',
            [$this, 'callbackProperties'],
            $content
        );
    }

    /**
     * Output filter for post content
     */
    public function bufferStart()
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
    public function shortcodeGallery(array $attr)
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
    public function gutenbergBlock(string $block_content, array $block)
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
    public function adminMenu()
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
    public function adminInit()
    {
        $this->optionsManager->registerOptions();
    }

    /**
     * Output settings page in backend
     */
    public function settingsPage()
    {
        include(self::BASEPATH.'templates/options.inc.php');
    }

    /**
     * Add metabox for post editor
     */
    public function metaBox()
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
    public function metaBoxOutputHtml($post)
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
    public function metaBoxSave($postId)
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
    public function onCreateBlog($blog_id, $user_id, $domain, $path, $site_id, $meta)
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
    public function onActivate()
    {
    }

    /**
     * Hook for plugin deactivation
     */
    public function onDeactivate()
    {
        wp_clear_scheduled_hook('lbwps_cleanup');
    }

    /**
     * Plugin initialization, will be called after all plugins have been loaded
     */
    public function init()
    {
        global $wpdb;

        load_plugin_textdomain('lightbox-photoswipe', false, 'lightbox-photoswipe/languages/');
        $dbVersion = $this->optionsManager->getOption('db_version');
        if (intval($dbVersion) < 3) {
            delete_option('disabled_post_ids');
        }
        if (intval($dbVersion) < 10) {
            $this->onActivate();
        }
        if (intval($dbVersion) < 22) {
            $this->deleteDatabaseTables();
        }
        if (intval($dbVersion) < 34) {
            // We don't use table based caching and don't need a cleanup job any longer
            delete_option('lightbox_photoswipe_use_cache');
            wp_clear_scheduled_hook('lbwps_cleanup');
            $table_name = $wpdb->prefix.'lightbox_photoswipe_img';
            $sql = "DROP TABLE IF EXISTS $table_name";
            $wpdb->query($sql);
        }
        if (intval($dbVersion) < 36) {
            delete_option('lightbox_photoswipe_add_lazyloading');
        }
        if ((int)$dbVersion !== self::DB_VERSION) {
            $this->cleanupTwigCache();
            $this->optionsManager->setOption('db_version', self::DB_VERSION, true);
        }

        $this->imageSizes = $this->getWPImageSizes();
    }

    /**
     * Cleanup when uninstalling the plugin
     *
     * @return void
     */
    function uninstallPluginData()
    {
        global $wpdb;

        $optionsManager = new OptionsManager();

        if (is_multisite()) {
            $blog_ids = $wpdb->get_col('SELECT blog_id FROM '.$wpdb->blogs);
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                $optionsManager->deleteOptions();
                restore_current_blog();
            }
        } else {
            $optionsManager->deleteOptions();
        }
    }

    /**
     * Output the form opening in the backend
     */
    public function uiFormStart()
    {
        echo '<form method="post" action="options.php">';
        settings_fields('lightbox-photoswipe-settings-group');
    }

    /**
     * Output the form closing in the backend
     */
    public function uiFormEnd()
    {
        submit_button();
        echo '</form>';
    }

    /**
     * Output text control with an optional placeholder in the admin page
     */
    public function uiControlText(string $name, string $placeholder = '', string $class = 'regular-text')
    {
        switch ($this->optionsManager->getOptionType($name)) {
            case 'list':
                $value = implode(',', $this->optionsManager->getOption($name));
                break;

            default:
                $value = $this->optionsManager->getOption($name);
                break;
        }

        echo sprintf(
            '<input id="%1$s" class="%2$s" type="text" name="%1$s" value="%3$s" placeholder="%4$s" />',
            esc_attr('lightbox_photoswipe_'.$name),
            esc_attr($class),
            esc_attr($value),
            esc_attr($placeholder)
        );
    }

    /**
     * Output a checkbox control in the admin page
     */
    public function uiControlCheckbox(string $name)
    {
        echo sprintf(
            '<input id="%1$s" type="checkbox" name="%1$s" value="1"%2$s/>',
            esc_attr('lightbox_photoswipe_'.$name),
            1 === (int)$this->optionsManager->getOption($name) ? ' checked' : ''
        );
    }

    /**
     * Output group of radio controls with custom separator in the admin page
     */
    public function uiControlRadio(string $name, array $optionValues, array $optionLabels, string $separator)
    {
        $value = $this->optionsManager->getOption($name);
        $output = '';
        $num = 0;
        while ($num < count($optionValues)) {
            $output .= sprintf(
                '<label style="margin-right:0.5em"><input id="%1$s-%2$d" type="radio" name="%1$s" value="%3$s"%4$s/>%5$s</label>%6$s',
                esc_attr('lightbox_photoswipe_'.$name),
                $num,
                $optionValues[$num],
                $value === $optionValues[$num] ? ' checked' : '',
                $optionLabels[$num] ?? '',
                $separator
            );
            $num++;
        }

        echo $output;
    }

    /**
     * Output all available post types as comma separated text
     *
     * @return string
     */
    public function uiGetPostTypes()
    {
        echo _wp_specialchars(implode(', ', get_post_types()));
    }

    /**
     * Make sure the old caching tables are removed when uninstalling the plugin
     *
     * @return void
     */
    protected function deleteDatabaseTables()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'lightbox_photoswipe_img';
        $sql = "DROP TABLE IF EXISTS $table_name";
        $wpdb->query($sql);
    }

    /**
     * Enqueue options for frontend script
     */
    protected function enqueueFrontendOptions($handle)
    {
        $translation_array = [
            'label_facebook' => __('Share on Facebook', 'lightbox-photoswipe'),
            'label_twitter' => __('Tweet', 'lightbox-photoswipe'),
            'label_pinterest' => __('Pin it', 'lightbox-photoswipe'),
            'label_download' => __('Download image', 'lightbox-photoswipe'),
            'label_copyurl' => __('Copy image URL', 'lightbox-photoswipe'),
            'label_ui_close' => __('Close [Esc]', 'lightbox-photoswipe'),
            'label_ui_zoom' => __('Zoom', 'lightbox-photoswipe'),
            'label_ui_prev' => __('Previous [←]', 'lightbox-photoswipe'),
            'label_ui_next' => __('Next [→]', 'lightbox-photoswipe'),
            'label_ui_error' => __('The image cannot be loaded', 'lightbox-photoswipe'),
            'label_ui_fullscreen' => __('Toggle fullscreen [F]', 'lightbox-photoswipe'),
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
        $translation_array['caption_type'] = apply_filters('lbwps_caption_type', $this->optionsManager->getOption('caption_type'), get_the_ID());
        $translation_array['bg_opacity'] = intval($this->optionsManager->getOption('bg_opacity'));
        $translation_array['padding_left'] = intval($this->optionsManager->getOption('padding_left'));
        $translation_array['padding_top'] = intval($this->optionsManager->getOption('padding_top'));
        $translation_array['padding_right'] = intval($this->optionsManager->getOption('padding_right'));
        $translation_array['padding_bottom'] = intval($this->optionsManager->getOption('padding_bottom'));
        wp_localize_script($handle, 'lbwpsOptions', $translation_array);
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
     * Get available image sizes, ordered by size
     */
    protected function getWPImageSizes()
    {
        $imageSizes = [];
        $additionalSizes = wp_get_additional_image_sizes();
        $intermediateSizes = get_intermediate_image_sizes();
        foreach($intermediateSizes as $intermediateSize) {
            if (in_array($intermediateSize, ['thumbnail', 'medium', 'large'])) {
                if ((bool)get_option($intermediateSize.'_crop') === false) {
                    $imageSizes[$intermediateSize] = [
                        'width' => get_option($intermediateSize.'_size_w'),
                        'height' => get_option($intermediateSize.'_size_h'),
                    ];
                }
            } elseif (isset($additionalSizes[$intermediateSize])) {
                if ($additionalSizes[$intermediateSize]['crop'] === false) {
                    $imageSizes[$intermediateSize] = [
                        'width' => $additionalSizes[$intermediateSize]['width'],
                        'height' => $additionalSizes[$intermediateSize]['height'],
                    ];
                }
            }
        }

        usort($imageSizes, function($a, $b) {
            $totalA = $a['width'] * $a['height'];
            $totalB = $b['width'] * $b['height'];
            if ($totalA > $totalB) return 1;
            if ($totalA < $totalB) return -1;

            return 0;
        });

        return $imageSizes;
    }

    /**
     * Clean up Twig cache
     */
    protected function cleanupTwigCache()
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
