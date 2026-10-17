Lightweight WordPress Theme Starter
Matt Persell-Thompson 
Web Developer & Designer



## 🛠️ Packages Being Used

| *Sass* | Compiles SCSS files into optimized CSS. |
| *esbuild* | Fast JavaScript bundler, minifier, and source-map generator. |
| *Chokidar* | Watches PHP, SCSS, and JS files for changes in real time. |
| *ws* | Runs the background WebSocket server for live browser reloads. |
| *npm-run-all* | Runs build and watch scripts concurrently in one terminal command. |


# Install exact dependencies
npm install [or] npm install -D sass esbuild chokidar ws npm-run-all

# Start dev environement (compilers and file watcher)
npm run dev




📁 Starter Directory Structure
Plaintext
your-theme-folder/
├── src/
│   ├── scss/
│   │   └── style.scss          # Primary SCSS entry point
│   └── js/
│       └── main.js             # Primary JS entry point
├── functions.php               # Enqueues assets & injects WebSocket client
├── package.json                # Dependencies and build/watch scripts
├── watcher.js                  # Custom WebSocket LiveReload server
├── style.css                   # Compiled & minified CSS (WordPress theme root)
├── script.js                   # Compiled & minified JS (WordPress theme root)
└── index.php                   # Theme template files (.php)






🚀 Quick Start (New Projects)
1. Copy Template Files
Copy package.json, watcher.js, src/, and the relevant hooks in functions.php into your new theme directory (wp-content/themes/your-theme-name/).

2. Update Theme Metadata
Edit the header comment in style.css (or src/scss/style.scss) to match your new theme details:

CSS
/*
Theme Name: Your Theme Name
Author: Your Name
Version: 1.0.0
*/


3. Install Dependencies
In your terminal, navigate to your theme root directory and run:

Bash
npm install
4. Start Development Mode
Run the concurrent dev command to start watching, compiling, minifying, and live-reloading:

Bash
npm run dev
Open your local development site (e.g., [http://your-site.local](http://your-site.local)). Open the browser Developer Console (Cmd + Option + I) to verify the connection message:

⚡ Connected to LiveReload!





📜 Configuration Code

package.json
JSON
{
  "name": "custom-wp-theme-starter",
  "version": "1.0.0",
  "type": "module",
  "scripts": {
    "watch:scss": "sass src/scss/style.scss style.css --style=compressed --watch",
    "watch:js": "esbuild src/js/main.js --outfile=script.js --bundle --minify --sourcemap --watch",
    "watch:reload": "node watcher.js",
    "dev": "run-p watch:scss watch:js watch:reload"
  },
  "devDependencies": {
    "chokidar": "^3.6.0",
    "esbuild": "^0.20.0",
    "npm-run-all": "^4.1.5",
    "sass": "^1.71.0",
    "ws": "^8.16.0"
  }
}







watcher.js
import { WebSocketServer } from 'ws';
import chokidar from 'chokidar';

const wss = new WebSocketServer({ port: 35729 });
let clients = new Set();

wss.on('connection', (ws) => {
  clients.add(ws);
  console.log('⚡ Browser connected to LiveReload');
  ws.on('close', () => clients.delete(ws));
});

function notifyReload(file) {
  console.log(`[Reload] File changed: ${file}`);
  clients.forEach((client) => {
    if (client.readyState === 1) {
      client.send(JSON.stringify({ type: 'reload', file }));
    }
  });
}

const watcher = chokidar.watch(['style.css', 'script.js', '**/*.php'], {
  ignored: [/node_modules/, /\.git/],
  persistent: true,
  ignoreInitial: true,
  usePolling: true,
  interval: 100
});

watcher.on('change', (path) => notifyReload(path));
console.log('⚡ Native LiveReload server running on port 35729...');





functions.php
PHP
<?php

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

add_action('wp_head', function() {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $is_local = str_contains($host, '.local') || str_contains($host, 'localhost') || str_contains($host, '127.0.0.1');

    if (!$is_local) {
        return;
    }
    ?>
    <script>
      (function() {
        const socket = new WebSocket('ws://localhost:35729');

        socket.onopen = () => console.log('⚡ Connected to LiveReload!');

        socket.onmessage = (event) => {
          const data = JSON.parse(event.data);
          if (data.file.endsWith('.css')) {
            const links = document.getElementsByTagName('link');
            for (let link of links) {
              if (link.rel === 'stylesheet' && link.href.includes('style.css')) {
                const url = new URL(link.href);
                url.searchParams.set('v', Date.now());
                link.href = url.href;
              }
            }
          } else {
            window.location.reload();
          }
        };

        socket.onerror = () => {
          console.warn('LiveReload server not active on port 35729');
        };
      })();
    </script>
    <?php
});





🛠 Troubleshooting
Port Conflict (EADDRINUSE: :::35729)
If a previous session hung and locked port 35729, kill the process using:

Bash
lsof -ti :35729 | xargs kill -9