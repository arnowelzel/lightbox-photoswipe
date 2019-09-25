=== Lightbox with PhotoSwipe ===

Contributors: awelzel
Tags: attachments, images, gallery, lightbox, fancybox, photoswipe
Requires at least: 4.0
Tested up to: 5.2
Stable tag: 2.8
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integration of PhotoSwipe (http://photoswipe.com) for WordPress.

== Description ==

This plugin is a simple integration of PhotoSwipe to WordPress. All linked images in a post or page will be displayed using PhotoSwipe, regardless if they are part of a gallery or single images. Just make sure that you link the image or gallery directly to the media and not the attachment page (in galleries the option `link=file` should be set).

More about PhotoSwipe see here: [http://photoswipe.com](http://photoswipe.com)

The version of PhotoSwipe provided with this plugin comes with a number of modifications and extensions. See the FAQ for details.

== Installation ==

1. Extract the contents of the package to the `/wp-content/plugins/lightbox-photoswipe` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Using the plugin =

All linked images in a post or page will be displayed using PhotoSwipe, regardless if they are part of a gallery or single images.

Make sure that you link the image or gallery directly to the media and not the attachment page (in galleries the option `link=file` should be set).

= Experimental feature: return to a specific URL when closing the lightbox =

Note: this was changed with version 2.0. The previous parameter `return` is no longer supported.

When you activate the setting for "Activate browser history" you can link directly to an image inside a page or post:

`http://domain.example/example-page#gid=1&pid=1`

This will load the given page/post and automatically open the first image (`pid=1`) in the lightbox. However, when closing the lightbox, you will see the page or post itself. Sometimes it is preferred to go to a specific URL when closing the lightbox. This can be done by using `returnurl` combined with the URL to got to as the first parameter:

`http://domain.example/example-page#returnurl=http://domain.example&gid=1&pid=1`

When a visitor now opens the link, closing the lightbox will get the visitor to specified URL `http://domain.example`.

= How to disable the plugin in certain pages/posts =

Please note: the order of the parameters have changed in version 1.90.

Some other plugins use PhotoSwipe as well and there may be a conflict - for example with the product pages of WooCommerce.

You can either configure the pages/posts manually in the settings or you can use the filter `lbwps_enabled`. This filter gets the ID of the current page/post and if the lightbox is currently enabled (`true` or `false`). If the filter returns `true`, the lightbox will be used, if it returns `false` the lightbox will be disabled - which means, no scripts and stylesheets will be included at all on the current page/post.

Example:

`
// Disable Lightbox with PhotoSwipe on WooCommerce product pages

function my_lbwps_enabled($enabled, $id)
{
    if (function_exists('is_product')) {
        if (is_product()) return false;
    }

    return $enabled;
}

add_filter('lbwps_enabled', 'my_lbwps_enabled', 10, 2);
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
        $markup
	);
    return $markup;
}

add_filter('lbwps_markup', 'my_lbwps_markup', 10, 1);`

= How to style the caption below the images =

If you want to style the caption below the images, you need to create custom styles for the following CSS classes:

pswp__caption__center - this class is used for the whole caption area.

pswp__caption__title and pswp__caption__desc - these classes are used, if the caption is divided into a title an description (based on the data-caption-title and data-caption-desc attributes in the image link).

pswp__caption__exif - this class is used for the EXIF data DIV element.

= Why is there no "zoom animation" when opening the lightbox? =

PhotoSwipe has the option to create a zoom animation from the thumbnail to the final image when opening the lightbox. However, this does not work well with square thumbnails since the thumbnail is just enlarged to the final image size without keeping its aspect ratio. This would result in a quite weird image display where a square thumbnail gets stretched to a portrait or landscape image before the final image is loaded. Just having a black background where the final image gets loaded seems to be the better solution. Also see [http://photoswipe.com/documentation/faq.html](http://photoswipe.com/documentation/faq.html) about this topic.

= Conflict with Advanced Gutenberg =

Lightbox with PhotoSwipe works fine with Gutenberg gallery blocks as well. However when you use the "Advanced Gutenberg" plugin it brings its own lightbox script which can cause conflicts. To avoid any problems, you should disable the Advanced Gutenberg lightbox in the settings. Disable the option "Open galleries in lightbox" in the backend configuration of Advanced Gutenberg.

= Local changes in PhotoSwipe =

The following changes are the differences to PhotoSwipe 4.0 as of 2019-09-24:

1) The default UI is based on a CSS file and a number of graphics in different formats. This CSS file got modified to provide a fix for WordPress themes which use elements with a quite high Z index which hide the controls of PhotoSwipe. By setting the Z index of the affected controls to the highest possible value, all controls stay visible in front.

2) There are four skins, which you can choose from. Every skin is based on the default UI with some modifications. "New share symbol" contains a modified "share" symbol while "solid" in the name indicates, that all controls have a solid background instead of a slight transparency.

3) When dragging the picture to the top, there is no additional fade out animation when closing the picture.

4) When looping is disabled, this also applies to the desktop view.

