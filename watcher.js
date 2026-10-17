import chokidar from 'chokidar';
import { WebSocketServer } from 'ws';

const wss = new WebSocketServer({ port: 35729 });

// Chokidar v4 approach: watch root directory, ignore node_modules and non-PHP/CSS/JS files
const watcher = chokidar.watch('.', {
  ignored: (path, stats) => {
    // Always ignore node_modules and hidden files (starting with .)
    if (path.includes('node_modules') || (stats && stats.isFile() && path.startsWith('.'))) {
      return true;
    }
    // If it's a file, only watch .php, .css, and .js files
    if (stats && stats.isFile()) {
      return !path.endsWith('.php') && !path.endsWith('.css') && !path.endsWith('.js');
    }
    return false;
  },
  ignoreInitial: true,
});

watcher.on('all', (event, filePath) => {
  console.log(`[Watcher] ${event}: ${filePath}`);
  wss.clients.forEach((client) => {
    if (client.readyState === 1) {
      client.send(JSON.stringify({ file: filePath }));
    }
  });
});
