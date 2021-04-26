=== Lightbox with PhotoSwipe ===

Contributors: awelzel
Tags: attachments, images, gallery, lightbox, fancybox, photoswipe
Requires at least: 4.0
Tested up to: 5.7
Stable tag: 3.1.12
Donate link: https://paypal.me/ArnoWelzel
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integration of PhotoSwipe (http://photoswipe.com) for WordPress.

== Description ==

This plugin integrates an extended version of PhotoSwipe to WordPress. All linked images in a post or page will be displayed using PhotoSwipe, regardless if they are part of a gallery or single images. Just make sure that you link the image or gallery directly to the media and not the attachment page (in galleries the option `link=file` should be set).

More about the original version of PhotoSwipe see here: [http://photoswipe.com](http://photoswipe.com)

The version of PhotoSwipe provided with this plugin comes with a number of modifications and extensions. See the FAQ for details.

== Installation ==

1. Extract the contents of the package to the `/wp-content/plugins/lightbox-photoswipe` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Using the plugin =

All linked images in a post or page will be displayed using PhotoSwipe, regardless if they are part of a gallery or single images.

Make sure that you link the image or gallery directly to the media and not the attachment page (in galleries the option `link=file` should be set).

If you want to display an image in it's own lightbox which does not display other images from the same post or page, you can add the attribute `data-lbwps-gid` to the link element with a unique value for this image. This value must not be a number since numbers are already used internally. For example you could the file name of the image like this:

`<a href="myimage.jpg" data-lbwps-gid="myimage.jpg"><img src="myimage-300x300.jpg" alt="My Image" /></a>`

You can also add the same `data-lbwps-gid` attribute to multiple single images to combine them in the same lightbox.

Note: the parameter was renamed from `data-gallery-id` to `data-lbwps-gid` in version 2.97 to avoid conflicts with existing themes or plugins!

= Experimental feature: return to a specific URL when closing the lightbox =

Note: this was changed with version 2.0. The previous parameter `return` is no longer supported.

When you activate the setting for "Activate browser history" you can link directly to an image inside a page or post:

`http://domain.example/example-page#gid=1&pid=1`

This will load the given page/post and automatically open the first image (`pid=1`) in the lightbox. However, when closing the lightbox, you will see the page or post itself. Sometimes it is preferred to go to a specific URL when closing the lightbox. This can be done by using `returnurl` combined with the URL to got to as the first parameter:

`http://domain.example/example-page#returnurl=http://domain.example&gid=1&pid=1`

When a visitor now opens the link, closing the lightbox will get the visitor to specified URL `http://domain.example`.

= The plugin seems not to work properly =

Some themes or plugins have their own lightbox implementation which can cause a conflict with Lightbox with PhotoSwipe. In this case you need to disable the lightbox of the theme or other plugins if possible.

Also keep in mind that linked images which are added using JavaScript after the page has been loaded already by the browser will not be displayed with Lightbox with PhotoSwipe if they are missing the attributes `data-lbwps-width` and `data-lbwps-height` with the width and height of the image.

= How to disable the plugin in certain pages/posts =

Please note: the order of the parameters have changed in version 1.90.

Some other plugins use PhotoSwipe as well and it may be neccessary to disable Lightbox with PhotoSwipe on some pages or posts - for example on the product pages of WooCommerce.

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

pswp__caption - this class is used for the whole caption area.

pswp__caption__center - this class is used for the caption itself.

pswp__caption__title and pswp__caption__desc - these classes are used, if the caption is divided into a title an description (based on the data-caption-title and data-caption-desc attributes in the image link).

pswp__description - this class is used for the description if this is displayed in addition to the regular caption.

pswp__caption__exif - this class is used for the EXIF data DIV element.

pswp__caption__exif_focal, pswp__caption__exif_fstop, pswp__caption__exif_shutter, pswp__caption__exif_iso, pswp__caption__exif_datetime - these classes are used for the individual properties in the EXIF data.

= Why is there no "zoom animation" when opening the lightbox? =

PhotoSwipe has the option to create a zoom animation from the thumbnail to the final image when opening the lightbox. However, this does not work well with square thumbnails since the thumbnail is just enlarged to the final image size without keeping its aspect ratio. This would result in a quite weird image display where a square thumbnail gets stretched to a portrait or landscape image before the final image is loaded. Just having a black background where the final image gets loaded seems to be the better solution. Also see [http://photoswipe.com/documentation/faq.html](http://photoswipe.com/documentation/faq.html) about this topic.

= Conflict with PublishPress Blocks (Advanced Gutenberg Blocks) =

Lightbox with PhotoSwipe works fine with Gutenberg gallery blocks as well. However when you use the "PublishPress Blocks" plugin it brings its own lightbox script which can cause conflicts. To avoid any problems, you should disable the Advanced Gutenberg lightbox in the settings. Disable the option "Open galleries in lightbox" in the backend configuration of PublishPress Blocks.

= How to use the PhotoSwipe API? =

The PhotoSwipe instance for the gallery is available as `window.lbwpsPhotoSwipe` after the gallery was initialized. Please note, that this variable is `null` if the lightbox is not open! This can be used to build your own extensions using the PhotoSwipe API. Also see [https://photoswipe.com/documentation/api.html](https://photoswipe.com/documentation/api.html) how to use the API.

= How to change the order of the images in the lightbox? =

If you want to display the images not in the order in which they are in the source code you can use the attribute `tabindex` in the image links. Also see [https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/tabindex](https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/tabindex) on how to use this attribute.

= Local changes in PhotoSwipe =

The following changes are the differences to PhotoSwipe 4.0 as of 2020-04-14:

1) The default UI is based on a CSS file and a number of graphics in different formats. This CSS file got modified to provide a fix for WordPress themes which use elements with a quite high Z index which hide the controls of PhotoSwipe. By setting the Z index of the affected controls to the highest possible value, all controls stay visible in front.

2) There are four skins, which you can choose from. Every skin is based on the default UI with some modifications. "New share symbol" contains a modified "share" symbol while "solid" in the name indicates, that all controls have a solid background instead of a slight transparency.

3) When dragging the picture to the top, there is no additional fade out animation when closing the picture.

4) When looping is disabled, this also applies to the desktop view.

