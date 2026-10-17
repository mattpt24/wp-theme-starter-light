<?php

// ADDED THEME SUPPORTS 
add_action('after_setup_theme', function() {
    add_theme_support('title-tag');    
    add_theme_support('post-thumbnails');
});

add_filter('upload_mimes', function($mimes) {
    $mimes['svg']  = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    return $mimes;
});



// ENQUEUE STYLES & SCRIPTS 
function theme_enqueue_scripts() {

    $css_path = get_template_directory() . '/style.css';
    if (file_exists($css_path)) {
        wp_enqueue_style('theme-style', get_stylesheet_uri(), [], filemtime($css_path));
    }

    $js_path = get_template_directory() . '/script.js';
    if (file_exists($js_path)) {
        wp_enqueue_script('theme-script', get_template_directory_uri() . '/script.js', [], filemtime($js_path), true);
    }
}

add_action('wp_enqueue_scripts', 'theme_enqueue_scripts');




// Inject Live Reload WebSocket script strictly on local dev domains
add_action('wp_head', function() {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $is_local = str_contains($host, '.local') || str_contains($host, 'localhost') || str_contains($host, '127.0.0.1');

    if (!$is_local) {
        return; // REJECT IF NOT A LOCAL SITE
    }
    ?>
    <script>
      (function() {
        const socket = new WebSocket('ws://localhost:35729');

        socket.onopen = () => {
          console.log('⚡ Connected to LiveReload!');
        };

        socket.onmessage = (event) => {
          const data = JSON.parse(event.data);
          if (data.file.endsWith('.css')) {
            // Hot swap style.css without page refresh
            const links = document.getElementsByTagName('link');
            for (let link of links) {
              if (link.rel === 'stylesheet' && link.href.includes('style.css')) {
                const url = new URL(link.href);
                url.searchParams.set('v', Date.now());
                link.href = url.href;
              }
            }
          } else {
            // Full reload for PHP or JS edits
            window.location.reload();
          }
        };

        socket.onerror = (err) => {
          console.warn('LiveReload server not running on localhost:35729');
        };
      })();
    </script>
    <?php
});