5) The grey placeholder for images when opening the lightbox is not visible (this was accomplished by adding `display: none;` for the placeholder).

6) Arrows for next and previous picture will be hidden for the first or last picture if no endless loop is activated.

7) When using full picture size in desktop view the UI elements will hide automatically and not only after a mouse movement and the image caption will also be hidden together with the navigation.

8) Full screen mode can also be activated by pressing the key "F" on the keyboard.

9) Gallery items support an optional "exif" property to display EXIF information in addition to the caption.

= Licensing =

To avoid any confusion: this plugin was published with the agreement of Dmitry Semenov.

== Screenshots ==

1. Configuration options in the backend
2. Example for the use in the frontend

== Changelog ==

= 2.7, 2.8 =

* Additional option to display EXIF information as caption.

= 2.6 =

* Full screen mode can now also be activated by pressing the key "F" on the keyboard.
* Set maximum possible priority for the output filter so it will be called at the latest possible moment.

= 2.5 =

* If images links contain attributes `data-caption-title` and `data-caption-desc` these attributes will be used as separate elements in the caption.

= 2.4 =

* Fixed a bug when using full picture size in desktop view.
* Endless loop is now also supported with only two images.
* Added an option to use the alternative text in the image as caption if needed.

= 2.3 =

* Clicking images will no longer close them.

= 2.2 =

* Added option to show pictures in full size in desktop view.

= 2.1 =

* Closing the lightbox by clicking the background enabled again and made configurable.

= 2.0 =

* The lightbox will not close any longer when clicking the background.
* Fix to avoid PHP notices because of using dynamic methods as static ones.
* Changed experimental feature "return on close" to "open URL on close".

= 1.99 =

* Modified "return on close" option to return to the previous URL without closing animation.
* Added option to select between image or lightbox URL when sharing on Facebook or Twitter.
* Added missing translations.

= 1.98 =

* Added backend option to enable or disable "tap to toggle UI controls" gesture on mobile devices.
* Added experimental support for "return on close" (see the description how to use this).
* Internal links without domain part (`/wp-content/...` instead of `http://domain.example/wp-content/...`) now also work.
* Code refactoring: frontend script is now called "js/frontend.js".
* Improved support for captions in Meow Gallery.

= 1.97 =

* Added WebP as supported image format.

= 1.96 =

* Fixed a bug when the size of an image could not be determined.

= 1.95 =

* Sharing on Facebook or Twitter will now use the image URL.

= 1.94 =

* PhotoSwipe with Lightbox will now also be included on error pages with status HTTP 404.

= 1.93 =

* Direct links to images using URL parameters `gid` and `pid` work again.

= 1.92 =

* Added support for reading caption from figcaption (thanks to Maciej Majewski contributing this feature).
* Added support for captions in Gutenberg gallery blocks.
* Fixed database upgrade behaviour for new installations to make sure all defaults and cleanup job are set properly.
* When deleting the plugin in the backend, plugin options will now also get removed from the WordPress database.

= 1.91 =

* Fixed some CSS issues with some themes which could cause the lightbox buttons not to show properly.

= 1.90 =

* Fixed wrong order in `lbwps_enabled` filter.

= 1.84 =

* Added option to enable or disable the fullscreen button in PhotoSwipe (thanks to Thomas Biering contributing this feature).

= 1.83 =

* Removed visible grey placeholder when opening the lightbox.

= 1.82 =

* Code refactoring

= 1.81 =

* Improved handling of linked images in certain galleries.

= 1.80 =

* Added support for remote images outside the domain of the website.
* Added scheduled internal cleanup of cached image sizes.

= 1.74 =

* Fixed potential performance issue and improved handling of linked images with line breaks or spaces/tabs between link and image tag.

= 1.73 =

* Fixed non working option for "allow infinite loop".

= 1.72 =

* Fixed non working option for "pinch to close".

= 1.71 =

* Reading captions from the database can be disabled.
* Captions from the database are now texturized to have proper curly quotes, dashes etc..
* Additional option to enable or disable pinch to close gesture.
* Additional option to enable or disable infinite loop.
* Change in PhotoSwipe: disabling loop option now also applies to the desktop view.

= 1.70 =

* Using client side captions from the gallery, if image meta data can not be loaded using its URL.

= 1.69 =

* Fixed supressing of lightbox UI if the plugin is disabled by a setting or the `lbwps_enabled` filter.

= 1.68 =

* Fixed supressing of script output if the plugin is disabled by a setting or the `lbwps_enabled` filter.

= 1.67 =

* Fixed missing captions if images got added using HTTPS and served via HTTP or vice versa
* Improved handling of multilined captions

= 1.65 =

* Fixed caption handling: captions should now always be displayed if enabled

= 1.64 =

* Zoom button can be disabled
* Captions can be disabled
* Spacing between pictures can be adjusted
* Refactoring for better PSR compliance
* Fix in PhotoSwipe: images will now fade out, when closing them by dragging them vertically up or down.

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
