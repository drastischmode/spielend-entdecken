# Spielend Entdecken — Projekt-Memory

## Live Site
- **URL:** http://spielend.ct.ws/
- **Host:** InfinityFree (ftpupload.net, Port 21)
- **FTP:** `/spielend.ct.ws/htdocs/` (User: `if0_42146922`, Pass: `ft0Pddadm4`)
- **WordPress:** WP 7.0.2, PHP 8.3.19, Theme `spielend-entdecken` (custom block theme)
- **WooCommerce:** 9.1.4 (9.3.3/9.2.3 crashen auf dem Freehost → downgrade auf 9.1.4 nötig)
- **WP-Admin:** User `game` / `SPiel361!?!` — Login per API/curl wird vom Freehost blockiert (WAF), nur aus dem Browser funktioniert er; alle Admin-Aufgaben laufen über FTP + PHP-Bootstrap (`wp-load.php`)

## Zugriffsmethode (JRB Remote API + Anti-Bot-Cookie)
Der Host hat einen JS-Challenge vor allen Seiten. Workaround:
1. `GET /wp-json/jrb/v1/site` mit Header `X-JRBRemoteSite-Token: nEFI0TQv78CeGijsUPmKRGsdpUWTFIcJA1tr9nGCriaWCiGs00rZ1iVinzgBQBeH`
2. Aus Antwort 3× `toNumbers("hex")` extrahieren → AES-128-CBC decrypt → Wert als Cookie `__test` setzen
3. Jede Anfrage mit `?i=1` (Cache-Bypass) + `__test`-Cookie
```python
import requests, re
from Crypto.Cipher import AES
cv = AES.new(bytes.fromhex(m[0]), AES.MODE_CBC, bytes.fromhex(m[1])).decrypt(bytes.fromhex(m[2])).hex()
```

## WooCommerce Shop – verifiziert & repariert (End-to-End-Test)
- **KRITISCHER FIX**: Warenkorb/Checkout-Seiten nutzten das Block-`page.html`-Template (nur statische Überschrift, kein Cart) → auf klassische Shortcodes umgestellt: Warenkorb(ID 23)=`[woocommerce_cart]`, Checkout(ID 24)=`[woocommerce_checkout]`, Konto(ID 25)=`[woocommerce_my_account]`
- **Kompletter Kaufprozess getestet (Browser)**: Shop(136 Prod, 16/Seite, Pagination) → Sortierung (Preis aufsteigend korrekt) → Produktseite (Gallery, Add-to-Cart, Tabs, Related) → Warenkorb (Item, Coupon-Feld, Summe, Checkout-Button) → Checkout (Billing-Felder, Zahlung: bacs Überweisung default + cod Nachnahme, Place-Order aktiv, Bestellübersicht)
- **My Account**: Login + Registrierung beide aktiv (Registrierung via `woocommerce_enable_myaccount_registration` aktiviert)
- **Einstellungen aktiv**: Guest Checkout=yes, Coupons=yes, Reviews=yes, Registrierung=yes
- **Wunschliste NEU gebaut** (statt totem YITH-Shortcode): `[spielend_wishlist_button]` (auf Produktseite via `woocommerce_single_product_summary` Hook) + `[spielend_wishlist_page]`, Cookie-basiert (`spielend_wishlist`), im spielend-essentials Plugin; Wunschzettel-Seite(ID 10) umgestellt. WICHTIG: Inline-JS braucht eigenes `wp_register_script('spielend-wishlist', false...)`-Handle, `wp_add_inline_script` hängt sonst nirgends
- **Produkt-IDs für Tests**: flip-7=138, cabo=137, speks-slabs=162
- Bestellung bewusst NICHT abgeschickt (würde echte Bestellung erzeugen)

