let lbwpsInit = function(domUpdate) {
    function lbwpsClick(event) {
        // Backwards compatible solution for older browsers
        if (event.target.parentNode.getAttribute('data-lbwps-width')) {

            event.preventDefault();
            openPhotoSwipe(false, 0, event.target.parentNode, false);
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
                openPhotoSwipe(false, 0, path[num], false);
                return;
            }
            num++;
        }
    }

    if (!domUpdate) {
        document.addEventListener('click', lbwpsClick);
    }

    let PhotoSwipe = window.PhotoSwipe,
        PhotoSwipeUI_Default = window.PhotoSwipeUI_Default;

    let links = document.querySelectorAll('a[data-lbwps-width]');

    let originalBodyPaddingRight = '';
    let originalBodyOverflow = '';

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

                galleryItems.push({
                    src: element.getAttribute('href'),
                    msrc: msrc,
                    w: element.getAttribute('data-lbwps-width'),
                    h: element.getAttribute('data-lbwps-height'),
                    title: title,
                    exif: element.getAttribute('data-lbwps-exif'),
                    getThumbBoundsFn: false,
                    showHideOpacity: true,
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

        if(params.pid) {
            params.pid = parseInt(params.pid, 10);
        }

        if(params.gid) {
            params.gid = parseInt(params.gid, 10);
        }

        return params;
    };

    let openPhotoSwipe = function(element_index, group_index, element, fromURL) {
        let id = 1,
            pswpElement = document.querySelector('.pswp'),
            gallery,
            options,
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

        options = {
            index: index,
            getThumbBoundsFn: false,
            showHideOpacity: true,
            loop: true,
            tapToToggleControls: true,
            clickToCloseNonZoomable: false,
        };

        if (fromURL === true) {
            options.index--;
        }

        if (id != null) {
            options.galleryUID = id;
        }

        if(lbwpsOptions.close_on_click === '0') {
            options.closeElClasses = ['pspw__button--close'];
        }

        if(lbwpsOptions.share_facebook === '1' ||
            lbwpsOptions.share_twitter === '1' ||
            lbwpsOptions.share_pinterest === '1' ||
            lbwpsOptions.share_download === '1' ||
            lbwpsOptions.share_copyurl === '1' ||
            (lbwpsOptions.share_custom_link !== '' && lbwpsOptions.share_custom_label !== '')) {
            options.shareEl = true;
            options.shareButtons = [];
            if(lbwpsOptions.share_facebook === '1') {
                if(lbwpsOptions.share_direct === '1') {
                    url = 'https://www.facebook.com/sharer/sharer.php?u={{image_url}}';
                } else {
                    url = 'https://www.facebook.com/sharer/sharer.php?u={{url}}';
                }
                options.shareButtons.push({id:'facebook', label:lbwpsOptions.label_facebook, url:url});
            }
            if(lbwpsOptions.share_twitter === '1') {
                if(lbwpsOptions.share_direct === '1') {
                    url = 'https://twitter.com/intent/tweet?text={{text}}&url={{image_url}}';
                } else {
                    url = 'https://twitter.com/intent/tweet?text={{text}}&url={{url}}';
                }
                options.shareButtons.push({id:'twitter', label:lbwpsOptions.label_twitter, url:url});
            }
            if(lbwpsOptions.share_pinterest === '1') options.shareButtons.push({id:'pinterest', label:lbwpsOptions.label_pinterest, url:'http://www.pinterest.com/pin/create/button/?url={{url}}&media={{image_url}}&description={{text}}'});
            if(lbwpsOptions.share_download === '1') options.shareButtons.push({id:'download', label:lbwpsOptions.label_download, url:'{{raw_image_url}}', download:true});
            if(lbwpsOptions.share_copyurl === '1') options.shareButtons.push({id:'copyurl', label:lbwpsOptions.label_copyurl, url:'{{raw_image_url}}', onclick:'window.lbwpsCopyToClipboard(\'{{raw_image_url}}\');return false;', download:false});
            if(lbwpsOptions.share_custom_link !== '' && lbwpsOptions.share_custom_label !== '') {
                options.shareButtons.push({id:'custom', label:lbwpsOptions.share_custom_label, url:lbwpsOptions.share_custom_link, download:false});
            }
        } else {
            options.shareEl = false;
        }

        options.closeOnScroll = lbwpsOptions.wheelmode === 'close';
        options.zoomOnScroll = lbwpsOptions.wheelmode === 'zoom';
        options.switchOnScroll = lbwpsOptions.wheelmode === 'switch';
        options.closeOnVerticalDrag = lbwpsOptions.close_on_drag === '1';
        options.history = lbwpsOptions.history === '1';
        options.counterEl = lbwpsOptions.show_counter === '1';
        options.fullscreenEl = lbwpsOptions.show_fullscreen === '1';
        options.zoomEl = lbwpsOptions.show_zoom === '1';
        options.captionEl = lbwpsOptions.show_caption === '1';
        options.loop = lbwpsOptions.loop === '1';
        options.pinchToClose = lbwpsOptions.pinchtoclose === '1';
        options.tapToToggleControls = lbwpsOptions.taptotoggle === '1';
        options.desktopSlider = lbwpsOptions.desktop_slider === '1';
        options.spacing = lbwpsOptions.spacing/100;
        options.timeToIdle = lbwpsOptions.idletime;

        if(lbwpsOptions.fulldesktop === '1') {
            options.barsSize = {top: 0, bottom: 0};
        }

        gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, items, options);
        gallery.listen('gettingData', function (index, item) {
            if (item.w < 1 || item.h < 1) {
                let img = new Image();
                img.onload = function () {
                    item.w = this.width;
                    item.h = this.height;
                    gallery.updateSize(true);
                };
                img.src = item.src;
            }
        });

        gallery.listen('destroy', function() {
            if (lbwpsOptions.hide_scrollbars === '1') {
                showScrollbar();
            }
            window.lbwpsPhotoSwipe = null;
            if (element) {
                element.focus();
            }
        })

        window.lbwpsPhotoSwipe = gallery;
        if (lbwpsOptions.hide_scrollbars === '1') {
            hideScrollbar();
        }
        gallery.init();
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
    };

    if(true !== domUpdate) {
        let hashData = photoswipeParseHash();
        if (hashData.pid && hashData.gid) {
            openPhotoSwipe(hashData.pid, hashData.gid, null, true);
        }
    }
};

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
    };
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
