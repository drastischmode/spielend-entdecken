# Spielend Essentials

Core functionality plugin for **spielend-entdecken.de** – custom post types, shortcodes and widgets for the "Spielend Entdecken" toy shop.

## Features

- **Testimonial custom post type** (`testimonial`) – public, with archive (`/testimonials/`), supports title, editor and featured image.
- **Newsletter subscriber custom post type** (`subscriber`) – internal, admin-managed, supports title and custom fields. The public REST endpoint feeds it.
- **Contact form handler** – stores submissions as `contact_message` posts and sends an email notification to the site admin.
- **REST endpoint** – `POST /wp-json/spielend/v1/newsletter/subscribe`.
- **Shortcodes** – `[spielend_social_icons]`, `[spielend_contact_info]`, `[spielend_contact_form]`.
- All inputs are sanitized and validated; forms are protected by nonces and honeypots.

## Requirements

| Software | Version |
| --- | --- |
| WordPress | 6.7+ |
| PHP | 8.1+ |

## Installation

1. Copy the `spielend-essentials` folder into `wp-content/plugins/`.
2. Activate the plugin under **Plugins → Installed Plugins**.
3. Optional: configure the theme options (see below) so the contact and social shortcodes render content.

## Configuration (theme options)

Values are read from WordPress theme mods (Customizer) first, falling back to options with the same key.

### Contact details

| Option key | Used by |
| --- | --- |
| `spielend_contact_phone` | `[spielend_contact_info]` |
| `spielend_contact_email` | `[spielend_contact_info]` |
| `spielend_contact_address` | `[spielend_contact_info]` |

### Social media profiles

Each URL is optional; only configured networks are rendered.

| Option key | Network |
| --- | --- |
| `spielend_social_facebook` | Facebook |
| `spielend_social_instagram` | Instagram |
| `spielend_social_x` | X (Twitter) |
| `spielend_social_youtube` | YouTube |
| `spielend_social_tiktok` | TikTok |
| `spielend_social_pinterest` | Pinterest |
| `spielend_social_whatsapp` | WhatsApp |

Programmatic example (e.g. in the theme's `functions.php`):

```php
set_theme_mod( 'spielend_contact_phone', '+49 30 12345678' );
set_theme_mod( 'spielend_social_instagram', 'https://www.instagram.com/spielend-entdecken' );
```

## Custom post types

### Testimonial (`testimonial`)

Public post type for customer reviews.

- URL slug: `/testimonials/`
- Supports: `title`, `editor`, `thumbnail`
- Available in the REST API (`show_in_rest`), so it can be queried by blocks.

### Newsletter subscriber (`subscriber`)

Internal post type holding newsletter sign-ups.

- Supports: `title`, `custom-fields`
- Not public; managed under **Newsletter** in the admin menu.
- The post title stores the email address; meta fields: `_spielend_email`, `_spielend_first_name`.

### Contact message (`contact_message`)

Internal post type used by the contact form handler to store every submission.

- Supports: `title`, `editor`
- Not public; managed under **Kontaktanfragen** in the admin menu.
- The title is `subject – name`; meta fields: `_spielend_name`, `_spielend_email`, `_spielend_subject`.

## REST API

### `POST /wp-json/spielend/v1/newsletter/subscribe`

Subscribes an email address to the newsletter.

**Request body (JSON or form-encoded):**

| Field | Type | Required | Description |
| --- | --- | --- | --- |
| `email` | string | yes | Valid email address |
| `first_name` | string | no | Subscriber's first name |
| `honeypot` | string | no | Spam trap – must be empty |

**Responses:**

| Status | Code | Meaning |
| --- | --- | --- |
| `200` | – | Success (`success: true`, `subscriber_id`). Also returned for spam bots that fill the honeypot, so they cannot detect the trap. |
| `400` | `spielend_invalid_email` | Email missing or invalid |
| `409` | `spielend_already_subscribed` | Email already subscribed |
| `500` | `spielend_subscribe_failed` | Could not store the subscriber |

**Example:**

```bash
curl -X POST https://spielend-entdecken.de/wp-json/spielend/v1/newsletter/subscribe \
  -H "Content-Type: application/json" \
  -d '{"email":"anna@example.com","first_name":"Anna"}'
```

```js
const response = await fetch( '/wp-json/spielend/v1/newsletter/subscribe', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify( { email: 'anna@example.com', first_name: 'Anna' } ),
} );
```

## Shortcodes

### `[spielend_social_icons]`

Outputs linked social icons (inline SVG, no external icon font) for every configured network.

```text
[spielend_social_icons]
```

### `[spielend_contact_info]`

Outputs phone, email and address from the theme options. Use the `show` attribute to limit the output.

```text
[spielend_contact_info]                // all three
[spielend_contact_info show="phone"]   // phone only
[spielend_contact_info show="email"]   // email only
[spielend_contact_info show="address"] // address only
```

The email is obfuscated with `antispambot()` to reduce harvesting; phone and email are rendered as `tel:` / `mailto:` links.

### `[spielend_contact_form]`

Renders the contact form (name, email, subject, message) with nonce and honeypot protection. Submissions are validated, stored as `contact_message` posts and emailed to the site admin.

```text
[spielend_contact_form]
[spielend_contact_form title="Schreib uns"]
```

## Hooks

### Actions

| Hook | Arguments | Description |
| --- | --- | --- |
| `spielend_newsletter_subscribed` | `$post_id`, `$email`, `$first_name` | Fired after a successful newsletter subscription. |

### Filters

| Hook | Arguments | Description |
| --- | --- | --- |
| `spielend_send_contact_email` | `bool $send` | Return `false` to disable the admin notification email. |

## Security

- Every input is sanitized on arrival (`sanitize_email`, `sanitize_text_field`, `sanitize_textarea_field`, `sanitize_key`, `wp_unslash`) and validated (`is_email`, required fields, maximum lengths).
- The contact form is protected by a WordPress nonce; both the contact form and the REST endpoint use a honeypot field to trap spam bots.
- All output is escaped (`esc_html`, `esc_url`, `esc_attr`).
- Contact form pages must not be served from a full-page cache, otherwise the nonce becomes stale. Exclude the page from caching (or from CDN caching) if you use a page cache.

## Changelog

### 1.0.0

- Initial release: testimonial, subscriber and contact message post types; newsletter REST endpoint; contact form handler; social icons and contact info shortcodes.