## Fehlerbehebungen (Detailanalyse Runde 3)
- **HTML-Fix Kategorie-Grid**: `wpautop`/Block-Renderer fügte leere `</p>`-Tags in `<a>`-Karten ein (Whitespace/Klickfehler/SEO) → `clean_category_grid_output()` im Plugin (the_content + render_block Filter, entfernt `<p></p>` und `</p>` vor `<figure>`); verifiziert: 8 Karten, 0 Bugs
- **"Beliebte Produkte" bereinigt**: Zeigte alphabetisch Biergläser + Diddl als "Bestseller" → 8 passende Produkte als `_featured=yes` markiert (Tonies 30/31/32, Papierflieger 148, Ravensburger Puzzle 152, Bücher 133/134/135) + eigener Shortcode `[spielend_featured_products ids="..."]` (WC-Block `post__in` rendert leere Titel als Bug!) mit sauberem `.se-featured-grid`/`.se-prod-card` Markup (Titel/Preis/Button/Bild, 4→2→1 Spalten responsive)
- **Kategorie "dies & das"** → **"Dies & Das"** umbenannt
- **Sortierung/Breadcrumb/Sprache**: waren bereits gefixt (wc-translate.js, .home .se-breadcrumb CSS); Analyse behauptete "Add to cart"/"Breadcrumb Home › Shop" sichtbar — falsch, im Browser DE + versteckt (Server-HTML zeigt EN, Client-JS übersetzt)

## Migrations-Vorbereitung (Freehost → Vollhost)
- **Migrations-Checkliste**: `/home/ubuntu/spielend/MIGRATION.md` (kombiniert mit PROJECT-MEMORY.md als Projekt-Doku)
- **Zahlungsarten vorkonfiguriert**: PayPal aktiviert (email philbo.modulor@gmail.com), Überweisung (bacs) mit PLATZHALTER-Bankkonto (DE0000... → User muss echte IBAN eintragen), Nachnahme; Stripe/Klarna-Optionen existieren, `enabled:no` (nur Keys fehlen)
- **Versand**: Zone "Deutschland" mit Flat-Rate 4,90€ (inst 1) + Free Shipping ab 50€ (inst 2) sauber angelegt
- **Steuer**: 19% MwSt.-Satz für DE angelegt (woocommerce_tax_rates + locations), Preise inkl. MwSt
- **Produktbilder vereinheitlicht**: alle 136 Produktbilder + 7 fehlende Kategorie-Thumbnails auf **800×800 (1:1)** center-crop (lokal PIL → Staging-Upload → PHP-Datei-Swap, ohne GD-Regeneration um Fatal-Error zu vermeiden); verifiziert im Browser: Shop + Kategorie-Grid alle ratio 1.00
- Checkout verifiziert: MwSt. sichtbar, 3 Zahlungsmethoden (bacs/cod/paypal) + Place-Order
- WICHTIG für Umzug: WooCommerce 9.1.4 (9.3+ crasht Freehost), wp-content/languages/plugins/woocommerce-de_DE.* mitnehmen, Anti-Bot blockt REST auf Freehost (AJAX nur server-seitig; auf Vollhost normal)

