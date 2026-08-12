<?php
if (!defined('ABSPATH')) exit;

function se_get_critical_css() {
    return '
/* Critical CSS - Inlined in <head> */
:root{
  --se-primary:#FF6B35;
  --se-secondary:#2B7A62;
  --se-accent:#F9C80E;
  --se-bg:#FAFAF8;
  --se-text:#2D2D2D;
  --se-white:#FFF;
  --se-radius:12px;
  --se-shadow:0 2px 8px rgba(0,0,0,.08);
  --se-container:1200px;
}
*{box-sizing:border-box}
html{scroll-behavior:smooth;-webkit-text-size-adjust:100%}
body{margin:0;font-family:"Inter",system-ui,sans-serif;font-size:1rem;line-height:1.6;color:var(--se-text);background:var(--se-bg);-webkit-font-smoothing:antialiased}
h1,h2,h3,h4,h5,h6{font-family:"Fredoka",system-ui,sans-serif;font-weight:600;line-height:1.2;margin:0 0 1rem}
h1{font-size:clamp(2rem,5vw,3.5rem)}h2{font-size:clamp(1.5rem,3vw,2.5rem)}h3{font-size:clamp(1.25rem,2.5vw,1.75rem)}
p{margin:0 0 1rem}
a{color:var(--se-primary);text-decoration:none;transition:color .2s}a:hover{color:var(--se-secondary)}
img{max-width:100%;height:auto;display:block}
button,input,select,textarea{font:inherit;color:inherit}
.wp-element-button,.button{cursor:pointer;border:none;border-radius:50px;padding:12px 24px;font-weight:600;transition:all .3s;display:inline-flex;align-items:center;justify-content:center;gap:8px}
.wp-element-button:hover,.button:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.15)}
.wp-block-button.is-style-outline .wp-block-button__link{background:transparent;border:2px solid currentColor}
.se-container{max-width:var(--se-container);margin:0 auto;padding:0 20px}
.se-grid{display:grid;gap:1.5rem}
.se-grid-2{grid-template-columns:repeat(2,1fr)}
.se-grid-3{grid-template-columns:repeat(3,1fr)}
.se-grid-4{grid-template-columns:repeat(4,1fr)}
@media (max-width:1024px){.se-grid-4{grid-template-columns:repeat(2,1fr)}.se-grid-3{grid-template-columns:repeat(2,1fr)}}
@media (max-width:768px){.se-grid-2,.se-grid-3,.se-grid-4{grid-template-columns:1fr}}
.se-flex{display:flex;gap:1rem;flex-wrap:wrap}
.se-flex-center{align-items:center;justify-content:center}
.se-flex-between{justify-content:space-between;align-items:center}
.se-card{background:var(--se-white);border-radius:var(--se-radius);box-shadow:var(--se-shadow);overflow:hidden;transition:transform .3s,box-shadow .3s}
.se-card:hover{transform:translateY(-4px);box-shadow:0 8px 24px rgba(0,0,0,.12)}
.se-product-card .se-product-image-link{display:block;aspect-ratio:1/1;overflow:hidden}
.se-product-card img{transition:transform .5s ease}.se-product-card:hover img{transform:scale(1.05)}
.se-product-actions{position:absolute;inset:0;background:rgba(255,255,255,.95);display:flex;gap:8px;align-items:center;justify-content:center;padding:16px;opacity:0;transition:opacity .3s}.se-product-card:hover .se-product-actions{opacity:1}
.se-product-actions button{width:44px;height:44px;border-radius:50%;background:var(--se-white);border:none;box-shadow:var(--se-shadow);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .2s}.se-product-actions button:hover{background:var(--se-primary);color:var(--se-white);transform:scale(1.1)}
.se-product-title{font-size:1rem;font-weight:600;margin:.75rem 0 .25rem;line-height:1.3}.se-product-title a{color:var(--se-text)}.se-product-title a:hover{color:var(--se-primary)}
.se-product-price{font-size:1.125rem;font-weight:700;color:var(--se-primary)}
.se-star-rating{display:inline-block;width:80px;height:16px;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23F9C80E'%3E%3Cpath d='M12 2l3.09 6.26L24 9.27l-6 5.84 1.39 8.07L12 21.72l-7.39-3.86L0 13.27l7.2-1.07L12 0l5.09 11.16L24 12.73z'/%3E%3C/svg%3E") repeat-x;background-size:16px 16px}
.se-star-rating span{display:block;height:16px;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23ddd'%3E%3Cpath d='M12 2l3.09 6.26L24 9.27l-6 5.84 1.39 8.07L12 21.72l-7.39-3.86L0 13.27l7.2-1.07L12 0l5.09 11.16L24 12.73z'/%3E%3C/svg%3E") repeat-x;background-size:16px 16px}
.se-btn-group{display:flex;gap:8px;flex-wrap:wrap}
.se-badge{display:inline-block;padding:4px 10px;border-radius:50px;font-size:.75rem;font-weight:600;text-transform:uppercase}
.se-sale-badge{background:var(--se-primary);color:var(--se-white)}
.se-out-of-stock-badge{background:#ccc;color:#666}
.se-container-narrow{max-width:800px;margin:0 auto;padding:0 20px}

/* Header critical */
.se-header{position:sticky;top:0;z-index:100;background:var(--se-white);box-shadow:var(--se-shadow);transition:box-shadow .3s}
.se-header.scrolled{box-shadow:0 4px 20px rgba(0,0,0,.1)}
.se-header-inner{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:12px 20px;max-width:var(--se-container);margin:0 auto}
.se-logo img{height:48px;width:auto}
.se-nav{display:flex;gap:2rem;list-style:none;margin:0;padding:0}.se-nav a{font-weight:500;padding:8px 0;position:relative}.se-nav a::after{content:"";position:absolute;bottom:0;left:0;width:0;height:2px;background:var(--se-primary);transition:width .3s}.se-nav a:hover::after{width:100%}
.se-header-actions{display:flex;align-items:center;gap:12px}
.se-search-form{position:relative}.se-search-form input{width:200px;padding:10px 40px 10px 16px;border:2px solid #eee;border-radius:50px;font-size:.875rem;transition:border-color .2s}.se-search-form input:focus{outline:none;border-color:var(--se-primary);width:280px}.se-search-form button{position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;padding:8px;cursor:pointer}
.se-mini-cart{position:relative}.se-mini-cart-btn{position:relative;background:none;border:none;padding:8px;cursor:pointer}.se-mini-cart-count{position:absolute;top:-4px;right:-4px;background:var(--se-primary);color:#fff;font-size:.7rem;font-weight:700;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center}

/* Mobile menu */
.se-mobile-toggle{display:none;background:none;border:none;padding:8px;cursor:pointer}
@media (max-width:900px){
  .se-nav{position:fixed;top:72px;left:0;right:0;background:var(--se-white);flex-direction:column;padding:20px;gap:1rem;box-shadow:0 8px 24px rgba(0,0,0,.1);transform:translateY(-100%);opacity:0;visibility:hidden;transition:all .3s}
  .se-nav.open{transform:translateY(0);opacity:1;visibility:visible}
  .se-search-form input{width:100%}
  .se-mobile-toggle{display:flex}
}

/* Footer critical */
.se-footer{background:var(--se-text);color:var(--se-white);padding:60px 20px 30px}
.se-footer-grid{display:grid;grid-template-columns:2fr repeat(3,1fr);gap:40px;max-width:var(--se-container);margin:0 auto;padding-bottom:40px}
@media (max-width:768px){.se-footer-grid{grid-template-columns:1fr 1fr}}
.se-footer h3{color:var(--se-white);font-size:1rem;margin-bottom:1.5rem}
.se-footer-nav{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:10px}.se-footer-nav a{color:rgba(255,255,255,.7);transition:color .2s}.se-footer-nav a:hover{color:var(--se-primary)}
.se-social-links{display:flex;gap:12px}.se-social-links a{width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;color:var(--se-white);transition:all .3s}.se-social-links a:hover{background:var(--se-primary);transform:translateY(-2px)}
.se-footer-bottom{border-top:1px solid rgba(255,255,255,.1);padding-top:20px;text-align:center;font-size:.875rem;color:rgba(255,255,255,.5)}

/* Skeleton loading */
.se-skeleton{background:linear-gradient(90deg,#f0f0f0 25%,#e0e0e0 50%,#f0f0f0 75%);background-size:200% 100%;animation:se-shimmer 1.5s infinite}
@keyframes se-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
.se-skeleton-img{aspect-ratio:1/1;border-radius:var(--se-radius)}
.se-skeleton-text{height:1rem;border-radius:4px;margin-bottom:.5rem}
.se-skeleton-text:last-child{width:60%}

/* Focus visible */
:focus-visible{outline:3px solid var(--se-accent);outline-offset:2px}

/* Skip link */
.se-skip-link{position:absolute;top:-100px;left:20px;background:var(--se-primary);color:#fff;padding:12px 20px;border-radius:8px;z-index:1000;transition:top .3s}.se-skip-link:focus{top:20px}
';
}
add_action('wp_head', function() {
    echo '<style id="se-critical-css">' . se_get_critical_css() . '</style>';
}, 1);

function se_dequeue_block_library_css() {
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('wc-block-style');
}
add_action('wp_enqueue_scripts', 'se_dequeue_block_library_css', 100);

function se_enqueue_assets() {
    $uri = get_template_directory_uri();
    $ver = SE_THEME_VERSION;
    
    wp_enqueue_style('se-fonts', $uri . '/assets/css/fonts.css', [], $ver);
    wp_enqueue_style('se-theme', $uri . '/assets/css/theme.css', ['se-fonts'], $ver);
    wp_enqueue_style('se-woocommerce', $uri . '/assets/css/woocommerce.css', ['se-theme'], $ver);
    
    wp_enqueue_script('se-main', $uri . '/assets/js/main.js', [], $ver, true);
    
    wp_localize_script('se-main', 'se_ajax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('se_ajax_nonce'),
        'cart_url' => wc_get_cart_url(),
        'checkout_url' => wc_get_checkout_url(),
        'is_rtl' => is_rtl(),
    ]);
}
add_action('wp_enqueue_scripts', 'se_enqueue_assets', 20);