5) The grey placeholder for images when opening the lightbox is not visible (this was accomplished by adding `display: none;` for the placeholder).

6) Arrows for next and previous picture will be hidden for the first or last picture if no endless loop is activated.

7) When using full picture size in desktop view the UI elements will hide automatically and not only after a mouse movement and the image caption will also be hidden together with the navigation.

8) Full screen mode can also be activated by pressing the key "F" on the keyboard.

9) Gallery items support an optional "exif" property to display EXIF information in addition to the caption.

10) Infinite loop is also possible with only two images.

11) Added slide animation for changing images using the arrow buttons or via keyboard as suggested in [https://github.com/dimsemenov/PhotoSwipe/pull/1179](https://github.com/dimsemenov/PhotoSwipe/pull/1179).

12) Added options to use the mouse wheel for zooming or switching images in desktop view.

13) Added option to add "onclick" handlers for sharing links.

14) Added option to add a custom URL for sharing links.

15) Fullscreen mode hides navigation buttons on mobile devices if possible.

= Licensing =

To avoid any confusion: this plugin was published with the agreement of Dmitry Semenov.

== Screenshots ==

1. General options in the backend
2. Theme options in the backend
3. Options for captions in the backend
4. Sharing options in the backend
5. Desktop options in the backend
6. Mobile options in the backend
7. Example for the use in the frontend

== Changelog ==

= 3.1.12 =

* Added a workaround to avoid warnings when image sizes can not be determined properly.

= 3.1.11 =

* Added translation for "German formal".
* Fullscreen view hides navigation on mobile devices if possible.

= 3.1.10 =

* Fixed opening images in lightbox directly via URL.

= 3.1.9 =

* Fixed an issue with multiple clickable links to the same image which got introduced with version 3.0.7.

= 3.1.8 =

* Uppercase image extension like JPG instead of jpg are no longer ignored.

= 3.1.7 =

* Images which are not located in the WordPress upload folder work again (this was a bug introduced by the Flywheel fix)

= 3.1.6 =

* Removed use of ABSPATH to determine the path of image files to avoid problems with sites hosted by Flywheel
* Fixed filter handling

= 3.1.5 =

* Hiding of scrollbars can now be turned off in case it does not work properly with theme of the site

= 3.1.4 =

* Added support for SCRIPT_DEBUG (thanks to Hristo Hristov for the suggestion)
* Optimized frontend scripts to be only one minimized file and in the footer in production mode
* Optimized stylesheets to be minimized and only one file in production mode

= 3.1.3 =

* Scrollbars will be restored after lightbox has closed and not during closing.

= 3.1.2 =

* Hiding scrollbars of the document body when opening the lightbox.
* Removed alt attribute from images inside the lightbox since this is not really useful and may cause problems with captions which contain HTML.
* Fixed missing captions for images which got scaled or rotated with the WordPress image editor (thanks to Emmanuel Liron for the fix).

= 3.1.1 =

* Reverted internal code change which caused some images not to get detected any longer properly.

= 3.1.0 =

* Added detection for DOM changes so also galleries added via JavaScript should work
* Changed handling with relative URLs to avoid problems with Bedrock (thanks to Smeedijzer Internet for pointing this out).

