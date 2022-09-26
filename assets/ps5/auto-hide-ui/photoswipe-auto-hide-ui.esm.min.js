const defaultOptions={idleTime:4000};class PhotoSwipeAutoHideUI{constructor(lightbox,options){this.options={...defaultOptions,...options};this.captionTimer=!1;this.lightbox=lightbox;this.hasTouch=!1;this.lightbox.on('change',()=>{document.addEventListener('touchstart',()=>{this.stopHideTimer();this.hasTouch=!0},{once:!0})
document.addEventListener('mousemove',()=>{this.startHideTimer()},{once:!0})});this.lightbox.on('destroy',()=>{this.stopHideTimer()})}
showUI(){if(this.lightbox&&this.lightbox.pswp&&this.lightbox.pswp.element){this.lightbox.pswp.element.classList.add('pswp--ui-visible')}}
hideUI(){if(this.lightbox&&this.lightbox.pswp&&this.lightbox.pswp.element){this.lightbox.pswp.element.classList.remove('pswp--ui-visible')}}
mouseMove(){this.stopHideTimer();if(this.lightbox){this.showUI();this.startHideTimer()}}
startHideTimer(){if(this.hasTouch){return}
this.stopHideTimer();this.captionTimer=window.setTimeout(()=>{this.hideUI()},this.options.idleTime);document.addEventListener('mousemove',()=>{this.mouseMove()},{once:!0})}
stopHideTimer(){if(this.captionTimer){window.clearTimeout(this.captionTimer);this.captionTimer=!1}}}
export default PhotoSwipeAutoHideUI