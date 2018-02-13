=== Lightbox with PhotoSwipe ===

Contributors: awelzel
Tags: attachments, images, gallery, lightbox, fancybox, photoswipe
Requires at least: 4.0
Tested up to: 4.9
Stable tag: 1.9
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integration of PhotoSwipe (http://photoswipe.com) for WordPress.

== Description ==

This plugins is a simple integration of PhotoSwipe to WordPress. All linked images in a post or page can be displayed using PhotoSwipe, regardless if they are part of a gallery or single images.

More about PhotoSwipe see here: <http://photoswipe.com>

Source of PhotoSwipe see here: <https://github.com/dimsemenov/PhotoSwipe>

== Installation ==

1. Extract the contents of the package to the `/wp-content/plugins/lightbox-photoswipe` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Licensing =

To avoid any confusion: this plugin was published with the agreement of Dmitry Semenov.

== Changelog ==

= 1.9 =

Modified CSS rules to make sure that the lightbox is not covered by other elements.

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