= 3.0.8 =

* Fixed a bug which caused wrong sort order for links with `tabindex` (1, 2, 3, 10, 11 and not 1, 10, 11, 2, 3 etc.)

= 3.0.7 =

* Refactored naming of functions and variables
* Made PhotoSwipe gallery instance available globally as `window.lbwpsPhotoSwipe` for other plugins (thanks to Thomas Biering for the suggestion)
* Added support for relative image URLs
* Added support for `tabindex` attribute in image links
* Multiple links to the same image created by some "lazy loading" solutions will be ignored
* Native lazy loading will only be added to an image if the attribute is not set already

= 3.0.6 =

* New option to use the WordPress caching instead of a custom database table (thanks to B-e-n-G)
* New option to ignore links to images on external sites
* New option to ignore links to images which contain a hash
* New option to handle custom CDN URLs

= 3.0.5 =

* Captions are now used including HTML code.

= 3.0.4 =

* Added missing translation.
* Changed frontend initialization to be faster and more compatible.

= 3.0.3 =

* Fixed invalid HTML in the backend settings.

= 3.0.2 =

* Fixed a bug which caused the lightbox not to work when there are links to images without visible thumbnails inside.

= 3.0.1 =

* Fixed a PHP warning if the size of an image could not be determined.

= 3.0 =

* New backend interface with tabs.
* Added option to exclude by post type.
* The lightbox will no longer be disabled on the home page, archive pages or search results if it is disabled in one or more pages/posts.
* Updated frontend code to improve compatibility with older browsers.
* Fixed redundant database updates which might cause a performance issue.

= 2.100 =

* Fix in caption handling for images with captions referred using `aria-describedby` which was broken since 2.94.

= 2.99 =

* Fix in caption handling for images with `data-caption-title` and `data-caption-desc` attributes.

= 2.97 =

* Images will now show during the opening transition of the lightbox.
* Renamed data attributes to avoid conflicts with existing themes or plugins.

= 2.96 =

* Editor meta box can be disabled in the backend settings.

= 2.94 =

* Removed jQuery again after some code refactoring.
* Added editor meta box so you can disable the lightbox in pages/posts itself.

= 2.92 =

* Fix in database cleanup.

= 2.90 =

* Added more options for using the mouse wheel in desktop view: zoom and switching images.
* Added sharing options.
* Fixed captions when EXIF output is enabled and EXIF data is missing in an image.

= 2.81 =

* Fixed an issue with the database.

= 2.80 =

* Added optional display of EXIF date.
* Fixed another bug for adding lazy loading attributes for images.

= 2.77 =

* Adding of lazy loading turned off by default since this may cause problems with certain themes and plugins. You can enable it again manually in the backend settings, if you want to keep this feature.

= 2.76 =

* Fixed a bug for adding lazy loading attributes for images.

= 2.75 =

* Additional checks for output buffering.
* New option to configure the timeout before controls will hide automatically in desktop view.
* New option to add native lazy loading to images.
* Added support for image descriptions.

= 2.70 =

* Restored use of jQuery to fix compatibility issues with some themes and plugins.

= 2.66 =

* Fixed a bug in caption handling to use alt attributes if no other caption source is available.

= 2.65 =

* Change enqueue handle names for scripts to avoid compatibility issues with some themes.
* Refactored frontend script to remove jQuery.
* Added CSS rule to automatically rotate images based on EXIF data.

= 2.64 =

* Common caption for Gutenberg gallery blocks is now recognized as well.

= 2.63 =

* Slide animation for using the arrows in desktop view can be disabled.

= 2.62 =

* Additional changes to improve compatibility with Borlabs Cookie.

= 2.60, 2.61 =

* Added slide animation for changing images using the arrow buttons or keyboard.
* Fixed direct opening of images using gid/pid parameter in the URL.

= 2.51 =

* Changed buffer handling again to avoid problems with images created outside the main content.

= 2.50 =

* Use browser history modification by default (you can turn this off in the settings if you don't want this).
* Added a workaround for a bug in the CSS rule for buttons in Twenty Twenty to avoid the wrong background color for UI elements.
* Added option to display WordPress galleries and Gutenberg gallery blocks in separate lightboxes.
* Changed handling of output buffering to avoid potential problems with CDNs and caching plugins.

= 2.13 =

* Fixed handling of images with URL parameters.

= 2.12 =

* Fixed WordPress 5.3 compatibility in backend.

= 2.10, 2.11 =

* Fixed incorrect handling of external images which are not served by the website itself.
* Better error handling if EXIF data can not be read.

= 2.9 =

* Fix for a bug when displaying only two images on a page and the second image is opened first.

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
