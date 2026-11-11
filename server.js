import http from 'http';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { WebSocketServer, WebSocket } from 'ws';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const PORT = process.env.PORT || 3000;
const PUBLIC_DIR = path.join(__dirname, 'public');

// MIME types for static file serving
const MIME_TYPES = {
    '.html': 'text/html; charset=utf-8',
    '.js': 'text/javascript; charset=utf-8',
    '.css': 'text/css; charset=utf-8',
    '.json': 'application/json',
    '.png': 'image/png',
    '.jpg': 'image/jpeg',
    '.svg': 'image/svg+xml',
    '.ico': 'image/x-icon',
};

// Rooms and State Storage (In-memory, extensible to database)
const rooms = {
    'cell_104': {
        id: 'cell_104',
        name: 'Mijn Cel #104',
        desc: 'Je eigen beveiligde isolatiecel met betonnen muren en tralies',
        width: 7,
        height: 7,
        doorX: 6,
        doorY: 6,
        furniture: [
            { id: 1, type: 'bed', name: 'Stalen Celbed', x: 1, y: 1, canSit: true, rotation: 0 },
            { id: 2, type: 'table', name: 'Metalen Tafel', x: 4, y: 1, canSit: false, rotation: 0 },
            { id: 3, type: 'chair', name: 'Bajeskruk', x: 4, y: 2, canSit: true, rotation: 0 },
            { id: 4, type: 'toilet', name: 'RVS Toilet', x: 1, y: 5, canSit: true, rotation: 0 },
            { id: 5, type: 'crate', name: 'Smokkelkist', x: 5, y: 5, canSit: false, rotation: 0 },
        ],
        players: new Map(), // socketId -> playerData
    },
    'luchtplaats': {
        id: 'luchtplaats',
        name: 'De Luchtplaats',
        desc: 'Centrale betonnen binnenplaats met basketbalring en hekwerk',
        width: 10,
        height: 10,
        doorX: 9,
        doorY: 9,
        furniture: [
            { id: 10, type: 'punching_bag', name: 'Bokszak', x: 2, y: 2, canSit: false, rotation: 0 },
            { id: 11, type: 'punching_bag', name: 'Bokszak', x: 4, y: 2, canSit: false, rotation: 0 },
            { id: 12, type: 'chair', name: 'Betonbank', x: 7, y: 3, canSit: true, rotation: 0 },
            { id: 13, type: 'table', name: 'Picknicktafel', x: 7, y: 4, canSit: false, rotation: 0 },
        ],
        players: new Map(),
    },
    'kantine': {
        id: 'kantine',
        name: 'Recreatiezaal & Kantine',
        desc: 'Kaarttafels, zithoek en centrale ontmoetingsplek',
        width: 9,
        height: 8,
        doorX: 8,
        doorY: 7,
        furniture: [
            { id: 20, type: 'table', name: 'Lange Eettafel', x: 3, y: 2, canSit: false, rotation: 0 },
            { id: 21, type: 'chair', name: 'Kruk', x: 3, y: 3, canSit: true, rotation: 0 },
            { id: 22, type: 'chair', name: 'Kruk', x: 4, y: 3, canSit: true, rotation: 0 },
            { id: 23, type: 'crate', name: 'Voorraadkist', x: 7, y: 1, canSit: false, rotation: 0 },
        ],
        players: new Map(),
    },
    'bende_hq': {
        id: 'bende_hq',
        name: 'Cartel Bende Hoofdkwartier',
        desc: 'Geheim bende clubhuis met kluizen en vergadertafel',
        width: 8,
        height: 8,
        doorX: 7,
        doorY: 7,
        furniture: [
            { id: 30, type: 'table', name: 'Vergadertafel', x: 3, y: 3, canSit: false, rotation: 0 },
            { id: 31, type: 'chair', name: 'Fauteuil', x: 3, y: 4, canSit: true, rotation: 0 },
            { id: 32, type: 'crate', name: 'Buitkluis', x: 1, y: 1, canSit: false, rotation: 0 },
            { id: 33, type: 'crate', name: 'Wapenbox', x: 1, y: 2, canSit: false, rotation: 0 },
        ],
        players: new Map(),
    },
};

// HTTP Static File Server
const server = http.createServer((req, res) => {
    let filePath = path.join(PUBLIC_DIR, req.url === '/' ? 'index.html' : req.url.split('?')[0]);
    const ext = path.extname(filePath).toLowerCase();

    // Default to index.html if file doesn't exist (SPA style)
    if (!fs.existsSync(filePath) || fs.statSync(filePath).isDirectory()) {
        filePath = path.join(PUBLIC_DIR, 'index.html');
    }

    fs.readFile(filePath, (err, content) => {
        if (err) {
            res.writeHead(500, { 'Content-Type': 'text/plain' });
            res.end('500 Server Fout');
            return;
        }
        res.writeHead(200, { 'Content-Type': MIME_TYPES[ext] || 'application/octet-stream' });
        res.end(content);
    });
});

// Realtime WebSocket Game Server
const wss = new WebSocketServer({ server });
let clientCounter = 0;

