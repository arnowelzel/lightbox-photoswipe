import PhotoSwipeLightbox from '/v5/photoswipe/photoswipe-lightbox.esm.js';

// Simple fullscreen API, stolen from v5 documentation (Native fullscreen on open)
const fullscreenAPI = getFullscreenAPI();

// Simple fullscreen SVG icon
const fullscreenSVG = '<svg aria-hidden="true" class="pswp__icn" viewBox="0 0 32 32" width="32" height="32"><use class="pswp__icn-shadow" xlink:href="#pswp__icn-fullscreen-exit"/><use class="pswp__icn-shadow" xlink:href="#pswp__icn-fullscreen-request"/><path d="M8 8v6.047h2.834v-3.213h3.213V8h-3.213zm9.953 0v2.834h3.213v3.213H24V8h-2.834zM8 17.953V24h6.047v-2.834h-3.213v-3.213zm13.166 0v3.213h-3.213V24H24v-6.047z" id="pswp__icn-fullscreen-request"/><path d="M11.213 8v3.213H8v2.834h6.047V8zm6.74 0v6.047H24v-2.834h-3.213V8zM8 17.953v2.834h3.213V24h2.834v-6.047h-2.834zm9.953 0V24h2.834v-3.213H24v-2.834h-3.213z" id="pswp__icn-fullscreen-exit" style="display:none"/></svg>';

const options = {
  gallerySelector: '.gallery',
  childSelector: 'a',
  pswpModule: '/v5/photoswipe/photoswipe.esm.js',
  pswpCSS: '/v5/photoswipe/photoswipe.css'
};

const lightbox = new PhotoSwipeLightbox(options);

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
});

// Exit full-screen mode when closing the lightbox
lightbox.on('close', () => {
  if (fullscreenAPI && fullscreenAPI.isFullscreen()) {
    fullscreenAPI.exit();
  }
});

lightbox.init();

// Simple fullscreen API helper, stolen from v5 documentation (Native fullscreen on open)
function getFullscreenAPI() {
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
};

// Toggle full-screen mode function
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