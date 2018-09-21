=== Lightbox with PhotoSwipe ===

Contributors: awelzel
Tags: attachments, images, gallery, lightbox, fancybox, photoswipe
Requires at least: 4.0
Tested up to: 4.9
Stable tag: 1.63
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integration of PhotoSwipe (http://photoswipe.com) for WordPress.

== Description ==

This plugins is a simple integration of PhotoSwipe to WordPress. All linked images in a post or page will be displayed using PhotoSwipe, regardless if they are part of a gallery or single images. Just make sure that you link the image or gallery directly to the media and not the attachment page (in galleries the option `link=file` should be set).

More about PhotoSwipe see here: <http://photoswipe.com>

== Installation ==

1. Extract the contents of the package to the `/wp-content/plugins/lightbox-photoswipe` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Using the plugin =

All linked images in a post or page will be displayed using PhotoSwipe, regardless if they are part of a gallery or single images.

Make sure that you link the image or gallery directly to the media and not the attachment page (in galleries the option `link=file` should be set).

= How to disable the plugin in certain pages/posts =

Some other plugins use PhotoSwipe as well and there may be a conflict - for example with the product pages of WooCommerce.

You can either configure the pages/posts manually in the settings or you can use the filter `lbwps_enabled`. This filter gets the ID of the current page/post and if the lightbox is currently enabled (`true` or `false`). If the filter returns `true`, the lightbox will be used, if it returns `false` the lightbox will be disabled - which means, no scripts and stylesheets will be included at all on the current page/post.

Example:

`
// Disable Lightbox with PhotoSwipe on WooCommerce product pages

function my_lbwps_enabled($id, $enabled)
{
	if( function_exists( 'is_product' ) )
	{
		if( is_product() ) return false;
	}

	return $enabled;
}

add_filter( 'lbwps_enabled', 'my_lbwps_enabled', 10, 2 );
`

= How to modify the PhotoSwipe markup =

If you want to modify the existing PhotoSwipe markup, you can use the filter `lbwps_markup`. This filter gets one parameter with the existing markup and must return the modified markup to be used.

A "quick & dirty" example to add additional stuff in the header with the controls (CSS should never be inline - this is just to get a working example):

`function my_lbwps_markup($markup)
{
	// Add some additional elements
	$markup = str_replace(
		'<div class="pswp__top-bar">',
		'<div class="pswp__top-bar"><div style="position:absolute; width:100%; text-align:center; line-height:44px; font-size:13px; color:#fff; opacity: 0.75;">Our content</div>',
		$markup );
	return $markup;
}

add_filter( 'lbwps_markup', 'my_lbwps_markup', 10, 1 );`

= Local changes in PhotoSwipe =

The following changes are the differences to PhotoSwipe 4.0 as of 2018-09-19.

1) The default UI is based on a CSS file and a number of graphics in different formats. This CSS file got modified to provide a fix for WordPress themes which use elements with a quite high Z index which hide the controls of PhotoSwipe. By setting the Z index of the affected controls to the highest possible value, all controls stay visible in front.

2) There are four skins, which you can choose from. Every skin is based on the default UI with some modifications. "New" contains a modified "share" symbol while "solid" in the name indicates, that all controls have a solid background instead of a slight transparency.

3) When dragging the picture to the top, there is no additional fade out animation when closing the picture.

= Licensing =

To avoid any confusion: this plugin was published with the agreement of Dmitry Semenov.

== Screenshots ==

1. Configuration options in the backend
2. Example for the use in the frontend

== Changelog ==

= 1.64 =

* Refactoring for better PSR compliance

= 1.63 =

* Fixed missing captions in lightbox for "Cleaner Gallery".
* Added documentation about the local changes in PhotoSwipe

= 1.61 =

* Added filter to modify the PhotoSwipe markup, if needed.

= 1.60 =

* Added selectable skins and new "share" symbol in PhotoSwipe.
* Added filter for disabling the lightbox if needed.

= 1.52 =

* Fixed an issue with opening images using URL parameters.

= 1.51 =

* Improved handling of browser history: URLs which refer to specific images will open the lightbox as well.
* Some frontend code refactoring.

= 1.50 =

* Added more customization options for PhotoSwipe.

= 1.40 =

* Fixed an issue with "will-change" CSS hints.
* Fixed a potential issue with internal options names.
* Renamed JavaScript object which is used by WordPress to pass translated labels in the frontend from `object_name` to `lightbox_photoswipe`.
* Sharing options can now be configured.
* Layout modifications for the sharing menu.

= 1.30 =

* Added "share" button in frontend.

= 1.20 =

* Added a setting in the backend to exclude the lightbox on certain pages or posts.

= 1.14 =

* Fixed an issue with additional attributes in the surrounding anchor element of pictures (thanks to user conducivedata for the suggestion).

= 1.13 =

* Fixed a problem which may occur when activating the plugin after an older version already had been in use.

= 1.11 =

* Fix in PhotoSwipe: when closing an image by zooming out, the image will not be displayed once again to fade out.

= 1.10 =

* Fixed problems with Firefox for Android which needs `button` elements to handle the UI properly.

= 1.9 =

* Modified CSS rules to make sure that the lightbox is not covered by other elements.

= 1.8 =

* Change UI elements from `button` to `div` to avoid layout problems with certain themes (Hamilton, Oria).

= 1.7 =

* Fix in PhotoSwipe: when closing an image by a vertical drag, the image was displayed again once to fade out, even though it was already moved out of the view. Now the image will just be closed and not be faded out after dragging it up or down.

= 1.6 =

* Added workaround for pictures served by Jetpack Photon.
* Code refactoring.

= 1.5 =

* Changed multisite handling.

= 1.4 = 

* Fixed an upgrade/installation issue.

= 1.3 = 

* Fixed an upgrade issue.

= 1.2 =

* Fixed a database issue.

= 1.1 =

* Added missing text-domain header for proper localization support.

= 1.0 =

* Initial release.
