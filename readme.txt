=== Lightbox with PhotoSwipe ===

Contributors: awelzel
Tags: attachments, images, gallery, lightbox, fancybox, photoswipe
Requires at least: 4.0
Tested up to: 4.9
Stable tag: 1.60
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

= Licensing =

To avoid any confusion: this plugin was published with the agreement of Dmitry Semenov.

== Screenshots ==

1. Configuration options in the backend
2. Example for the use in the frontend

== Changelog ==

= 1.60 =

* Added selectable skins.

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
