jQuery(function($) {
	var PhotoSwipe = window.PhotoSwipe,
		PhotoSwipeUI_Default = window.PhotoSwipeUI_Default;

	$('body').on('click', 'a[data-width]', function(e) {
		if( !PhotoSwipe || !PhotoSwipeUI_Default ) {
			return;
		}

		e.preventDefault();
		openPhotoSwipe( this );
	});

	var parseThumbnailElements = function(gallery, el) {
		var elements = $(gallery).find('a[data-width]').has('img'),
			galleryItems = [],
			index;

		elements.each(function(i) {
			var $el = $(this),
				caption;

			if( $el.next().is('.wp-caption-text') ) {
				// image with caption
				caption = $el.next().text();
			} else if( $el.parent().next().is('.wp-caption-text') ) {
				// gallery icon with caption
				caption = $el.parent().next().text();
			} else {
				caption = $el.attr('title');
			}

			galleryItems.push({
				src: $el.attr('href'),
				w: $el.attr('data-width'),
				h: $el.attr('data-height'),
				title: caption,
				getThumbBoundsFn: false,
				showHideOpacity: true,
				el: $el
			});
			if( el === $el.get(0) ) {
				index = i;
			}
		});

		return [galleryItems, parseInt(index, 10)];
	};

	var openPhotoSwipe = function( element, disableAnimation ) {
		var pswpElement = $('.pswp').get(0),
			galleryElement = $(element).parents('body').first(),
			gallery,
			options,
			items, index;

		items = parseThumbnailElements(galleryElement, element);
		index = items[1];
		items = items[0];

		options = {
			index: index,
			getThumbBoundsFn: false,
			showHideOpacity: true,
			history: false,
			shareEl: false
		};

		if(disableAnimation) {
			options.showAnimationDuration = 0;
		}

		gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);
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
		gallery.init();
	};
});
