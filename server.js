const http = require('http');
const WebSocket = require('ws');
const server = http.createServer();
const wss = new WebSocket.Server({ server });

let players = {};
let colors = ['blue', 'red'];

function getIP(req) {
  return req.headers['x-forwarded-for'] || req.socket.remoteAddress;
}

wss.on('connection', function connection(ws, req) {
  const ip = getIP(req);
  if (Object.values(players).find(p => p.ip === ip)) {
    ws.send(JSON.stringify({ error: "Ya estás conectado desde este IP" }));
    return;
  }

  if (Object.keys(players).length >= 2) {
    ws.send(JSON.stringify({ error: "Juego lleno" }));
    return;
  }

  const id = Date.now();
  const color = colors[Object.keys(players).length];
  players[id] = { ws, ip, color, health: 3 };

  ws.send(JSON.stringify({ type: 'init', color }));

  ws.on('message', function incoming(message) {
    const data = JSON.parse(message);
    if (data.type === 'attack') {
      const opponent = Object.entries(players).find(([k, v]) => k != id);
      if (opponent) {
        opponent[1].health--;
        if (opponent[1].health <= 0) {
          players[id].ws.send(JSON.stringify({ type: 'result', win: true }));
          opponent[1].ws.send(JSON.stringify({ type: 'result', win: false }));
        } else {
          opponent[1].ws.send(JSON.stringify({ type: 'attacked' }));
        }
      }
    }
  });

  ws.on('close', () => {
    delete players[id];
  });
});

server.listen(3000, () => {
  console.log('Servidor WebSocket corriendo en http://localhost:3000');
});