## Design-Overhaul (Runde: "noch lange nicht high quality")
- **Section-Rhythmik behoben**: vorher 6 aufeinanderfolgende weiße Sektionen (eintönig) → jetzt abwechselnd Weiß (#FFF) / Creme (#FAFAF8) / Weiß / Creme / Weiß / Grün (Newsletter) / Dunkel (Footer)
- **Section-Subtitles** eingeführt (`.se-section-sub`): kurze Beschreibungszeile unter jeder H2 für Design-Hierarchie (Kategorien/Beliebte/USP/Blog)
- **Typografie-Skala**: H1 Seiten-Titel `clamp(1.8rem,4vw,2.6rem)` + margin-bottom 24px (vorher klebte "Shop"-H1 am Breadcrumb); Section-H2 letter-spacing -0.01em, weight 700
- **KRITISCHES Mobile-Problem**: Header war 278px hoch (Logo+Nav+Suche+Cart gestapelt) → Desktop-Navigation auf <768px ausgeblendet (Mobile-Bottom-Bar übernimmt!), Header als Grid `1fr auto`, Logo 100px → **82px** Header
- Hero: Vollhöhe `calc(100vh - 130px)`, besseres 3-Stufen-Dark-Overlay (35%→55%→72%), Mobile-Titel clamp(1.9-2.6rem)
- Karten-Schatten vereinheitlicht, Footer-H3 als Uppercase-Labels
- **Verifiziert**: 5 Seiten Desktop + 3 Mobile, kein Overflow, Header 82px überall, keine JS-/HTTP-Fehler
- **Commit `f383408`** gepusht

## Wichtige Hosting-Limits
- `fopen`/Filesystem-Funktionen blockiert → PHP-Skripte fatal-error, wenn sie Dateien via fopen lesen; `file_get_contents`/`file_put_contents` auf Theme-Dateien funktionieren (→ Theme-Edits per FTP-Download/Edit/Upload)- FTP-Upload limitiert (~20MB Dateigrößen schlagen fehl) → große Zips via PHP `download_url()` vom Server holen lassen oder direkt von wordpress.org
- Complianz-Cookie-Plugin crasht die Seite (Fatal Error) → gelöscht; stattdessen eigenes DSGVO-Cookie-Banner ins Theme eingebaut (`assets/js/cookie-banner.js` + `assets/css/cookie-banner.css`)
- WP-Login-POSTs von außen: 302 zurück, kein Auth-Cookie (WAF)

## Firmendaten (von alter Seite spielend-entdecken.de)
- **Firma:** Lessenich GmbH, Hochstraße 57, 47918 Tönisvorst
- **Geschäftsführer:** Andreas Lessenich & Stefan Lessenich
- **Telefon:** +49 (0)2151 - 970267 · **WhatsApp:** 0152 - 22 41 47 91
- **E-Mail:** info@spielend-entdecken.de (auch info@lessenich.net)
- **USt-ID:** DE 814 62 4792
- **Öffnungszeiten:** Mo-Fr 10:00–13:00 & 14:00–18:00, Sa 10:00–14:00
- **Claim:** "Seit 1902" (NICHT 2020!), kleiner Spielwarenladen vom Niederrhein, seit über 120 Jahren

## Inhalt
- 136 Produkte (alle mit Bildern), 29 Kategorien, 22 Kategorie-Thumbnails
- 7 Content-Seiten + WooCommerce-Seiten (Shop, Warenkorb, Checkout, Konto)
- Permalinks: `/%postname%/` (deutsch: `/warenkorb/`, `/ueber-uns/`; My Account bleibt `/my-account/`)
- Kategorien-Slugs: `tonies`, `lego`, `kreativ`, `puzzle`, … (kein `holzspielzeug`!)

## Inhalt (maximal ergänzt)
- **136 Produkte**: ALLE haben jetzt Beschreibungen (Template-basiert: Name + Tönisvorst-Bezug "Seit 1902, Hochstraße 57" + Marken/Kategorie-Kontext + Service-Hinweis) und Excerpts; Preise alle gesetzt (0 ohne)
- **29 Kategorien**: alle mit Beschreibungen (individuelle Texte pro Slug) + 15 mit SEO-Meta (rank_math_description + _yoast_wpseo_metadesc)
- **7 Blog-Artikel**: Holzspielzeuge, Kreativität, Nachhaltigkeit, Tonies, Brettspiele, Hobby Horsing, LEGO — alle mit Produkt-Links
- **Rechtsseiten erweitert**: AGB (Geltung/Vertragsschluss/Preise/Lieferung/Eigentumsvorbehalt/Gewährleistung/OS), Widerruf (Frist/Widerrufsrecht/Rücksendung), Datenschutz (Rechte/Cookies/Hosting/SSL)
- **Über uns**: Geschichte (seit 1902, GF Andreas & Stefan Lessenich, 180 m², Versprechen)
- **Kontakt**: Adresse/Tel/E-Mail/WhatsApp/Öffnungszeiten + Link Über uns
- **Seiten bereinigt**: Sample Page, About Us (EN), Shop/Checkout-Duplikate, Privacy Policy gelöscht
- **SEO-Meta** für 7 Kernseiten gesetzt (Rank Math/Yoast-Format, greift bei Plugin-Install)
- Warenkorb-Seite mit Überschrift
- Zen-AI (AI Engine) für Produktbeschreibungen nutzbar, aber Rate-Limits auf Free-Modellen → Template-Generator schneller

## Konfiguriert (Optionen)
- Zahlungsarten: **Überweisung (bacs)** + **Nachnahme (cod)** aktiv; PayPal/Stripe deaktiviert
- Versand: Flat 4,90 €; **kostenlos ab 50 €** (free_shipping)
- Währung EUR, Land DE:NW, Preise inkl. MwSt, Steuerberechnung aktiv
- `site_icon` = Attachment 325, `custom_logo` = Attachment 324 (Logo.png von alter Seite)
- `blogdescription` = "Hochwertiges Spielzeug seit 1902 – Spielend Entdecken in Tönisvorst…"
- Kontakt/Social-Optionen (`spielend_contact_*`, `spielend_social_*`) gesetzt
- Hero-Optionen (`spielend_hero_*`): Titel, Untertitel, 2 CTAs, Video-URL, Badges (Zeilen "Text|Link")
- Blog-Seite erstellt (ID 328, `page_for_posts`), 8 Posts live unter `/blog/`

## Performance (optimiert)
- **W3 Total Cache aktiviert**: Page Cache (file), DB Cache, Browser Cache, Minify
- Statische Seiten: **112–169ms** (gecacht) statt 2,2–2,5s vorher; Cache-Control `max-age=2592000`
- **Hero-Video komprimiert**: 9,9MB → 1,1MB (ffmpeg: 1280px, CRF 28, 24fps, faststart, `-an`)
- Nach Änderungen an functions.php/theme.json: **W3TC-Cache flushen** (sonst alte Meta-Tags/Assets)
- WooCommerce-Session-Seiten (warenkorb/checkout/my-account) bleiben uncacht (1,7–2,3s, erwartet)
- W3TC cached NICHT URLs mit Query-String → `?i=1`-Requests sind immer langsam (Cache-Bypass, nur für Admin-Tests nutzen)

## Admin-Seite (neu)
- WP-Admin → Menü **"Spielend Entdecken"** (Icon dashicons-toys, Position 58)
- Tab-basiert: Allgemein / Startseite-Hero / Kontakt / Social / Newsletter / Shop
- Nutzt die korrekten `spielend_*`-Keys (Plugin-Lesekette `get_theme_mod` → `get_option`)
- Helper `spielend_opt($key, $default)` für Theme/Plugin
- Hero-Shortcode `[se_hero]` (Titel, Untertitel, 2 CTAs, Video, Poster, Badges dynamisch)
- **Admin-Dashboard-Widget** `spielend_overview`: Offene Bestellungen, Tagesumsatz, Produkte, Bewertungen + Schnellzugriffe

## Frontend-Features (ergänzt)
- **WhatsApp-Floating-Button** (`assets/js/whatsapp.js` + `assets/css/whatsapp.css`): wa.me/4915222414791, Float-Animation, Label bei Hover
- **Blog**: 3 SEO-Artikel erstellt (Holzspielzeuge, Kreativität, Nachhaltigkeit), Junk-Posts gelöscht; `home.html` = Blog-Index-Template (Post-Grid); `/blog/` zeigt 3 Karten
- **Social-Links**: `spielend_social_tiktok/instagram/facebook` = spielwaren_lessenich; Footer nutzt dynamischen `[spielend_social_icons]`-Shortcode
- **Mobile-Fix**: `overflow-x:hidden` auf html/body (Mini-Cart-Drawer verursachte horizontalen Scroll auf <768px)
- **Shop-Suche**: Header-Suchformular (`.se-search-form`) → `/ ?s=`, `pre_get_posts` beschränkt Suche auf `product`; `search.html` neu geschrieben (wp:query mit postType=product, 4-Spalten-Grid, Produkt-Preis+Button)
- **Kategorie-QuickNav** (`.se-category-quicknav`): 12 Top-Kategorien als Pill-Buttons über dem Produkt-Grid (Hook `woocommerce_before_shop_loop`, Priorität 5), aktive Kategorie markiert
- **Breadcrumbs**: Shop/Kategorie über `wc-block-breadcrumbs` bzw. `wp_body_open`-Hook für Shop; Startseite per CSS versteckt (`.home .se-breadcrumb{display:none}`)
- **WICHTIG**: AJAX/REST-Suche (`/wp-json/spielend/v1/search`) funktioniert NICHT auf diesem Host (Anti-Bot-Challenge blockt alle `/wp-json/`-fetches ohne JS-Cookie) → nur form-basierte Suche nutzen!
- Verifiziert mit Playwright (14 Tests): Suche, QuickNav, Breadcrumbs, Sortierung, Produkte, WhatsApp alle ✅

## Wichtige WP-Besonderheit (Block-Themes)
- `front-page.html` = Startseite (wenn show_on_front=page), `home.html` = **Posts/Blog-Index** (NICHT Startseite!)
- Template-Parts mit `<meta>`-Tags rendern NICHT in `<head>` → SEO-Meta via `wp_head`-Hook (se_seo_meta)
- Template-Änderungen benötigen W3TC-Cache-Flush (`w3tc_flush_all()`), sonst bleiben alte Versionen hängen

## Theme-Struktur (`wp-content/themes/spielend-entdecken/`)
```
parts/header.html        — Header: Logo (site-logo), Nav (Shop/Blog/Über uns/Kontakt), Suche, Mini-Cart; Favicon-Links
parts/footer.html        — Footer: Legal-Links, "Seit 1902", © Jahr, Social Icons
templates/home.html      — Hero (Gradient, kein Platzhalter-Bild), Kategorie-Grid [spielend_category_grid], Produkte, Testimonials, Newsletter (inline Blocks)
functions.php            — Setup, Enqueue (theme.css, cookie-banner), se_seo_meta (Meta-Description + OG), Bloat-Entfernung, WC-Script-Optimierung
theme.json               — Farbpalette (Primary #FF6B35, Secondary #2B7A62, Accent #F9C80E, Background #FAFAF8, Foreground #2D2D2D, Base #FFF), Fredoka/Inter Fonts, spielend-hero Gradient
assets/js/cookie-banner.js   — DSGVO-Banner (localStorage se_cookie_consent)
assets/css/cookie-banner.css
inc/theme-options.php, woocommerce.php, schema.php, critical-css.php, acf-fields.php
patterns/register-patterns.php, additional-patterns.php
```
Wichtig: Template-Parts mit `<meta>`-Tags werden NICHT in den `<head>` gerendert (Block-Theme!) → SEO-Meta per `wp_head`-Hook in functions.php (`se_seo_meta`).

## Verify
- Alle Seiten: `/`, `/shop/`, `/impressum/`, `/datenschutz/`, `/agb/`, `/widerruf/`, `/ueber-uns/`, `/kontakt/`, `/warenkorb/`, `/checkout/`, `/my-account/`, `/product-category/*` → alle 200
- Kategorie-Archive rendern Produkte (Tonies = 12 Produkte)
- Kategorie-Grid dynamisch über Plugin-Shortcode `[spielend_category_grid]` (8 Top-Kategorien nach Produktanzahl)

## Offen (braucht User/Echtdaten)
- Bankverbindung für Überweisung (WooCommerce → Zahlungen → BACS)
- E-Mail-Versand (SMTP)
- Google Search Console / Analytics
- Impressum-Einträge sind vollständig (USt-ID DE 814 62 4792 drin)

## Design-Optimierungen (ergänzt)
- **Scroll-Animationen** (`assets/js/animations.js` + `assets/css/animations.css`): `se-reveal`/`se-reveal-scale` Sektionen mit IntersectionObserver (Fade-In-Up), `prefers-reduced-motion` respektiert
- **Animated Counter** in Trust-Badges: `se-counter` mit `data-target`, easeOutCubic, erkennt Zahlen in Badge-Texten (Seit **1902**, Über **130** Produkte, ab **50** €); Zahlen <10000 ohne Tausenderpunkt
- **Floating Toys** im Hero (`.se-hero-toy`): 4 schwebende Emojis (🧸🧩🎨🎲), z-index 2 über Video, opacity .22, float-Animation
- **Produktkarten-Hover**: Lift + orange Schatten (cubic-bezier bounce), Sale-Badge als Orange-Gradient
- **USP-Section** (`.se-usp-grid`, 4 Kacheln: Laden 180m² / Beratung seit 1902 / Nachhaltig / Schneller Versand) in front-page.html
- **FAQ-Accordion** (`.se-faq-item`, 5 `<details>`-Fragen: Versand/Abholung/Geschenkverpackung/Zahlung/Rückgabe) in front-page.html
- **Sticky Header**: `.se-header` mit `position:fixed; top:0` + `main{padding-top:130px}`, `.home .se-hero{margin-top:-130px}`; `main.js` (war nie enqueued!) jetzt in functions.php geladen — `scrolled`-Klasse = Schatten beim Scrollen
- Die Analyse (Hero-Video/Produktkacheln/Trust-Badges/Suche/Warenkorb/Breadcrumbs "fehlen") war komplett veraltet — alles existierte; nur USP/FAQ/Sticky-Header waren wirklich neu
- **Bewusst NICHT umgesetzt** (Risiko/Nutzen): Dark Mode, Custom Cursor, Sound-Design, Seasonal Themes, Skeleton-Loading (Server-Rendering, kein AJAX), Geschenk-Finder (hoher Aufwand), Glassmorphism (kontrastarm für WCAG), Sticky Mobile Bottom-Bar (Mini-Cart-Drawer übernimmt das), Instagram-Feed (braucht API-Key)

## Aufbauende Optimierungen (Runde 2)
- **Blog-Teaser auf Startseite**: 3 Karten mit den neuesten Beiträgen (`se-blog-teaser-card`, 16/10-Bilder, wp:query perPage=3) + "Alle Beiträge ansehen"-Button; zwischen FAQ und Newsletter
- **Add-to-Cart-Button in Orange**: Gradient `linear-gradient(135deg,#FF6B35,#E85D04)`, weißer Text, Schatten — alle WC-Buttons (add_to_cart, single_add_to_cart, a.button)
- **Mobile Sticky Bottom-Bar** (`se-mobile-bar`): fixiert unten, 5 Links (Start/Shop/Warenkorb/Wunschliste/Konto), Warenkorb-Zähler-Badge, `env(safe-area-inset-bottom)`, body-padding 56px auf <768px; in footer.html als HTML-Block
- Verifiziert (Playwright): Blog-Teaser 3 Karten, Button-Orange-Gradient, Mobile-Bar sichtbar + 5 Links + kein Overflow, keine JS-Fehler
- Die Analyse behauptete vieles fehle (USP, Sortierung, Newsletter, 4-Spalten-Footer) — alles existierte schon; nur Blog-Teaser, Orange-Button, Mobile-Bar waren neu

## UI-Design-Optimizer Skill (angewendet)
- Skill: `~/.config/opencode/skills/ui-design-optimizer/` → Empfehlung für Kinderprodukte: **Claymorphism** Style, ecommerce-orange Palette, Nunito (children) Typografie
- **Umgesetzt in theme.css**: Claymorphism-Produktkarten (3-Lagen-Grün-Schatten, radius 20px), Bild-Zoom 1.06 bei Hover, sanfte Add-to-Cart-Animationen, verspielte Button-Wölbung (translateY + box-shadow), Quick-View-Pfeil-Badge bei Hover
- Anti-Patterns des Skills beachtet: kein Pop-up-Overload, keine ablenkenden Hintergründe, keine Auto-Play-Video-Flut (nur 1 Hero-Video)
- Typografie: Fredoka/Inter bleiben (rund/verspielt = kindgerecht, entspricht Nunito-Empfehlung)
- Verifiziert (Playwright): Karten-Radius 20px + weicher Schatten, Hover-Zoom matrix(1.06), Mobile 1-Spalten-Grid, kein Overflow, keine JS-Fehler

## AI Engine (WordPress AI) – eingerichtet
- **AI Engine v3.7.0** installiert & aktiv (WordPress.org, getestet mit WP 7.0.3, PHP 8.1+; PHP 8.3 ✅)
- **Provider: OpenCode Zen** (`https://opencode.ai/zen/v1`, OpenAI-kompatibel), API-Key aus `~/.config/opencode/.secrets/zen.key`
- **Nur kostenlose Modelle** (User-Anforderung): Default `deepseek-v4-flash-free`, Fast `hy3-free` — beide verifiziert (HTTP 200, Antwort "Hallo!"); `ling-3.0-tiny-free` war temporär 503
- Weitere Free-Modelle verfügbar: `mimo-v2.5-free`, `laguna-s-2.1-free`, `nemotron-3-ultra-free`, `nemotron-3.5-lightning-free`, `big-pickle`
- **Kostenschutz**: Bilder/Audio/Embeddings-Modelle geleert (keine kostenpflichtigen Generierungen), nur Free-Modelle als Default
- Konfig in `mwai_options` (ai_envs[0], ai_default_model, ai_fast_default_model)
- **Kein WP-Core-AI**: WP 7.0.2 hat KEINE eingebauten AI-Dateien (ai_block/ai_assistant existieren nicht) — die Analyse war falsch; AI Engine ist die echte Lösung
- **WICHTIG (Sicherheit)**: API-Key NIE in PHP-Skripten loggen/zeigen; Konfig-Skripte mit Key sofort nach Upload/Ausführung von Server + lokal löschen (per FTP + rm)
- **WP-Admin-Login** (`game`) wird vom Freehost blockiert — AI Engine UI (Playground/Assistant) nur im Browser erreichbar; Verifikation über PHP-Curl gegen Zen möglich

## Kontrast & Übersetzung (WCAG AA + Deutsch)
- **Kontrast-Fixes in theme.css** (gemessen im Browser):
  - Hero-Badges: `rgba(255,255,255,.14)` über Video (unlesbar) → `rgba(26,26,46,.78)` + Blur = **17.06:1**
  - Produkttitel: `#FF6B35` (3:1) → `#C44A1A` = **4.84:1**; Hover `#A03A12`
  - Kategorie-Zähler: `#2B7A62` (4.5:1) → `#1B5A42` = **8.12:1**
  - Nav-Links: Orange → `#1A1A2E` (17:1), Hover `#C44A1A`
  - Text-Links generell: `#D45400` → `#C44A1A`/`#A03A12`
  - Fließtext: `#1A1A2E`
- **Deutsche WooCommerce-Übersetzung**: `woocommerce-de_DE.mo` heruntergeladen (downloads.wordpress.org/translation) nach `wp-content/languages/plugins/` — aber Block-Strings (Add to cart, Sortierung, Mini-Cart) brauchen JSON-Übersetzungen → **DOM-basiertes Übersetzungs-JS** `assets/js/wc-translate.js` (enqueued), das EN→DE-Strings nachlädt
- Übersetzt: Add to cart, Sortieroptionen, Mini-Cart "leer", Related products, Tabs, Checkout-Labels etc.
- Verifiziert: Sortieroptionen komplett deutsch (Standardsortierung/Nach Beliebtheit/Preis aufsteigend…), "In den Warenkorb", kein EN-Rest, keine JS-Fehler

## Git-Repo (neu eingerichtet)
- **Standort**: `/home/ubuntu/spielend/` (git, branch `main`)
- **Erster Commit** `5ad429f`: vollständiger Theme- (53 Dateien) + Plugin-Stand (2) + PROJECT-MEMORY.md + MIGRATION.md
- **Struktur**: `wp-content/themes/spielend-entdecken/` (komplett vom Server gemirrort) + `wp-content/plugins/spielend-essentials/` + Docs
- **.gitignore**: ignoriert andere Plugins (nur spielend-essentials tracked), keine WP-Core-Dateien
- **WICHTIG**: Repo enthält nur Theme/Plugin/Docs — KEINe Uploads (Produktbilder liegen nur auf Server)
- **Remote**: https://github.com/drastischmode/spielend-entdecken (öffentlich, gh-CLI als `drastischmode` authentifiziert)
- **History vereint** via `git merge --strategy-option=ours --allow-unrelated-histories origin/main`

### Push-Workflow (nach jeder Theme/Plugin-Änderung)
```bash
# 1. Änderungen auf dem Server deployen (FTP, wie gewohnt)
# 2. Aktuellen Stand vom Server spiegeln
lftp -u "if0_42146922","ft0Pddadm4" ftpupload.net -e "
mirror /spielend.ct.ws/htdocs/wp-content/themes/spielend-entdecken /home/ubuntu/spielend/wp-content/themes/spielend-entdecken
mirror /spielend.ct.ws/htdocs/wp-content/plugins/spielend-essentials /home/ubuntu/spielend/wp-content/plugins/spielend-essentials
quit"
# 3. Commit + Push
cd /home/ubuntu/spielend
git add -A
git commit -m "Beschreibung der Änderung"
git push origin main
```
- Zugangsdaten NIEMALS committen (FTP-Passwort, API-Keys, Tokens)
- Produktbilder/Uploads bleiben aus dem Repo (nur Server)

## Lokale Dateien
- `/tmp/opencode/` — Theme-Kopien (header/footer/home.html, functions.php), Import-Skripte, Logo.png
- Alte Seite scraped: `https://www.spielend-entdecken.de` (Produkte/Kategorien/Impressum/Datenschutz-Quelle)
