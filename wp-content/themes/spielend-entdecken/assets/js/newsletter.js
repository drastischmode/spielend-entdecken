/* Spielend Newsletter – verbindet Newsletter-Formulare mit dem REST-Endpoint des Plugins */
(function() {
  'use strict';

  function initNewsletterForm(form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();

      var email = form.querySelector('input[type="email"], input[name="email"]');
      var btn = form.querySelector('button[type="submit"]');
      var msg = form.querySelector('.se-newsletter-msg');
      var honeypot = form.querySelector('input[name="honeypot"]');

      if (!email || !email.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
        showMsg(form, 'Bitte gib eine gültige E-Mail-Adresse ein.', 'error');
        return;
      }

      var original = btn ? btn.textContent : '';
      if (btn) { btn.disabled = true; btn.textContent = 'Wird angemeldet…'; }

      var fd = new FormData();
      fd.append('email', email.value.trim());
      if (honeypot) fd.append('honeypot', honeypot.value);
      var first = form.querySelector('input[name="first_name"]');
      if (first && first.value) fd.append('first_name', first.value.trim());

      fetch('/wp-json/spielend/v1/newsletter/subscribe', {
        method: 'POST',
        body: fd
      })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data && data.success) {
          showMsg(form, data.message || 'Vielen Dank! Bitte bestätige deine Anmeldung per E-Mail.', 'success');
          form.querySelector('input[type="email"], input[name="email"]').value = '';
        } else {
          var msgText = (data && data.data && data.data.message) ? data.data.message : 'Anmeldung fehlgeschlagen. Bitte versuche es erneut.';
          showMsg(form, msgText, 'error');
        }
      })
      .catch(function() {
        showMsg(form, 'Verbindungsfehler. Bitte versuche es später erneut.', 'error');
      })
      .finally(function() {
        if (btn) { btn.disabled = false; btn.textContent = original; }
      });
    });
  }

  function showMsg(form, text, type) {
    var msg = form.querySelector('.se-newsletter-msg');
    if (!msg) {
      msg = document.createElement('p');
      msg.className = 'se-newsletter-msg';
      form.parentNode.appendChild(msg);
    }
    msg.className = 'se-newsletter-msg ' + (type === 'error' ? 'se-newsletter-msg--error' : 'se-newsletter-msg--success');
    msg.textContent = text;
    setTimeout(function() { msg.textContent = ''; }, 6000);
  }

  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form.se-newsletter, form.se-footer-newsletter, .se-newsletter-form').forEach(initNewsletterForm);
  });
})();
