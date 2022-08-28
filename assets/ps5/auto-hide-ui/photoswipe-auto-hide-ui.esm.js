/**
 * PhotoSwipe Auto Hide UI plugin v1.0.0
 *
 * By https://arnowelzel.de
 */

class PhotoSwipeAutoHideUI {
    constructor(lightbox, options) {
        this.options = {
            ...options
        };

        this.captionTimer = false;

        this.lightbox = lightbox;

        this.lightbox.on('change', () => {
            document.addEventListener('mousemove', () => { this.startHideTimer() }, {once:true});
        });

        this.lightbox.on('destroy', () => {
            this.stopHideTimer();
        });
    }

    showUI() {
        this.lightbox.pswp.element.classList.add('pswp--ui-visible');
    }

    hideUI() {
        this.lightbox.pswp.element.classList.remove('pswp--ui-visible');
    }

    mouseMove() {
        this.stopHideTimer();
        if (this.lightbox) {
            this.showUI();
            this.startHideTimer();
        }
    }

    startHideTimer() {
        this.stopHideTimer();
        this.captionTimer = window.setTimeout(() => {
            this.hideUI();
        }, 5000);
        document.addEventListener('mousemove', () => { this.mouseMove() }, {once:true});
    }

    stopHideTimer() {
        if (this.captionTimer) {
            window.clearTimeout(this.captionTimer);
            this.captionTimer = false;
        }
    }
}

export default PhotoSwipeAutoHideUI;
