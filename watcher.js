import { WebSocketServer } from 'ws';
import chokidar from 'chokidar';

const wss = new WebSocketServer({ port: 35729 });
let clients = new Set();

wss.on('connection', (ws) => {
  clients.add(ws);
  console.log('⚡ Browser connected to Site LiveReload');
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
  ignoreInitial: true
});

watcher.on('change', (path) => notifyReload(path));
console.log('⚡ Native LiveReload server running on port 35729...');