function broadcastToRoom(roomId, message, excludeSocket = null) {
    const room = rooms[roomId];
    if (!room) return;
    const data = JSON.stringify(message);

    for (const [socket, player] of room.players.entries()) {
        if (socket !== excludeSocket && socket.readyState === WebSocket.OPEN) {
            socket.send(data);
        }
    }
}

wss.on('connection', (ws) => {
    const clientId = ++clientCounter;
    let currentRoomId = null;
    let playerData = null;

    // Heartbeat ping-pong
    ws.isAlive = true;
    ws.on('pong', () => { ws.isAlive = true; });

    ws.on('message', (raw) => {
        try {
            const data = JSON.parse(raw.toString());

            switch (data.type) {
                case 'join_room': {
                    // Leave previous room if any
                    if (currentRoomId && rooms[currentRoomId]) {
                        rooms[currentRoomId].players.delete(ws);
                        broadcastToRoom(currentRoomId, {
                            type: 'player_leave',
                            playerId: clientId,
                        });
                    }

                    currentRoomId = data.roomId || 'cell_104';
                    const room = rooms[currentRoomId] || rooms['cell_104'];

                    playerData = {
                        id: clientId,
                        name: data.playerName || `Speler #${clientId}`,
                        x: room.doorX - 1,
                        y: room.doorY - 1,
                        direction: 4,
                        isSitting: false,
                        isWaving: false,
                    };

                    room.players.set(ws, playerData);

                    // Send initial room state to joining player
                    const playersList = Array.from(room.players.values());
                    ws.send(JSON.stringify({
                        type: 'room_state',
                        room: {
                            id: room.id,
                            name: room.name,
                            desc: room.desc,
                            width: room.width,
                            height: room.height,
                            doorX: room.doorX,
                            doorY: room.doorY,
                        },
                        furniture: room.furniture,
                        players: playersList,
                        myId: clientId,
                    }));

                    // Announce player joined to others in room
                    broadcastToRoom(currentRoomId, {
                        type: 'player_join',
                        player: playerData,
                    }, ws);
                    break;
                }

                case 'move': {
                    if (!playerData || !currentRoomId) return;
                    playerData.x = data.x;
                    playerData.y = data.y;
                    playerData.direction = data.direction;
                    playerData.isSitting = data.isSitting || false;

                    broadcastToRoom(currentRoomId, {
                        type: 'player_move',
                        playerId: clientId,
                        x: data.x,
                        y: data.y,
                        targetX: data.targetX,
                        targetY: data.targetY,
                        direction: data.direction,
                        isSitting: data.isSitting || false,
                    }, ws);
                    break;
                }

                case 'chat': {
                    if (!playerData || !currentRoomId) return;
                    const text = (data.text || '').trim().slice(0, 120);
                    if (!text) return;

                    broadcastToRoom(currentRoomId, {
                        type: 'player_chat',
                        playerId: clientId,
                        name: playerData.name,
                        text: text,
                    });
                    break;
                }

                case 'action': {
                    if (!playerData || !currentRoomId) return;
                    if (data.action === 'wave') {
                        broadcastToRoom(currentRoomId, {
                            type: 'player_wave',
                            playerId: clientId,
                        });
                    } else if (data.action === 'sit') {
                        playerData.isSitting = !playerData.isSitting;
                        broadcastToRoom(currentRoomId, {
                            type: 'player_sit',
                            playerId: clientId,
                            isSitting: playerData.isSitting,
                        });
                    }
                    break;
                }

                case 'place_furni': {
                    if (!currentRoomId || !rooms[currentRoomId]) return;
                    const room = rooms[currentRoomId];
                    const item = {
                        id: Date.now(),
                        type: data.itemType,
                        name: data.itemName,
                        x: data.x,
                        y: data.y,
                        canSit: !!data.canSit,
                        rotation: data.rotation || 0,
                    };
                    room.furniture.push(item);

                    broadcastToRoom(currentRoomId, {
                        type: 'furni_placed',
                        item: item,
                    });
                    break;
                }
            }
        } catch (e) {
            console.error('Fout bij verwerken WebSocket bericht', e);
        }
    });

    ws.on('close', () => {
        if (currentRoomId && rooms[currentRoomId]) {
            rooms[currentRoomId].players.delete(ws);
            broadcastToRoom(currentRoomId, {
                type: 'player_leave',
                playerId: clientId,
            });
        }
    });
});

// Periodic Heartbeat check (every 20s to ensure zero Cloudflare timeouts)
setInterval(() => {
    wss.clients.forEach((ws) => {
        if (!ws.isAlive) return ws.terminate();
        ws.isAlive = false;
        ws.ping();
    });
}, 20000);

server.listen(PORT, '127.0.0.1', () => {
    console.log(`\n=================================================`);
    console.log(`  TBS KLINIEK v2 - 2D ISOMETRISCHE WERELD`);
    console.log(`  Lokale server draait op: http://127.0.0.1:${PORT}`);
    console.log(`  Realtime WebSockets actief & klaar voor verbindingen!`);
    console.log(`=================================================\n`);
});

