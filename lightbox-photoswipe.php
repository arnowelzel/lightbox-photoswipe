<?php
/*
Plugin Name: Lightbox with PhotoSwipe
Plugin URI: https://wordpress.org/plugins/lightbox-photoswipe/
Description: Lightbox with PhotoSwipe
Version: 1.51
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
	const LIGHTBOX_PHOTOSWIPE_VERSION = '1.51';
	var $disabled_post_ids;
	var $share_facebook;
	var $share_pinterest;
	var $share_twitter;
	var $share_download;
	var $close_on_scroll;
	var $close_on_drag;
	var $history;
	var $show_counter;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->disabled_post_ids = explode(',', get_option('lightbox_photoswipe_disabled_post_ids'));
		$this->share_facebook = get_option('lightbox_photoswipe_share_facebook');
		$this->share_pinterest = get_option('lightbox_photoswipe_share_pinterest');
		$this->share_twitter = get_option('lightbox_photoswipe_share_twitter');
		$this->share_download = get_option('lightbox_photoswipe_share_download');
		$this->close_on_scroll = get_option('lightbox_photoswipe_close_on_scroll');
		$this->close_on_drag = get_option('lightbox_photoswipe_close_on_drag');
		$this->history = get_option('lightbox_photoswipe_history');
		$this->show_counter = get_option('lightbox_photoswipe_show_counter');
		
		if(!is_admin()) {
			add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
			add_action('wp_footer', array($this, 'footer'));
			add_action('template_redirect', array($this, 'output_filter'), 99);
		}
		add_action('wpmu_new_blog', array($this, 'on_create_blog'), 10, 6);
		add_filter('wpmu_drop_tables', array($this, 'on_delete_blog'));
		add_action('plugins_loaded', array($this, 'init'));
		add_action('admin_menu', array($this, 'admin_menu'));
	}
	
	/**
	 * Scripts/CSS
	 */
	function enqueue_scripts() {
		if(!is_404() && in_array(get_the_ID(), $this->disabled_post_ids)) return;
		
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
		$translation_array = array(
			'label_facebook' => __( 'Share on Facebook', 'lightbox-photoswipe' ),
			'label_twitter' => __( 'Tweet', 'lightbox-photoswipe' ),
			'label_pinterest' => __( 'Pin it', 'lightbox-photoswipe' ),
			'label_download' => __( 'Download image', 'lightbox-photoswipe' )
		);
		$translation_array['share_facebook'] = ($this->share_facebook == '1')?'1':'0';
		$translation_array['share_twitter'] = ($this->share_twitter == '1')?'1':'0';
		$translation_array['share_pinterest'] = ($this->share_pinterest == '1')?'1':'0';
		$translation_array['share_download'] = ($this->share_download == '1')?'1':'0';
		$translation_array['close_on_scroll'] = ($this->close_on_scroll != '1')?'1':'0';
		$translation_array['close_on_drag'] = ($this->close_on_drag != '1')?'1':'0';
		$translation_array['history'] = ($this->history == '1')?'1':'0';
		$translation_array['show_counter'] = ($this->show_counter == '1')?'1':'0';
		wp_localize_script( 'photoswipe', 'lightbox_photoswipe', $translation_array);
		
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
		if(!is_404() && in_array(get_the_ID(), $this->disabled_post_ids)) return;
		
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
			'/(<a.[^>]*href=["\'])(.[^"]*?)(["\'])(.[^>]*)(><img )/s',
			array(get_class($this), 'output_callback'),
			$content);
		$content = preg_replace_callback(
			'/(<a.[^>]*href=["\'])(.[^"]*?)(["\'])(><img )/s',
			array(get_class($this), 'output_callback'),
			$content);
		return $content;
	}
	
	function output_filter( $content ) {
		if(!is_404() && in_array(get_the_ID(), $this->disabled_post_ids)) return;
		
		ob_start(array(get_class($this), 'output'));
	}

	/**
	 * Handling of settings
	 */
	function admin_menu() {
		add_options_page(__('Lightbox with PhotoSwipe', 'lightbox-photoswipe'), __('Lightbox with PhotoSwipe', 'lightbox-photoswipe'),
			'administrator', 'lightbox-photoswipe', array($this, 'settings_page') , plugins_url('/images/icon.png', __FILE__) );

		add_action( 'admin_init', array($this, 'register_settings') );
	}

	function register_settings() {
		register_setting( 'lighbox-photoswipe-settings-group', 'lightbox_photoswipe_disabled_post_ids' );
		register_setting( 'lighbox-photoswipe-settings-group', 'lightbox_photoswipe_share_facebook' );
		register_setting( 'lighbox-photoswipe-settings-group', 'lightbox_photoswipe_share_twitter' );
		register_setting( 'lighbox-photoswipe-settings-group', 'lightbox_photoswipe_share_pinterest' );
		register_setting( 'lighbox-photoswipe-settings-group', 'lightbox_photoswipe_share_download' );
		register_setting( 'lighbox-photoswipe-settings-group', 'lightbox_photoswipe_close_on_scroll' );
		register_setting( 'lighbox-photoswipe-settings-group', 'lightbox_photoswipe_close_on_drag' );
		register_setting( 'lighbox-photoswipe-settings-group', 'lightbox_photoswipe_history' );
		register_setting( 'lighbox-photoswipe-settings-group', 'lightbox_photoswipe_show_counter' );
	}

	function settings_page() {
		echo '<div class="wrap"><h1>' . __('Lightbox with PhotoSwipe', 'lightbox-photoswipe') . '</h1><form method="post" action="options.php">';
		settings_fields( 'lighbox-photoswipe-settings-group' );
		// do_settings_sections( 'lighbox-photoswipe-settings-group' );
		echo '<table class="form-table"><tr>
			<th scope="row"><label for="lightbox_photoswipe_disabled_post_ids">'.__('Excluded pages/posts', 'lightbox-photoswipe').'</label></th>
			<td><input id="lightbox_photoswipe_disabled_post_ids" class="regular-text" type="text" name="lightbox_photoswipe_disabled_post_ids" value="' . esc_attr(get_option('lightbox_photoswipe_disabled_post_ids')) . '" /><p class="description">'.__('Enter a comma separated list with the IDs of the pages/posts where the lightbox should not be used.', 'lightbox-photoswipe').'</p></td>
			</tr>
			<tr>
			<th scope="row">'.__('Visible sharing options', 'lightbox-photoswipe').'</th>
			<td>
			<label for="lightbox_photoswipe_share_facebook"><input id="lightbox_photoswipe_share_facebook" type="checkbox" name="lightbox_photoswipe_share_facebook" value="1"'; if(get_option('lightbox_photoswipe_share_facebook')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Share on Facebook', 'lightbox-photoswipe').'</label><br />
			<label for="lightbox_photoswipe_share_twitter"><input id="lightbox_photoswipe_share_twitter" type="checkbox" name="lightbox_photoswipe_share_twitter" value="1" '; if(get_option('lightbox_photoswipe_share_twitter')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Tweet', 'lightbox-photoswipe').'</label><br />
			<label for="lightbox_photoswipe_share_pinterest"><input id="lightbox_photoswipe_share_pinterest" type="checkbox" name="lightbox_photoswipe_share_pinterest" value="1" '; if(get_option('lightbox_photoswipe_share_pinterest')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Pin it', 'lightbox-photoswipe').'</label><br />
			<label for="lightbox_photoswipe_share_download"><input id="lightbox_photoswipe_share_download" type="checkbox" name="lightbox_photoswipe_share_download" value="1"'; if(get_option('lightbox_photoswipe_share_download')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Download image', 'lightbox-photoswipe').'</label>
			</td></tr>
			<tr>
			<th scope="row">'.__('Other options', 'lightbox-photoswipe').'</th>
			<td>
			<label for="lightbox_photoswipe_close_on_scroll"><input id="lightbox_photoswipe_close_on_scroll" type="checkbox" name="lightbox_photoswipe_close_on_scroll" value="1"'; if(get_option('lightbox_photoswipe_close_on_scroll')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Close when scrolling in desktop view', 'lightbox-photoswipe').'</label><br />
			<label for="lightbox_photoswipe_close_on_drag"><input id="lightbox_photoswipe_close_on_drag" type="checkbox" name="lightbox_photoswipe_close_on_drag" value="1"'; if(get_option('lightbox_photoswipe_close_on_drag')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Close with vertical drag in mobile view', 'lightbox-photoswipe').'</label><br />
			<label for="lightbox_photoswipe_history"><input id="lightbox_photoswipe_history" type="checkbox" name="lightbox_photoswipe_history" value="1"'; if(get_option('lightbox_photoswipe_history')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Activate browser history', 'lightbox-photoswipe').'</label><br />
			<label for="lightbox_photoswipe_show_counter"><input id="lightbox_photoswipe_show_counter" type="checkbox" name="lightbox_photoswipe_show_counter" value="1"'; if(get_option('lightbox_photoswipe_show_counter')=='1') echo ' checked="checked"'; echo ' />&nbsp;'.__('Show picture counter', 'lightbox-photoswipe').'</label>
			</td></tr>
			</table>';
		submit_button();
		echo '</form></div>';
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
			update_option('lightbox_photoswipe_share_facebook', '1');
			update_option('lightbox_photoswipe_share_pinterest', '1');
			update_option('lightbox_photoswipe_share_twitter', '1');
			update_option('lightbox_photoswipe_share_download', '1');
			update_option('lightbox_photoswipe_close_on_scroll', '1');
			update_option('lightbox_photoswipe_close_on_drag', '1');
			update_option('lightbox_photoswipe_show_counter', '1');
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
		
		if($db_version == '' || intval($db_version) < 2) {
			$this->delete_tables();
			$this->create_tables();
		}
		else if(intval($db_version) < 3) {
			update_option('lightbox_photoswipe_disabled_post_ids', get_option('disabled_post_ids'));
			delete_option('disabled_post_ids');
			update_option('lightbox_photoswipe_share_facebook', '1');
			update_option('lightbox_photoswipe_share_pinterest', '1');
			update_option('lightbox_photoswipe_share_twitter', '1');
			update_option('lightbox_photoswipe_share_download', '1');
		}
		else if(intval($db_version) < 4) {
			update_option('lightbox_photoswipe_close_on_scroll', '1');
			update_option('lightbox_photoswipe_close_on_drag', '1');
			update_option('lightbox_photoswipe_show_counter', '1');
		}
		
		update_option('lightbox_photoswipe_db_version', 4);
	}
}

$lightbox_photoswipe = new LightboxPhotoSwipe();
