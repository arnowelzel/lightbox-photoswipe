<?php
/*
Plugin Name: Lightbox with PhotoSwipe
Plugin URI: https://wordpress.org/plugins/lightbox-photoswipe/
Description: Lightbox with PhotoSwipe
Version: 1.6
Author: Arno Welzel
Author URI: http://arnowelzel.de
Text Domain: lightbox-photoswipe
*/

defined('ABSPATH') or die();

/**
 * Lightbox with PhotoSwipe
 * 
 * @package lightbox-photoswipe
 */
class LightboxPhotoSwipe {
	const LIGHTBOX_PHOTOSWIPE_VERSION = '1.6';

	/**
	 * Constructor
	 */
	public function __construct() {
		if(!is_admin()) {
			add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
			add_action('wp_footer', array($this, 'footer'));
			add_action('template_redirect', array($this, 'output_filter'), 99);
		}
		add_action('wpmu_new_blog', array($this, 'on_create_blog'), 10, 6);
		add_filter('wpmu_drop_tables', array($this, 'on_delete_blog'));
		add_action('plugins_loaded', array($this, 'init'));
	}
	
	/**
	 * Scripts/CSS
	 */
	function enqueue_scripts() {
		wp_enqueue_script(
			'photoswipe-lib',
			plugin_dir_url( __FILE__ ) . 'lib/photoswipe.min.js',
			array(),
			self::LIGHTBOX_PHOTOSWIPE_VERSION
		);
		wp_enqueue_script(
			'photoswipe-ui-default',
			plugin_dir_url( __FILE__ ) . 'lib/photoswipe-ui-default.min.js',
			array('photoswipe-lib'),
			self::LIGHTBOX_PHOTOSWIPE_VERSION
		);

		wp_enqueue_script(
			'photoswipe',
			plugin_dir_url( __FILE__ ) . 'js/photoswipe.js',
			array('photoswipe-lib', 'photoswipe-ui-default', 'jquery'),
			self::LIGHTBOX_PHOTOSWIPE_VERSION
		);
		wp_enqueue_style(
			'photoswipe-lib',
			plugin_dir_url( __FILE__ ) . 'lib/photoswipe.css',
			false,
			self::LIGHTBOX_PHOTOSWIPE_VERSION
		);
		wp_enqueue_style(
			'photoswipe-default-skin',
			plugin_dir_url( __FILE__ ) . 'lib/default-skin/default-skin.css',
			false,
			self::LIGHTBOX_PHOTOSWIPE_VERSION
		);
	}

	/**
	 * Footer in frontend with PhotoSwipe UI
	 */
	function footer() {
		echo '<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
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
			<button class="pswp__button pswp__button--arrow--left" title="'.__('Previous (arrow left)', 'lightbox-photoswipe').'">
			</button>
			<button class="pswp__button pswp__button--arrow--right" title="'.__('Next (arrow right)', 'lightbox-photoswipe').'">
			</button>
			<div class="pswp__caption">
				<div class="pswp__caption__center"></div>
			</div>
		</div>
	</div>
</div>';
	}

	/**
	 * Callback to handle a single image
	 */
	function output_callback($matches) {
		global $wpdb;
		
		$attr = '';
		$baseurl_http = get_site_url(null, null, 'http');
		$baseurl_https = get_site_url(null, null, 'https');
		$file = $matches[2];
		
		// Workaround for pictures served by Jetpack Photon
		$file = preg_replace( '/(i[0-2]\.wp.com\/)/s' , '', $file);
		
		if(substr($file, 0, strlen($baseurl_http)) == $baseurl_http || substr($file, 0, strlen($baseurl_https)) == $baseurl_https) {
			$file = str_replace($baseurl_http.'/', '', $file);
			$file = str_replace($baseurl_https.'/', '', $file);
			$file = ABSPATH . $file;
			$type = wp_check_filetype($file);
			
			if(in_array($type['ext'], array('jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp', 'tif', 'tiff', 'ico')) && file_exists($file)) {
				$imgkey = md5($file) . '-'. filemtime($file);
				$table_img = $wpdb->prefix . 'lightbox_photoswipe_img';
				$entry = $wpdb->get_row("SELECT width, height FROM $table_img where imgkey='$imgkey'");
				if(null != $entry) {
					$imagesize[0] = $entry->width;
					$imagesize[1] = $entry->height;
				} else {
					$imagesize = getimagesize($file);
					$created = strftime('%Y-%m-%d %H:%M:%S');
					$sql = "INSERT INTO $table_img (imgkey, created, width, height) VALUES (\"$imgkey\", \"$created\", $imagesize[0], $imagesize[1])";
					$wpdb->query($sql);
				}
				$attr = ' data-width="'.$imagesize[0].'" data-height="'.$imagesize[1].'"';
			}
		}

		if(count($matches) == 6) {
			$result = $matches[1].$matches[2].$matches[3].$matches[4].$attr.$matches[5];
		} else {
			$result = $matches[1].$matches[2].$matches[3].$attr.$matches[4];
		}
		
		return $result;
	}

	/**
	 * Output filter
	 */
	function output($content) {
		$content = preg_replace_callback(
			'/(<a href=["\'])(.[^"]*?)(["\'])(.[^>]*)(><img )/s',
			array(get_class($this), 'output_callback'),
			$content);
		$content = preg_replace_callback(
			'/(<a href=["\'])(.[^"]*?)(["\'])(><img )/s',
			array(get_class($this), 'output_callback'),
			$content);
		return $content;
	}
	
	function output_filter( $content ) {
		ob_start(array(get_class($this), 'output'));
	}

	/**
	 * Create custom database tables
	 */
	function create_tables() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'lightbox_photoswipe_img'; 
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
		  imgkey char(64) DEFAULT '' NOT NULL,
		  created datetime,
		  width mediumint(7),
		  height mediumint(7),
		  PRIMARY KEY (imgkey),
		  INDEX idx_created (created)
		) $charset_collate;";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$wpdb->query($sql);
	}

	/**
	 * Delete custom database tables
	 */
	function delete_tables() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'lightbox_photoswipe_img'; 
		$sql = "DROP TABLE IF EXISTS $table_name";
		$wpdb->query($sql);
	}

	/**
	 * Handler for creating a new blog
	 */
	function on_create_blog($blog_id, $user_id, $domain, $path, $site_id, $meta) {
		if(is_plugin_active_for_network('lightbox-photoswipe/lightbox-photoswipe.php')) {
			switch_to_blog($blog_id);
			$this->create_tables();
			restore_current_blog();
		}
	}

	/**
	 * Filter for deleting a blog
	 */
	function on_delete_blog($tables) {
		global $wpdb;
		
		$tables[] = $wpdb->prefix . 'lightbox_photoswipe_img';
		
		return $tables;
	}

	/**
	 * Plugin initialization
	 */
	function init() {
		global $wpdb;

		load_plugin_textdomain('lightbox-photoswipe', false, 'lightbox-photoswipe/languages/');

		$db_version = get_option('lightbox_photoswipe_db_version');
		
		if($db_version == '')
		{
			$this->create_tables();
		}
		else if($db_version < '1.6') {
			$this->delete_tables();
			$this->create_tables();
		}
		
		update_option('lightbox_photoswipe_db_version', self::LIGHTBOX_PHOTOSWIPE_VERSION);
	}
}

$lightbox_photoswipe = new LightboxPhotoSwipe();
