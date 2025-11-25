(function(){
  function initCarousel(root){
    try {
      var settings = JSON.parse(root.getAttribute('data-settings') || '{}');
    } catch(e){
      var settings = {};
    }

    var viewport = root.querySelector('.p5m-carousel-viewport');
    var track = root.querySelector('.p5m-carousel-track');
    if(!viewport || !track) return;

    var slides = Array.prototype.slice.call(track.children);
    if(slides.length === 0) return;

    var slidesDesktop = parseInt(settings.slidesDesktop || 3, 10);
    var slidesTablet = parseInt(settings.slidesTablet || 2, 10);
    var slidesMobile = parseInt(settings.slidesMobile || 1, 10);

    function slidesPerView(){
      var w = root.clientWidth;
      if (w < 640) return slidesMobile;
      if (w < 1024) return slidesTablet;
      return slidesDesktop;
    }

    function applySlideSizes(){
      var spv = slidesPerView();
      var basis = (100 / spv) + '%';
      slides.forEach(function(sl){
        sl.style.flex = '0 0 ' + basis;
      });
    }

    var state = {
      index: 0,
      translating: false,
      autoplayTimer: null,
      width: 0,
      total: slides.length,
      pos: 0
    };

    function updateWidth(){
      state.width = viewport.clientWidth;
    }

    function translateTo(index, animate){
      var spv = slidesPerView();
      var maxIndex = Math.max(0, state.total - spv);
      if(settings.infinite && !settings.marquee){
        // infinite by cloning edges if needed
        if(!track.__cloned){
          var head = slides.slice(0, spv).map(function(n){ return n.cloneNode(true); });
          var tail = slides.slice(-spv).map(function(n){ return n.cloneNode(true); });
          tail.forEach(function(n){ track.insertBefore(n, track.firstChild); });
          head.forEach(function(n){ track.appendChild(n); });
          track.__cloned = true;
          slides = Array.prototype.slice.call(track.children);
          state.total = slides.length;
          state.index = index + spv;
          requestAnimationFrame(function(){
            track.style.transform = 'translate3d(' + (-state.index * (100/spv)) + '%,0,0)';
          });
          return;
        }
        var targetIndex = index + spv; // offset for clones
        if(animate){
          track.style.transition = 'transform ' + (settings.transitionSpeed||400) + 'ms ease';
        } else {
          track.style.transition = 'none';
        }
        track.style.transform = 'translate3d(' + (-targetIndex * (100/spv)) + '%,0,0)';
        state.index = index;
        // reset edges
        setTimeout(function(){
          track.style.transition = 'none';
          if(state.index < 0){
            state.index = (slides.length - spv*2) - 1;
          } else if (state.index > (slides.length - spv*2 - 1)){
            state.index = 0;
          }
          var resetTarget = state.index + spv;
          track.style.transform = 'translate3d(' + (-resetTarget * (100/spv)) + '%,0,0)';
        }, (settings.transitionSpeed||400));
      } else if (!settings.marquee){
        var clamped = Math.max(0, Math.min(index, maxIndex));
        if(animate){
          track.style.transition = 'transform ' + (settings.transitionSpeed||400) + 'ms ease';
        } else {
          track.style.transition = 'none';
        }
        var pct = -(clamped * (100/spv));
        track.style.transform = 'translate3d(' + pct + '%,0,0)';
        state.index = clamped;
      }
    }

    function next(){ translateTo(state.index + 1, true); }
    function prev(){ translateTo(state.index - 1, true); }

    function startAutoplay(){
      if(!settings.autoplay || settings.marquee) return;
      stopAutoplay();
      state.autoplayTimer = setInterval(function(){ next(); }, settings.autoplayDelay || 3000);
    }
    function stopAutoplay(){ if(state.autoplayTimer){ clearInterval(state.autoplayTimer); state.autoplayTimer = null; } }

    function handleResize(){
      applySlideSizes();
      updateWidth();
      // Reset position appropriate for current spv
      translateTo(state.index, false);
    }

    // Marquee mode
    var rafId = null; var lastTs = 0; var marqueeX = 0; var marqueeClones = false; var pause = false;
    function marqueeLoop(ts){
      if(!lastTs) lastTs = ts;
      var delta = (ts - lastTs) / 1000; // s
      lastTs = ts;
      var pxPerSec = settings.marqueeSpeed || 60;
      marqueeX -= pxPerSec * delta;
      track.style.transform = 'translate3d(' + marqueeX + 'px,0,0)';
      // when moved by a slide width, reset by cloning approach
      var first = track.children[0];
      if(first){
        var fw = first.getBoundingClientRect().width + parseFloat(getComputedStyle(track).columnGap||getComputedStyle(track).gap||0);
        if(-marqueeX > fw){
          marqueeX += fw;
          track.appendChild(first);
        }
      }
      if(!pause) rafId = requestAnimationFrame(marqueeLoop);
    }

    function initMarquee(){
      // Ensure enough slides by cloning to avoid gap
      if(!marqueeClones){
        var totalWidth = 0;
        slides.forEach(function(sl){ totalWidth += sl.getBoundingClientRect().width; });
        while(totalWidth < viewport.clientWidth * 2){
          slides.forEach(function(sl){ track.appendChild(sl.cloneNode(true)); });
          slides = Array.prototype.slice.call(track.children);
          totalWidth = 0; slides.forEach(function(sl){ totalWidth += sl.getBoundingClientRect().width; });
        }
        marqueeClones = true;
      }
      if(settings.marqueePauseOnHover){
        root.addEventListener('mouseenter', function(){ pause = true; cancelAnimationFrame(rafId); rafId = null; });
        root.addEventListener('mouseleave', function(){ if(!rafId){ pause = false; lastTs = 0; rafId = requestAnimationFrame(marqueeLoop); } });
      }
      rafId = requestAnimationFrame(marqueeLoop);
    }

    // Arrows
    var prevBtn = root.querySelector('.p5m-carousel-prev');
    var nextBtn = root.querySelector('.p5m-carousel-next');
    if(prevBtn) prevBtn.addEventListener('click', function(){ prev(); startAutoplay(); });
    if(nextBtn) nextBtn.addEventListener('click', function(){ next(); startAutoplay(); });

    // Init
    applySlideSizes();
    updateWidth();

    if(settings.marquee){
      initMarquee();
    } else {
      translateTo(0, false);
      startAutoplay();
      if(settings.autoplay){
        root.addEventListener('mouseenter', stopAutoplay);
        root.addEventListener('mouseleave', startAutoplay);
      }
    }

    window.addEventListener('resize', handleResize);
  }

  function initAll(){
    document.querySelectorAll('.p5m-posts-carousel').forEach(initCarousel);
  }
  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', initAll);
  } else { initAll(); }
})();
