import PhotoSwipeLightbox from './lib/photoswipe-lightbox.esm.min.js';
import PhotoSwipeDynamicCaption from './dynamic-caption/photoswipe-dynamic-caption-plugin.esm.min.js';

let lbwpsInit = function(domUpdate) {
    const fullscreenAPI = getFullscreenAPI();

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

                if (element.getAttribute('data-lbwps-title') != null) {
                    title = title + '<div class="pswp__caption__title">' + element.getAttribute('data-lbwps-title') + '</div>';
                }

                if (lbwpsOptions.usecaption === '1' && caption != null) {
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

    let openPhotoSwipe = function(element_index, group_index, element, fromURL, returnToUrl) {
        let id = 1,
//            gallery,
//            options,
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
            bgOpacity: 1,
            showHideAnimationType: 'fade',
            showAnimationDuration: 250,
            hideAnimationDuration: 250,
            pswpModule: () => import('./lib/photoswipe.esm.min.js'),
        }

        // Additional options (also see https://photoswipe.com/options/)

        options.spacing = lbwpsOptions.spacing/100;
        options.loop = lbwpsOptions.loop === '1';
        options.wheelToZoom = lbwpsOptions.wheelmode === 'zoom';
        options.pinchToClose = lbwpsOptions.pinchtoclose === '1';
        options.closeOnVerticalDrag = lbwpsOptions.close_on_drag === '1';
        options.clickToCloseNonZoomable = true;

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
        options.timeToIdle = lbwpsOptions.idletime;
        */

        const lightbox = new PhotoSwipeLightbox(options);
        lightbox.on('destroy', () => {
            if (lbwpsOptions.hide_scrollbars === '1') {
                showScrollbar();
            }
            if (element) {
                element.focus();
            }
        });

        // Add fullscreen button and keyboard shortcut for fullscreen mode
        // based on the idea described at https://github.com/dimsemenov/PhotoSwipe/issues/1759
        if (lbwpsOptions.show_fullscreen === '1') {
            const fullscreenSVG = '<svg aria-hidden="true" class="pswp__icn" viewBox="0 0 32 32" width="32" height="32">' +
                '<use class="pswp__icn-shadow" xlink:href="#pswp__icn-fullscreen-exit"/>' +
                '<use class="pswp__icn-shadow" xlink:href="#pswp__icn-fullscreen-request"/>' +
                '<path id="pswp__icn-fullscreen-request" transform="translate(4,4)" d="M20 3h2v6h-2V5h-4V3h4zM4 3h4v2H4v4H2V3h2zm16 16v-4h2v6h-6v-2h4zM4 19h4v2H2v-6h2v4z" /></g>' +
                '<path id="pswp__icn-fullscreen-exit" style="display:none" transform="translate(4,4)" d="M18 7h4v2h-6V3h2v4zM8 9H2V7h4V3h2v6zm10 8v4h-2v-6h6v2h-4zM8 15v6H6v-4H2v-2h6z"/>' +
                '</svg>';

            lightbox.on('uiRegister', function() {
                lightbox.pswp.ui.registerElement({
                    name: 'fullscreen-button',
                    title: 'Toggle fullscreen',
                    order: 9,
                    isButton: true,
                    html: fullscreenSVG,
                    onClick: (event, el) => {
                        toggleFullscreen();
                    }
                });

                lightbox.pswp.events.add(document, 'keydown', (e) => {
                    if (e.keyCode == 70) { // 'f'
                        toggleFullscreen();
                        e.preventDefault();
                    }
                });
            });

            lightbox.on('close', () => {
                if (fullscreenAPI && fullscreenAPI.isFullscreen()) {
                    fullscreenAPI.exit();
                }
            });
        }

        // Experimental slide transition - does not work very well with endless rotation
        /*
        const customGoTo = (index, animate = false) => {
            const ctx = lightbox.pswp;
            index = ctx.getLoopedIndex(index);
            const indexChanged = ctx.mainScroll.moveIndexBy(index - ctx.potentialIndex, animate);

            if (indexChanged) {
                ctx.dispatch('afterGoto');
            }
        }
        lightbox.on('uiRegister', () => {
            lightbox.pswp.next = () => customGoTo(lightbox.pswp.potentialIndex + 1, true);
            lightbox.pswp.prev = () => customGoTo(lightbox.pswp.potentialIndex - 1, true);
        });
        */

        // Add captions with dynamic caption plugin
        if (lbwpsOptions.show_caption === '1') {
            const captionPlugin = new PhotoSwipeDynamicCaption(lightbox, {
                type: lbwpsOptions.caption_type,
                captionContent: (slide) => {
                    return '<div class="pswp__caption">'
                        +slide.data.title
                        +'</div>'
                        +'<div class="pswp__caption__exif">'
                        +slide.data.exif
                        +'</div>';
                }
            });
        }

        lightbox.init();
        if (lbwpsOptions.hide_scrollbars === '1') {
            hideScrollbar();
        }
        lightbox.loadAndOpen(index);
    };

    // Fullscreen API helper
    function getFullscreenAPI()
    {
        let api;
        let enterFS;
        let exitFS;
        let elementFS;
        let changeEvent;
        let errorEvent;
        if (document.documentElement.requestFullscreen) {
            enterFS = 'requestFullscreen';
            exitFS = 'exitFullscreen';
            elementFS = 'fullscreenElement';
            changeEvent = 'fullscreenchange';
            errorEvent = 'fullscreenerror';
        } else if (document.documentElement.webkitRequestFullscreen) {
            enterFS = 'webkitRequestFullscreen';
            exitFS = 'webkitExitFullscreen';
            elementFS = 'webkitFullscreenElement';
            changeEvent = 'webkitfullscreenchange';
            errorEvent = 'webkitfullscreenerror';
        }
        if (enterFS) {
            api = {
                request: function (el) {
                    if (enterFS === 'webkitRequestFullscreen') {
                        el[enterFS](Element.ALLOW_KEYBOARD_INPUT);
                    } else {
                        el[enterFS]();
                    }
                },
                exit: function () {
                    return document[exitFS]();
                },
                isFullscreen: function () {
                    return document[elementFS];
                },
                change: changeEvent,
                error: errorEvent
            };
        }
        return api;
    }

    // Toggle fullscreen view
    function toggleFullscreen() {
        if (fullscreenAPI) {
            if (fullscreenAPI.isFullscreen()) {
                // Exit full-screen mode
                fullscreenAPI.exit();
                // Toggle "Exit" and "Enter" full-screen SVG icon display
                setTimeout(function() {
                    document.getElementById('pswp__icn-fullscreen-exit').style.display = 'none';
                    document.getElementById('pswp__icn-fullscreen-request').style.display = 'inline';
                }, 300);
            } else {
                // Enter full-screen mode
                fullscreenAPI.request(document.querySelector(`.pswp`));
                // Toggle "Exit" and "Enter" full-screen SVG icon display
                setTimeout(function() {
                    document.getElementById('pswp__icn-fullscreen-exit').style.display = 'inline';
                    document.getElementById('pswp__icn-fullscreen-request').style.display = 'none';
                }, 300);
            }
        }
    }

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