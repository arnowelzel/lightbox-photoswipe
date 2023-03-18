import PhotoSwipeLightbox from './lib/photoswipe-lightbox.esm.min.js';
import PhotoSwipeDynamicCaption from './dynamic-caption/photoswipe-dynamic-caption-plugin.esm.min.js';
import PhotoSwipeAutoHideUI from './auto-hide-ui/photoswipe-auto-hide-ui.esm.min.js';
import PhotoSwipeFullscreen from './fullscreen/photoswipe-fullscreen.esm.min.js';

let lbwpsInit = function(domUpdate) {
    if (!domUpdate) {
        document.addEventListener('click', (event) => {
            // Backwards compatible solution for older browsers
            if (event.target.parentNode.getAttribute('data-lbwps-width')) {

                event.preventDefault();
                openPhotoSwipe(false, 0, event.target.parentNode, false, '');
                return;
            }

            // In case above did not work, try analyzing the path for the event
            let path = event.composedPath && event.composedPath();
            if (!path) {
                return;
            }
            let num = 0;
            while (num < path.length)
            {
                if (typeof path[num].getAttribute === 'function' && path[num].getAttribute('data-lbwps-width')) {
                    event.preventDefault();
                    openPhotoSwipe(false, 0, path[num], false, '');
                    return;
                }
                num++;
            }
        });
    }

    // Use group IDs of elementor image carousels for the image links inside
    //
    // We assume the following structure:
    //
    // <div class="elementor-widget-image-carousel ...">
    //   <div class="elementor-widget-container ...">
    //     <div class="elementor-image-carousel swiper-wrapper ...">
    //       <div class="swiper-slide ...">
    //         <a href="image-url">...</a>
    //       </div>
    //       <div class="swiper-slide ...">
    //         <a href="image-url">...</a>
    //       </div>
    //       <div class="swiper-slide ...">
    //         <a href="image-url">...</a>
    //       </div>
    //       ...
    //     </div>
    //   </div>
    // </div>
    //
    // Each carousel also contains one "swiper-slide-duplicate" div which is ignored as
    // this is only used to repeat the first image at the end

    let elementorCarouselWidgetList = document.querySelectorAll('div[class*="elementor-widget-image-carousel"]');
    for (let i = 0; i < elementorCarouselWidgetList.length; i++) {
        let widgetId = elementorCarouselWidgetList[i].getAttribute('data-lbwps-gid');
        if (widgetId != null) {
            if (elementorCarouselWidgetList[i].firstElementChild != null &&
                elementorCarouselWidgetList[i].firstElementChild.firstElementChild != null &&
                elementorCarouselWidgetList[i].firstElementChild.firstElementChild.firstElementChild != null &&
                elementorCarouselWidgetList[i].firstElementChild.firstElementChild.firstElementChild.firstElementChild != null) {
                let imageBlock = elementorCarouselWidgetList[i].firstElementChild.firstElementChild.firstElementChild.firstElementChild;
                while(imageBlock != null) {
                    if (imageBlock.classList.contains('swiper-slide') && !imageBlock.classList.contains('swiper-slide-duplicate')) {
                        let imageLink = imageBlock.firstElementChild;
                        if (imageLink != null && imageLink.nodeName === 'A' && imageLink.getAttribute('data-lbwps-gid') == null) {
                            imageLink.setAttribute('data-lbwps-gid', widgetId);
                        }
                    }
                    imageBlock = imageBlock.nextElementSibling;
                }
            }
        }
    }

    // Use group IDs of elementor image widgets for the image links inside
    //
    // We assume the following structure:
    //
    // <div class="elementor-widget-image ..." data-lbwbs-gid="...">
    //   <div class="elementor-widget-container">
    //     <a href="image-url">...</a>
    //   </div>
    // </div>

    let elementorImageWidgetList = document.querySelectorAll('div[class*="elementor-widget-image"]');
    for (let i = 0; i < elementorImageWidgetList.length; i++) {
        let widgetId = elementorImageWidgetList[i].getAttribute('data-lbwps-gid');
        if (widgetId != null) {
            if (elementorImageWidgetList[i].firstElementChild != null &&
                elementorImageWidgetList[i].firstElementChild.firstElementChild != null) {
                let imageLink = elementorImageWidgetList[i].firstElementChild.firstElementChild;
                if (imageLink != null && imageLink.nodeName == 'A' && imageLink.getAttribute('data-lbwps-gid') == null) {
                    imageLink.setAttribute('data-lbwps-gid', widgetId);
                }
            }
        }
    }

    let originalBodyPaddingRight = '';
    let originalBodyOverflow = '';

    let hideScrollbar = function () {
        const scrollbarWidth = window.innerWidth - document.body.offsetWidth;
        originalBodyPaddingRight = document.body.style.paddingRight;
        originalBodyOverflow = document.body.style.overflow;
        document.body.style.paddingRight = scrollbarWidth + 'px';
        document.body.style.overflow = 'hidden';
    };

    let showScrollbar = function () {
        document.body.style.paddingRight = originalBodyPaddingRight;
        document.body.style.overflow = originalBodyOverflow;
    };

    let parseThumbnailElements = function (link, id) {
        let elements,
            galleryItems = [],
            index;

        if (id == null || id == 1) {
            elements = document.querySelectorAll('a[data-lbwps-width]:not([data-lbwps-gid])');
        } else {
            elements = document.querySelectorAll('a[data-lbwps-width][data-lbwps-gid="' + id + '"]');
        }

        for (let i = 0; i < elements.length; i++) {
            let element = elements[i];

            // Only use image if it was not added already
            let useImage = true;
            let linkHref = element.getAttribute('href');
            for (let j = 0; j < galleryItems.length; j++) {
                if (galleryItems[j].src == linkHref) {
                    useImage = false;
                }
            }

            if (useImage) {
                let caption = null;
                let title = null;
                let tabindex = element.getAttribute('tabindex');

                if (tabindex == null) {
                    tabindex = 0;
                }

                caption = element.getAttribute('data-lbwps-caption');

                // Attribute "aria-describedby" in the <a> element contains the ID of another element with the caption
                if (caption == null && element.firstElementChild) {
                    let describedby = element.firstElementChild.getAttribute('aria-describedby');
                    if (describedby != null) {
                        let description = document.getElementById(describedby);
                        if (description != null) caption = description.innerHTML;
                    }
                }

                // Other variations
                if (caption == null) {
                    let nextElement = element.nextElementSibling;
                    let parentElement = element.parentElement.nextElementSibling;
                    let parentElement2 = element.parentElement.parentElement.nextElementSibling;
                    let parentElement3 = element.parentElement.parentElement.parentElement.nextElementSibling;

                    if (nextElement != null) {
                        if (nextElement.className === '.wp-caption-text') {
                            caption = nextElement.innerHTML;
                        } else if (nextElement && nextElement.nodeName === "FIGCAPTION") {
                            caption = nextElement.innerHTML;
                        }
                    } else if (parentElement != null) {
                        if (parentElement.className === '.wp-caption-text') {
                            caption = parentElement.innerHTML;
                        } else if (parentElement.className === '.gallery-caption') {
                            caption = parentElement.innerHTML;
                        } else if (parentElement.nextElementSibling && parentElement.nextElementSibling.nodeName === "FIGCAPTION") {
                            caption = parentElement.nextElementSibling.innerHTML;
                        }
                    } else if (parentElement2 && parentElement2.nodeName === "FIGCAPTION") {
                        caption = parentElement2.innerHTML;
                    } else if (parentElement3 && parentElement3.nodeName === "FIGCAPTION") {
                        // This variant is used by Gutenberg gallery blocks
                        caption = parentElement3.innerHTML;
                    }
                }

                if (caption == null) {
                    caption = element.getAttribute('title');
                }

                // Build complete caption based on selected elements
                title = '';

                if (element.getAttribute('data-lbwps-title') != null && element.getAttribute('data-lbwps-title') != '') {
                    title = title + '<div class="pswp__caption__title">' + element.getAttribute('data-lbwps-title') + '</div>';
                }

                if (lbwpsOptions.usecaption === '1' && caption != null && caption != '') {
                    title = title + '<div class="pswp__caption__text">' + caption + '</div>';
                }

                if (lbwpsOptions.use_alt === '1' && element.firstElementChild && element.firstElementChild.getAttribute('alt')) {
                    title = title + '<div class="pswp__caption__alt">' + element.firstElementChild.getAttribute('alt') + '</div>';
                }

                if (element.getAttribute('data-lbwps-description') != null) {
                    title = title + '<div class="pswp__caption__desc">' + element.getAttribute('data-lbwps-description') + '</div>';
                }

                let msrc = element.getAttribute('href');
                if (element.getAttribute('data-lbwps-srcsmall')) {
                    msrc = element.getAttribute('data-lbwps-srcsmall');
                }

                let exif = element.getAttribute('data-lbwps-exif');
                if (!exif) {
                    exif = '';
                }

                galleryItems.push({
                    src: element.getAttribute('href'),
                    msrc: msrc,
                    width: element.getAttribute('data-lbwps-width'),
                    height: element.getAttribute('data-lbwps-height'),
                    title: title,
                    exif: exif,
                    el: element,
                    tabindex: tabindex
                });
            }
        }

        // Sort items by tabindex
        galleryItems.sort(function (a, b) {
            let indexa = parseInt(a.tabindex);
            let indexb = parseInt(b.tabindex);
            if (indexa > indexb) {
                return 1;
            }
            if (indexa < indexb) {
                return -1;
            }
            return 0;
        });

        // Determine current selected item
        if (link != null) {
            for (let i = 0; i < galleryItems.length; i++) {
                if (galleryItems[i].el.getAttribute('href') === link.getAttribute('href')) {
                    index = i;
                }
            }
        }

        return [galleryItems, parseInt(index, 10)];
    };

    let photoswipeParseHash = function() {
        let hash = window.location.hash.substring(1), params = {};

        if(hash.length < 5) {
            return params;
        }

        let vars = hash.split('&');
        for (let i = 0; i < vars.length; i++) {
            if(!vars[i]) {
                continue;
            }
            let pair = vars[i].split('=');
            if(pair.length < 2) {
                continue;
            }
            params[pair[0]] = pair[1];
        }

        if(params.gid) {
            params.gid = parseInt(params.gid, 10);
        }

        return params;
    };

    let openPhotoSwipe = function(element_index, group_index, element, fromURL, returnToUrl) {
        let id = 1,
            items,
            index;

        if (element != null) {
            id = element.getAttribute('data-lbwps-gid');
        } else {
            id = group_index;
        }

        items = parseThumbnailElements(element, id);
        if(element_index === false) {
            index = items[1];
        } else {
            index = element_index;
        }
        items = items[0];

        const options = {
            dataSource: items,
            showHideAnimationType: 'fade',
            showAnimationDuration: 250,
            hideAnimationDuration: 250,
            closeTitle: lbwpsOptions.label_ui_close,
            zoomTitle: lbwpsOptions.label_ui_zoom,
            arrowPrevTitle: lbwpsOptions.label_ui_prev,
            arrowNextTitle: lbwpsOptions.label_ui_next,
            errorMsg: lbwpsOptions.label_ui_error,
            pswpModule: () => import('./lib/photoswipe.esm.min.js'),

            // Additional options (also see https://photoswipe.com/options/)
            spacing: lbwpsOptions.spacing/100,
            loop: lbwpsOptions.loop === '1',
            wheelToZoom: true,
            pinchToClose: lbwpsOptions.pinchtoclose === '1',
            closeOnVerticalDrag: lbwpsOptions.close_on_drag === '1',
            clickToCloseNonZoomable: true,
            bgOpacity: lbwpsOptions.bg_opacity / 100,
            padding: {
                top: parseInt(lbwpsOptions.padding_top),
                bottom: parseInt(lbwpsOptions.padding_bottom),
                left: parseInt(lbwpsOptions.padding_left),
                right: parseInt(lbwpsOptions.padding_right)
            }
        }

        // Not supported any longer by PhotoSwipe 5 itself
        // Maybe we add this in future versions

        /*
        options.counterEl = lbwpsOptions.show_counter === '1';
        options.closeOnScroll = lbwpsOptions.wheelmode === 'close';
        options.switchOnScroll = lbwpsOptions.wheelmode === 'switch';
        options.history = lbwpsOptions.history === '1';
        options.zoomEl = lbwpsOptions.show_zoom === '1';
        options.tapToToggleControls = lbwpsOptions.taptotoggle === '1';
        options.desktopSlider = lbwpsOptions.desktop_slider === '1';
        */

        const lightbox = new PhotoSwipeLightbox(options);
        lightbox.on('destroy', () => {
            const pswpElements = document.getElementsByClassName('pswp__scroll-wrap');
            if (lbwpsOptions.hide_scrollbars === '1') {
                showScrollbar();
            }
            if (element) {
                element.focus();
            }
        });

        // Add fullscreen button and keyboard shortcut for fullscreen mode
        if (lbwpsOptions.show_fullscreen === '1') {
            const fullscreenPlugin = new PhotoSwipeFullscreen(lightbox, {
                fullscreenTitle: lbwpsOptions.label_ui_fullscreen
            });
        }

        // Add captions with dynamic caption plugin
        if (lbwpsOptions.show_caption === '1') {
            const captionPlugin = new PhotoSwipeDynamicCaption(lightbox, {
                type: lbwpsOptions.caption_type,
                captionContent: (slide) => {
                    let caption = '';

                    if (slide.data.title && slide.data.title !== '') {
                        caption = caption + '<div class="pswp__caption">'
                            + slide.data.title
                            + '</div>';
                    }

                    if (slide.data.exif && slide.data.exif !== '') {
                        caption = caption + '<div class="pswp__caption__exif">'
                            + slide.data.exif
                            + '</div>';
                    }

                    return caption;
                },
                mobileLayoutBreakpoint: function() {
                    if (this.options.type !== 'overlay') {
                        return window.innerWidth < 600;
                    }
                }
            });
        }

        // Add automatic hide of controls and caption
        if (lbwpsOptions.idletime > 0) {
            const autoHideUI = new PhotoSwipeAutoHideUI(lightbox, {
                    idleTime: lbwpsOptions.idletime
                }
            );
        }

        lightbox.init();
        if (lbwpsOptions.hide_scrollbars === '1') {
            hideScrollbar();
        }
        lightbox.loadAndOpen(index);
    };


    window.lbwpsCopyToClipboard = function(str) {
        const el = document.createElement('textarea');
        el.value = str;
        el.setAttribute('readonly', '');
        el.style.position = 'absolute';
        el.style.left = '-9999px';
        document.body.appendChild(el);
        const selected =
            document.getSelection().rangeCount > 0
                ? document.getSelection().getRangeAt(0)
                : false;
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        if (selected) {
            document.getSelection().removeAllRanges();
            document.getSelection().addRange(selected);
        }
    }

    let hashData = photoswipeParseHash();
    if (hashData.pid && hashData.gid) {
        // If the URL provides picture and group ID click the given element
        // as opening the lightbox at this point won't work
        let elements;

        if (hashData.gid == 1) {
            elements = document.querySelectorAll('a[data-lbwps-width]:not([data-lbwps-gid])');
        } else {
            elements = document.querySelectorAll('a[data-lbwps-width][data-lbwps-gid="' + hashData.gid + '"]');
        }
        history.replaceState(null, null, ' ');
        elements[hashData.pid-1].click();
    }
}

