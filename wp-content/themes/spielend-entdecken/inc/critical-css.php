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
';
}

function se_dequeue_block_library_css() {
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('wc-blocks-style');
}
add_action('wp_enqueue_scripts', 'se_dequeue_block_library_css', 100);

function se_add_resource_hints($urls, $relation_type) {
    if ('preconnect' === $relation_type) {
        $urls[] = ['href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous'];
    }
    return $urls;
}
add_filter('wp_resource_hints', 'se_add_resource_hints', 10, 2);

function se_inline_critical_css() {
    echo '<style id="se-critical-css">' . se_get_critical_css() . '</style>';
}
add_action('wp_head', 'se_inline_critical_css', 1);
