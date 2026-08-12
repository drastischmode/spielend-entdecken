(function() {
  'use strict';

  // Scroll-Triggered Animations
  var revealEls = document.querySelectorAll('.se-reveal, .se-reveal-scale');
  if (revealEls.length && 'IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    revealEls.forEach(function(el) { observer.observe(el); });
  } else {
    revealEls.forEach(function(el) { el.classList.add('is-visible'); });
  }

  // Animated Counter für Trust-Badges
  var counters = document.querySelectorAll('.se-counter');
  function animateCounter(el) {
    if (el.dataset.done) return;
    el.dataset.done = '1';
    var target = parseFloat(el.dataset.target || '0');
    var decimals = el.dataset.decimals ? parseInt(el.dataset.decimals, 10) : 0;
    var suffix = el.dataset.suffix || '';
    var prefix = el.dataset.prefix || '';
    var duration = 1600;
    var start = null;
    function step(ts) {
      if (!start) start = ts;
      var progress = Math.min((ts - start) / duration, 1);
      var eased = 1 - Math.pow(1 - progress, 3); // easeOutCubic
      var val = Math.floor(eased * target);
      el.textContent = prefix + formatNum(val, decimals) + suffix;
      if (progress < 1) requestAnimationFrame(step);
      else el.textContent = prefix + formatNum(target, decimals) + suffix;
    }
    requestAnimationFrame(step);
  }

  function formatNum(val, decimals) {
    if (decimals) return val.toFixed(decimals);
    // Bis 4 Ziffern ohne Tausenderpunkt (Jahreszahlen wie 1902), darüber mit Punkt
    return val >= 10000 ? val.toLocaleString('de-DE') : String(Math.round(val));
  }

  if (counters.length && 'IntersectionObserver' in window) {
    var co = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          co.unobserve(entry.target);
        }
      });
    }, { threshold: 0.4 });
    counters.forEach(function(el) { co.observe(el); });
  } else {
    counters.forEach(function(el) { el.textContent = el.dataset.target || el.textContent; });
  }
})();
