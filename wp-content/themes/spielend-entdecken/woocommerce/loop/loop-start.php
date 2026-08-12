<?php
if (!defined('ABSPATH')) exit;
?>
<ul class="products columns-<?php echo esc_attr(wc_get_loop_prop('columns')); ?>" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:1.5rem;list-style:none;padding:0;margin:0;">