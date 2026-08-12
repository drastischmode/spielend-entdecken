(function() {
  'use strict';
  // WooCommerce Block-Strings Deutsch-Übersetzung (DOM-basiert)
  var translations = {
    'Add to cart': 'In den Warenkorb',
    'Default sorting': 'Standardsortierung',
    'Sort by popularity': 'Nach Beliebtheit',
    'Sort by average rating': 'Nach Bewertung',
    'Sort by latest': 'Nach Neuheit',
    'Sort by price: low to high': 'Preis aufsteigend',
    'Sort by price: high to low': 'Preis absteigend',
    'Your cart is currently empty': 'Dein Warenkorb ist derzeit leer.',
    'View cart': 'Warenkorb ansehen',
    'Showing': 'Zeige',
    'of': 'von',
    'results': 'Ergebnisse',
    'Related products': 'Ähnliche Produkte',
    'Description': 'Beschreibung',
    'Additional information': 'Zusätzliche Informationen',
    'Reviews': 'Bewertungen',
    'Only %d left in stock': 'Nur %d auf Lager',
    'In stock': 'Auf Lager',
    'Out of stock': 'Nicht auf Lager',
    'Free shipping': 'Kostenloser Versand',
    'Shipping': 'Versand',
    'Search results': 'Suchergebnisse',
    'Quantity': 'Menge',
    'Update cart': 'Warenkorb aktualisieren',
    'Proceed to checkout': 'Zur Kasse',
    'Coupon code': 'Gutscheincode',
    'Apply coupon': 'Gutschein anwenden',
    'Remove': 'Entfernen',
    'Total': 'Gesamtsumme',
    'Subtotal': 'Zwischensumme',
    'Cart totals': 'Warenkorbsumme',
    'Checkout': 'Zur Kasse',
    'Payment': 'Zahlung',
    'Place order': 'Bestellung aufgeben',
    'Your order': 'Deine Bestellung',
    'Product': 'Produkt',
    'Price': 'Preis',
    'Download': 'Download'
  };

  function translate(el) {
    if (el.nodeType !== 1 || el.closest('script,style,textarea')) return;
    var walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT, {
      acceptNode: function(node) {
        var parent = node.parentNode;
        if (!parent || parent.closest('script,style,textarea,input')) return NodeFilter.FILTER_REJECT;
        return node.textContent.trim() ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
      }
    });
    var nodes = [], node;
    while ((node = walker.nextNode())) nodes.push(node);
    nodes.forEach(function(tn) {
      var text = tn.textContent;
      var changed = false;
      Object.keys(translations).forEach(function(en) {
        var re = new RegExp('\\b' + en.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\b', 'g');
        if (re.test(text)) {
          text = text.replace(re, translations[en]);
          changed = true;
        }
      });
      if (changed) tn.textContent = text;
    });
    // Select-Optionen übersetzen
    var selects = el.querySelectorAll ? el.querySelectorAll('select option') : [];
    for (var i = 0; i < selects.length; i++) {
      var opt = selects[i];
      var txt = opt.textContent;
      if (translations[txt]) opt.textContent = translations[txt];
    }
  }

  function run() {
    translate(document.body);
  }
  if (document.body) run();
  document.addEventListener('DOMContentLoaded', run);
  // Nachladen durch WooCommerce-Blöcke abfangen
  setTimeout(run, 1500);
  setTimeout(run, 3000);
})();