// Universal ready handler
let lbwpsReady = (function () {
    let readyEventFired = false;
    return function (fn) {
        // Create an idempotent version of the 'fn' function
        let idempotentFn = function () {
            if (readyEventFired) {
                return;
            }
            readyEventFired = true;
            return fn();
        }

        // If the browser ready event has already occured
        if (document.readyState === "complete") {
            return idempotentFn()
        }

        // Use the event callback
        document.addEventListener("DOMContentLoaded", idempotentFn, false);

        // A fallback to window.onload, that will always work
        window.addEventListener("load", idempotentFn, false);
    }
})();

lbwpsReady(function() {
    window.lbwpsPhotoSwipe = null;
    lbwpsInit(false);

    let mutationObserver = null;
    if (typeof MutationObserver !== 'undefined') {
        let mutationObserver = new MutationObserver(function (mutations) {
            if (window.lbwpsPhotoSwipe === null) {
                let nodesAdded = false;
                for (let i = 0; i < mutations.length; i++) {
                    if ('childList' === mutations[i].type) {
                        nodesAdded = true;
                    }
                };
                if (nodesAdded) {
                    lbwpsInit(true);
                }
            }
        });
        mutationObserver.observe(document.querySelector('body'), {childList: true, subtree: true, attributes: false});
    }
});
