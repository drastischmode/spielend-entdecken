(function() {
  var key = 'se_cookie_consent';
  try {
    if (localStorage.getItem(key)) return;
  } catch(e) {}
  var d = document.createElement('div');
  d.id = 'se-cookie-banner';
  d.setAttribute('role', 'dialog');
  d.setAttribute('aria-label', 'Cookie-Hinweis');
  d.innerHTML =
    '<div class="se-cookie-inner">' +
    '<p><strong>Cookie-Hinweis</strong><br>' +
    'Wir verwenden Cookies, um unsere Website zu verbessern und Ihnen den bestmöglichen Service zu bieten. ' +
    'Details finden Sie in unserer <a href="/datenschutz/">Datenschutzerklärung</a>.</p>' +
    '<div class="se-cookie-buttons">' +
    '<button type="button" class="se-cookie-accept" data-choice="all">Alle akzeptieren</button>' +
    '<button type="button" class="se-cookie-accept" data-choice="essential">Nur notwendige</button>' +
    '</div></div>';
  document.body.appendChild(d);
  d.querySelectorAll('.se-cookie-accept').forEach(function(b) {
    b.addEventListener('click', function() {
      try { localStorage.setItem(key, b.getAttribute('data-choice')); } catch(e) {}
      d.remove();
    });
  });
})();
