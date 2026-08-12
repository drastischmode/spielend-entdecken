# Spielend Entdecken – Migrations-Checkliste (Freehost → Vollwertiger Webhost)

## Vorbereitet für den Umzug (bereits konfiguriert)

### Zahlungsarten (im Checkout aktiv)
| Methode | Status | Bemerkung |
|---------|--------|-----------|
| **Überweisung (bacs)** | ✅ aktiv | Bankkonto = PLATZHALTER (DE0000...) – **echte Daten eintragen!** |
| **Nachnahme (cod)** | ✅ aktiv | |
| **PayPal** | ✅ aktiv | email/receiver `philbo.modulor@gmail.com` hinterlegt – auf Zielhost verifizieren |

### Für spätere Integration vorbereitet (Platzhalter-Konfig vorhanden)
- **Stripe**: Gateway-Optionen existieren, `enabled: no` – nach Key-Eingabe aktivieren
- **Klarna**: Optionen existieren, `enabled: no` – braucht Merchant-Key

### Versand (Zone "Deutschland")
- **Flat-Rate** 4,90 € (taxable)
- **Free Shipping** ab 50 € (requires min_amount)

### Steuer
- **19% MwSt.** (DE) Satz angelegt (tax_rate DE 19.0000, shipping taxable)
- Preise inkl. MwSt, Steuerberechnung aktiv

### Währung & Land
- EUR, DE:NW, Store: Hochstraße 57, 47918 Tönisvorst

### Konten
- Guest Checkout ✅, Kunden-Registrierung ✅, Login beim Checkout ✅

## Beim Umzug NACHZUTRAGEN (braucht echte Daten)

### 🔴 Pflicht
1. **Domain**: `.de`-Domain kaufen (spielend-entdecken.de) + SSL
2. **BACS-Bankkonto**: echte IBAN/BIC in WooCommerce → Zahlungen → Überweisung
3. **PayPal** auf Zielhost mit echtem Geschäftskonto verifizieren
4. **Impressum**: Handelsregister-Nr. + USt-ID vervollständigen (DE 814 62 4792 ist drin, aber HR fehlt evtl.)

### 🟡 Empfohlen für Produktion
5. **Stripe/Klarna** aktivieren (PayPal allein reicht für DE-Kunden meist, aber Kreditkarte + Klarna erhöhen Conversion)
6. **SMTP** (E-Mail-Versand aus Bestellungen)
7. **Google Search Console** + Analytics
8. **Zahlungstest** mit Mini-Order (0,01 €) vor Go-Live

### 🟢 Optionale Verbesserung
9. **Eigene Produktfotos** (statt gescraper Bilder) – 1:1-Format ist vorbereitet (800×800)
10. **Cookie-Banner** auf Zielhost mit echtem DSGVO-Tool (aktuell Theme-eigenes Banner)
11. **W3 Total Cache** auf Zielhost neu konfigurieren (oder durch besseren Cache ersetzen)

## Technisches Setup auf Freehost (relevant für Export)

- **WP 7.0.2**, WooCommerce 9.1.4 (9.3+ crasht auf Freehost! Auf Zielhost aktualisieren)
- **Theme**: spielend-entdecken (Custom Block Theme, kein Page Builder)
- **Plugins aktiv**: spielend-essentials, JRB Remote API, W3TC, Loginizer, WooCommerce, AI Engine
- **136 Produkte**, 29 Kategorien, 7 Blog-Posts, alle Bilder 800×800
- **AI Engine** mit OpenCode Zen (Free-Modelle) konfiguriert
- **Hinweis**: Auf Freehost blockt Anti-Bot die REST-API für AJAX → Suche/Filter arbeiten server-seitig. Auf Zielhost mit echter Domain funktionieren AJAX/REST normal!

## Export-Anleitung
1. WordPress: Werkzeuge → Export (XML) für Beiträge/Seiten
2. WooCommerce: Export alle Produkte + Kategorien (CSV)
3. Dateien: `wp-content/uploads/` komplett + Theme + Plugins
4. Datenbank: phpMyAdmin-Export (oder All-in-One WP Migration Plugin)
5. Wichtig: `wp-content/languages/plugins/woocommerce-de_DE.*` mitnehmen (sonst WooCommerce auf Englisch!)
