// assets/js/header.js
document.addEventListener('DOMContentLoaded', function () {
  var btn = document.getElementById('p5m-menu-toggle');
  var nav = document.getElementById('p5m-mobile-nav');
  if (btn && nav) {
    btn.addEventListener('click', function () {
      var open = btn.getAttribute('aria-expanded') === 'true';
      btn.setAttribute('aria-expanded', String(!open));
      // alterna visibilidad
      nav.classList.toggle('hidden');
    });
  }

  // Mobile submenu toggles: append a toggle button for menu items with children
  var mobileNav = document.getElementById('mobile-nav');
  if (mobileNav) {
    var parentItems = mobileNav.querySelectorAll('li.menu-item-has-children, li.has-child');
    parentItems.forEach(function (li) {
      // avoid duplicating button
      if (li.querySelector('.submenu-toggle')) return;

      var link = li.querySelector('a');
      var toggle = document.createElement('button');
      toggle.type = 'button';
      toggle.className = 'submenu-toggle ml-2 inline-flex items-center justify-center p-2';
      toggle.setAttribute('aria-expanded', 'false');
      toggle.setAttribute('aria-label', 'Toggle submenu');
      // simple caret icon (SVG)
      toggle.innerHTML = '<svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 8L10 12L14 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

      if (link) {
        // Insert toggle INSIDE the link so clicks on the toggle don't trigger navigation
        link.appendChild(toggle);

        // Ensure toggle clicks don't cause the link to navigate
        toggle.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          li.classList.toggle('open');
          var expanded = li.classList.contains('open');
          toggle.setAttribute('aria-expanded', String(expanded));
        });

      } else {
        // fallback: append to li and behave similarly
        li.appendChild(toggle);
        toggle.addEventListener('click', function (e) {
          e.preventDefault();
          li.classList.toggle('open');
          var expanded = li.classList.contains('open');
          toggle.setAttribute('aria-expanded', String(expanded));
        });
      }
    });
  }

  // Header scroll threshold behavior con breakpoint mínimo
  var headerEl = document.querySelector('header.p5-header');
  if (headerEl) {
    var threshold = parseInt(headerEl.getAttribute('data-scroll-threshold') || '0', 10);
    var minWidth = parseInt(headerEl.getAttribute('data-scroll-min-width') || '0', 10); // p.ej. 1024 para desktop

    var eligible = function(){ return (minWidth === 0) || (window.innerWidth >= minWidth); };
    var apply = function(){
      if (threshold > 0 && eligible() && window.scrollY >= threshold) {
        headerEl.classList.add('is-scrolled');
      } else {
        headerEl.classList.remove('is-scrolled');
      }
    };

    apply();
    window.addEventListener('scroll', apply, { passive: true });
    window.addEventListener('resize', apply);

    // Optional: body offset only if explicitly enabled via data-fixed-offset="1"
    var fixedOffsetEnabled = headerEl.getAttribute('data-fixed-offset') === '1';
    if (fixedOffsetEnabled) {
      var updateFixedOffset = function() {
        var isFixed = headerEl.classList.contains('fixed');
        if (isFixed) {
          var h = headerEl.offsetHeight || 0;
          document.body.classList.add('has-fixed-header');
          document.body.style.setProperty('--p5-header-offset', h + 'px');
          document.body.style.paddingTop = 'var(--p5-header-offset)';
        } else {
          document.body.classList.remove('has-fixed-header');
          document.body.style.removeProperty('--p5-header-offset');
          document.body.style.paddingTop = '';
        }
      };
      updateFixedOffset();
      window.addEventListener('resize', updateFixedOffset);
      if ('ResizeObserver' in window) {
        try {
          var ro = new ResizeObserver(updateFixedOffset);
          ro.observe(headerEl);
        } catch (e) {}
      } else {
        headerEl.addEventListener('click', function(){ setTimeout(updateFixedOffset, 50); });
      }
    }
  }
});
