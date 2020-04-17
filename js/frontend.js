var lbwps_init = function() {
    var PhotoSwipe = window.PhotoSwipe,
        PhotoSwipeUI_Default = window.PhotoSwipeUI_Default;


    var links = document.querySelectorAll('a[data-width]');
    links.forEach(function(link) {
        link.addEventListener('click', function(event) {
            if (!PhotoSwipe || !PhotoSwipeUI_Default) {
                return;
            }

            event.preventDefault();
            openPhotoSwipe(false, 0, this, false, '');
        });
    });

    var parseThumbnailElements = function(link, id) {
        var elements,
            galleryItems = [],
            index;

        if (id == null || id == 1) {
            elements = document.querySelectorAll('a[data-width]:not([data-gallery-id])');
        } else {
            elements = document.querySelectorAll('a[data-width][data-gallery-id="'+id+'"]');
        }

        var number = 0;
        elements.forEach(function(element) {
            var caption = null;
            
            caption = element.getAttribute('data-caption');

            if(caption == null) {
                if(element.getAttribute('data-caption-title') != null) {
                    caption = '<div class="pswp__caption__title">'+element.getAttribute('data-caption-title')+'</div>';
                }

                if(element.getAttribute('data-caption-desc') != null) {
                    if(caption == null) caption = '';
                    caption = caption + '<div class="pswp__caption__desc">'+element.getAttribute('data-caption-desc')+'</div>';
                }
            }

            if(caption == null) {
                var nextElement = element.nextElementSibling;
                var parentElement = element.parentElement.nextElementSibling;
                var parentElement2 = element.parentElement.parentElement.nextElementSibling;
                var parentElement3 = element.parentElement.parentElement.parentElement.nextElementSibling;

                if(nextElement != null) {
                    if(nextElement.className === '.wp-caption-text') {
                        caption = nextElement.textContent;
                    } else if(nextElement && nextElement.nodeName === "FIGCAPTION") {
                        caption = nextElement.textContent;
                    }
                } else if(parentElement != null) {
                    if(parentElement.className === '.wp-caption-text') {
                        caption = parentElement.textContent;
                    } else if(parentElement.className === '.gallery-caption') {
                        caption = parentElement.textContent;
                    }
                } else if(parentElement2 && parentElement2.nodeName === "FIGCAPTION") {
                    caption = parentElement2.textContent;
                } else if(parentElement3 && parentElement3.nodeName === "FIGCAPTION") {
                    // This variant is used by Gutenberg gallery blocks
                    caption = parentElement3.textContent;
                }
            }

            if(caption == null) {
                caption = element.getAttribute('title');
            }

            if(caption == null && lbwps_options.use_alt == '1') {
                caption = element.firstElementChild.getAttribute('alt');
            }

            if(element.getAttribute('data-description') != null) {
                if(caption == null) caption = '';
                caption = caption + '<div class="pswp__description">'+element.getAttribute('data-description')+'</div>';
            }

            galleryItems.push({
                src: element.getAttribute('href'),
                w: element.getAttribute('data-width'),
                h: element.getAttribute('data-height'),
                title: caption,
                exif: element.getAttribute('data-exif'),
                getThumbBoundsFn: false,
                showHideOpacity: true,
                el: element
            });

            if(link === element) {
                index = number;
            }

            number++;
        });
        
        return [galleryItems, parseInt(index, 10)];
    };

    var photoswipeParseHash = function() {
        var hash = window.location.hash.substring(1), params = {};

        if(hash.length < 5) {
            return params;
        }

        var vars = hash.split('&');
        for(var i = 0; i < vars.length; i++) {
            if(!vars[i]) {
                continue;
            }
            var pair = vars[i].split('=');
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

    var openPhotoSwipe = function(element_index, group_index, element, fromURL, returnToUrl) {
        var id = 1,
            pswpElement = document.querySelector('.pswp'),
            gallery,
            options,
            items,
            index;

        if (element != null) {
            id = element.getAttribute('data-gallery-id');
        } else {
            id = group_index;
        }

        items = parseThumbnailElements(element, id);
        if(element_index == false) {
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

        if (id != null) {
            options.galleryUID = id;
        }

        if(lbwps_options.close_on_click == '0') {
            options.closeElClasses = ['pspw__button--close'];
        }

        if(lbwps_options.share_facebook == '1' ||
            lbwps_options.share_twitter == '1' ||
            lbwps_options.share_pinterest == '1' ||
            lbwps_options.share_download == '1' ||
            lbwps_options.share_copyurl == '1' ||
            (lbwps_options.share_custom_link !== '' && lbwps_options.share_custom_label !== '')) {
            options.shareEl = true;
            options.shareButtons = [];
            if(lbwps_options.share_facebook == '1') {
                if(lbwps_options.share_direct == '1') {
                    url = 'https://www.facebook.com/sharer/sharer.php?u={{image_url}}';
                } else {
                    url = 'https://www.facebook.com/sharer/sharer.php?u={{url}}';
                }
                options.shareButtons.push({id:'facebook', label:lbwps_options.label_facebook, url:url});
            }
            if(lbwps_options.share_twitter == '1') {
                if(lbwps_options.share_direct == '1') {
                    url = 'https://twitter.com/intent/tweet?text={{text}}&url={{image_url}}';
                } else {
                    url = 'https://twitter.com/intent/tweet?text={{text}}&url={{url}}';
                }
                options.shareButtons.push({id:'twitter', label:lbwps_options.label_twitter, url:url});
            }
            if(lbwps_options.share_pinterest == '1') options.shareButtons.push({id:'pinterest', label:lbwps_options.label_pinterest, url:'http://www.pinterest.com/pin/create/button/?url={{url}}&media={{image_url}}&description={{text}}'});
            if(lbwps_options.share_download == '1') options.shareButtons.push({id:'download', label:lbwps_options.label_download, url:'{{raw_image_url}}', download:true});
            if(lbwps_options.share_copyurl == '1') options.shareButtons.push({id:'copyurl', label:lbwps_options.label_copyurl, url:'{{raw_image_url}}', onclick:'window.lbwpsCopyToClipboard(\'{{raw_image_url}}\');return false;', download:false});
            if(lbwps_options.share_custom_link !== '' && lbwps_options.share_custom_label !== '') {
                options.shareButtons.push({id:'custom', label:lbwps_options.share_custom_label, url:lbwps_options.share_custom_link, download:false});
            }
        } else {
            options.shareEl = false;
        }

        if(lbwps_options.wheelmode == 'close') options.closeOnScroll = true;else options.closeOnScroll = false;
        if(lbwps_options.wheelmode == 'zoom') options.zoomOnScroll = true;else options.zoomOnScroll = false;
        if(lbwps_options.wheelmode == 'switch') options.switchOnScroll = true;else options.switchOnScroll = false;
        if(lbwps_options.close_on_drag == '1') options.closeOnVerticalDrag = true;else options.closeOnVerticalDrag = false;
        if(lbwps_options.history == '1') options.history = true;else options.history = false;
        if(lbwps_options.show_counter == '1') options.counterEl = true;else options.counterEl = false;
        if(lbwps_options.show_fullscreen == '1') options.fullscreenEl = true;else options.fullscreenEl = false;
        if(lbwps_options.show_zoom == '1') options.zoomEl = true;else options.zoomEl = false;
        if(lbwps_options.show_caption == '1') options.captionEl = true;else options.captionEl = false;
        if(lbwps_options.loop == '1') options.loop = true;else options.loop = false;
        if(lbwps_options.pinchtoclose == '1') options.pinchToClose = true;else options.pinchToClose = false;
        if(lbwps_options.taptotoggle == '1') options.tapToToggleControls = true; else options.tapToToggleControls = false;
        if(lbwps_options.desktop_slider == '1') options.desktopSlider = true; else options.desktopSlider = false;
        options.spacing = lbwps_options.spacing/100;

        options.timeToIdle = lbwps_options.idletime;

        if(fromURL == true) {
            options.index = parseInt(index, 10) - 1;
        }

        if(lbwps_options.fulldesktop == '1') {
            options.barsSize = {top: 0, bottom: 0};
        }

        gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, items, options);
        gallery.listen('gettingData', function (index, item) {
            if (item.w < 1 || item.h < 1) {
                var img = new Image();
                img.onload = function () {
                    item.w = this.width;
                    item.h = this.height;
                    gallery.updateSize(true);
                };
                img.src = item.src;
            }
        });
        
        if (returnToUrl != '') {
            gallery.listen('unbindEvents', function() {
                document.location.href = returnToUrl;
            });
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

    var hashData = photoswipeParseHash();
    if(hashData.pid && hashData.gid) {
        var returnUrl = '';
        if (typeof(hashData.returnurl) !== 'undefined') {
            returnUrl = hashData.returnurl;
        }
        openPhotoSwipe(hashData.pid, hashData.gid, null, true, returnUrl);
    }
};

if (document.readyState === "complete" || (document.readyState !== "loading" && !document.documentElement.doScroll)) {
    lbwps_init();
} else {
    document.addEventListener("DOMContentLoaded", lbwps_init);
}
