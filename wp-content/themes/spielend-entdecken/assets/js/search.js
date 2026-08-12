(function() {
  'use strict';
  var searchWrap = document.getElementById('se-search');
  if (!searchWrap) return;

  var input = searchWrap.querySelector('.se-search-input');
  var dropdown = searchWrap.querySelector('.se-search-dropdown');
  var productsEl = searchWrap.querySelector('.se-search-products');
  var catsEl = searchWrap.querySelector('.se-search-cats');
  var suggEl = searchWrap.querySelector('.se-search-suggestions');
  var clearBtn = searchWrap.querySelector('.se-search-clear');
  var timer = null;

  function closeDropdown() { dropdown.classList.remove('is-visible'); }
  function openDropdown() { dropdown.classList.add('is-visible'); }

  input.addEventListener('input', function() {
    var q = input.value.trim();
    clearTimeout(timer);
    if (q.length < 2) {
      showSuggestions();
      if (q.length === 0) { closeDropdown(); return; }
      openDropdown();
      return;
    }
    timer = setTimeout(function() { fetchResults(q); }, 300);
  });

  input.addEventListener('focus', function() {
    if (input.value.trim().length > 0) openDropdown();
  });

  document.addEventListener('click', function(e) {
    if (!searchWrap.contains(e.target)) closeDropdown();
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDropdown();
  });

  clearBtn.addEventListener('click', function() {
    input.value = '';
    closeDropdown();
    input.focus();
  });

  function showSuggestions() {
    var s = ['LEGO', 'Tonies', 'Ravensburger', 'Holzspielzeug', 'Puzzle', 'Knete'];
    if (!suggEl) return;
    suggEl.style.display = '';
    productsEl.style.display = 'none';
    catsEl.style.display = 'none';
    suggEl.innerHTML = s.map(function(t) {
      return '<span class="se-search-tag" data-q="' + t + '">' + t + '</span>';
    }).join('');
    suggEl.querySelectorAll('.se-search-tag').forEach(function(tag) {
      tag.addEventListener('click', function() {
        input.value = tag.getAttribute('data-q');
        fetchResults(tag.getAttribute('data-q'));
      });
    });
  }

  function fetchResults(q) {
    fetch('/wp-json/spielend/v1/search?q=' + encodeURIComponent(q), {
      headers: { 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) { renderResults(data, q); })
    .catch(function(err) { console.error('Search error', err); });
  }

  function renderResults(data, q) {
    var hasProducts = data.products && data.products.length;
    var hasCats = data.categories && data.categories.length;

    suggEl.style.display = 'none';
    productsEl.style.display = hasProducts ? '' : 'none';
    catsEl.style.display = hasCats ? '' : 'none';

    if (hasProducts) {
      productsEl.innerHTML = data.products.map(function(p) {
        var img = p.image ? '<img src="' + p.image + '" alt="" loading="lazy">' : '<span class="se-search-ph"></span>';
        return '<a href="' + p.url + '" class="se-search-product">' + img +
          '<span class="se-search-product-info"><span class="se-search-product-title">' + p.title + '</span>' +
          '<span class="se-search-product-price">' + p.price + '</span></span></a>';
      }).join('');
    } else {
      productsEl.innerHTML = '<p class="se-search-empty">Keine Produkte zu "' + q + '" gefunden</p>';
      productsEl.style.display = '';
    }

    if (hasCats) {
      catsEl.innerHTML = data.categories.map(function(c) {
        return '<a href="' + c.url + '" class="se-search-cat">' + c.name +
          ' <span class="se-search-cat-count">(' + c.count + ')</span></a>';
      }).join('');
    } else {
      catsEl.innerHTML = '';
    }

    openDropdown();
  }
})();
