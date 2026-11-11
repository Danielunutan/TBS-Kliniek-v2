import http from 'http';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { WebSocketServer, WebSocket } from 'ws';
import mysql from 'mysql2/promise';

const dbPool = mysql.createPool({
    host: '127.0.0.1',
    user: 'root',
    password: '',
    database: 'plus',
    waitForConnections: true,
    connectionLimit: 5,
});

async function getFreshTicket(username = 'Daniel') {
    const ticket = 'tbs_sso_' + Math.random().toString(36).substring(2, 12);
    try {
        await dbPool.query('UPDATE users SET auth_ticket = ? WHERE username = ?', [ticket, username]);
        return ticket;
    } catch (e) {
        console.error('Error setting ticket in DB:', e.message);
        return ticket;
    }
}

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const PORT = process.env.PORT || 3000;
const VOLT_DIR = path.join(__dirname, 'volt-client');
const HOTEL_FILES_DIR = path.join(__dirname, 'hotel-files');
const PUBLIC_DIR = path.join(__dirname, 'public');
const ASSETS_DIR = path.join(__dirname, 'assets');

// MIME types for static file serving
const MIME_TYPES = {
    '.html': 'text/html; charset=utf-8',
    '.js': 'text/javascript; charset=utf-8',
    '.css': 'text/css; charset=utf-8',
    '.json': 'application/json; charset=utf-8',
    '.jsonc': 'application/json; charset=utf-8',
    '.png': 'image/png',
    '.jpg': 'image/jpeg',
    '.svg': 'image/svg+xml',
    '.ico': 'image/x-icon',
    '.mp3': 'audio/mpeg',
    '.nitro': 'application/octet-stream',
    '.hab': 'application/octet-stream',
    '.bin': 'application/octet-stream',
    '.wasm': 'application/wasm',
    '.ttf': 'font/ttf',
    '.woff': 'font/woff',
    '.woff2': 'font/woff2',
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
const server = http.createServer(async (req, res) => {
    // Add CORS headers for web client & WebGL textures
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', '*');

    if (req.method === 'OPTIONS') {
        res.writeHead(204);
        res.end();
        return;
    }

    const cleanUrl = req.url.split('?')[0];
    console.log('[HTTP REQ]', req.method, cleanUrl);

    // 1. Root & index.html -> Serve Volt Client with dynamic SSO ticket
    if (cleanUrl === '/' || cleanUrl === '/index.html') {
        const urlObj = new URL(req.url, 'http://127.0.0.1:3000');
        let ticket = urlObj.searchParams.get('sso') || urlObj.searchParams.get('token');
        if (!ticket) {
            ticket = await getFreshTicket('Daniel');
            res.writeHead(302, { Location: `/?sso=${ticket}` });
            res.end();
            return;
        }
        const voltIndexPath = path.join(VOLT_DIR, 'index.html');
        fs.readFile(voltIndexPath, (err, content) => {
            if (err) {
                res.writeHead(500, { 'Content-Type': 'text/plain' });
                res.end('500 Server Fout');
                return;
            }
            res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
            res.end(content);
        });
        return;
    }

    // 2. Check if file exists in VOLT_DIR (client bundle, assets, configuration)
    const voltFilePath = path.join(VOLT_DIR, cleanUrl);
    if (fs.existsSync(voltFilePath) && !fs.statSync(voltFilePath).isDirectory()) {
        const ext = path.extname(voltFilePath).toLowerCase();
        fs.readFile(voltFilePath, (err, content) => {
            if (err) {
                res.writeHead(500, { 'Content-Type': 'text/plain' });
                res.end('500 Server Fout');
                return;
            }
            res.writeHead(200, { 'Content-Type': MIME_TYPES[ext] || 'application/octet-stream' });
            res.end(content);
        });
        return;
    }

    // 3. Check if file exists in HOTEL_FILES_DIR (/hotel-files/...)
    if (cleanUrl.startsWith('/hotel-files/')) {
        const relPath = cleanUrl.replace(/^\/hotel-files\//, '');
        const hotelFilePath = path.join(HOTEL_FILES_DIR, relPath);
        if (fs.existsSync(hotelFilePath) && !fs.statSync(hotelFilePath).isDirectory()) {
            const ext = path.extname(hotelFilePath).toLowerCase();
            fs.readFile(hotelFilePath, (err, content) => {
                if (err) {
                    res.writeHead(500, { 'Content-Type': 'text/plain' });
                    res.end('500 Server Fout');
                    return;
                }
                res.writeHead(200, { 'Content-Type': MIME_TYPES[ext] || 'application/octet-stream' });
                res.end(content);
            });
            return;
        }
    }

    // 4. Fallback to PUBLIC_DIR
    const publicFilePath = path.join(PUBLIC_DIR, cleanUrl);
    if (fs.existsSync(publicFilePath) && !fs.statSync(publicFilePath).isDirectory()) {
        const ext = path.extname(publicFilePath).toLowerCase();
        fs.readFile(publicFilePath, (err, content) => {
            if (err) {
                res.writeHead(500, { 'Content-Type': 'text/plain' });
                res.end('500 Server Fout');
                return;
            }
            res.writeHead(200, { 'Content-Type': MIME_TYPES[ext] || 'application/octet-stream' });
            res.end(content);
        });
        return;
    }

    // 5. Fallback to ASSETS_DIR
    if (cleanUrl.startsWith('/assets/')) {
        const relPath = cleanUrl.replace(/^\/assets\//, '');
        const assetFilePath = path.join(ASSETS_DIR, relPath);
        if (fs.existsSync(assetFilePath) && !fs.statSync(assetFilePath).isDirectory()) {
            const ext = path.extname(assetFilePath).toLowerCase();
            fs.readFile(assetFilePath, (err, content) => {
                if (err) {
                    res.writeHead(500, { 'Content-Type': 'text/plain' });
                    res.end('500 Server Fout');
                    return;
                }
                res.writeHead(200, { 'Content-Type': MIME_TYPES[ext] || 'application/octet-stream' });
                res.end(content);
            });
            return;
        }
    }

    // Return 404 for missing static assets
    console.log('[HTTP 404 Missing]:', cleanUrl);
    res.writeHead(404, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ error: 'Not found', path: cleanUrl }));
});

// Realtime WebSocket Game Server
const wss = new WebSocketServer({ server });
let clientCounter = 0;

function createEvaPacket(headerId, encodeFn = null) {
    const body = [];
    const writer = {
        writeInt: (val) => {
            const buf = Buffer.alloc(4);
            buf.writeInt32BE(Number(val) || 0, 0);
            body.push(buf);
        },
        writeShort: (val) => {
            const buf = Buffer.alloc(2);
            buf.writeInt16BE(Number(val) || 0, 0);
            body.push(buf);
        },
        writeByte: (val) => {
            const buf = Buffer.alloc(1);
            buf.writeUInt8(Number(val) || 0, 0);
            body.push(buf);
        },
        writeBoolean: (val) => {
            const buf = Buffer.alloc(1);
            buf.writeUInt8(val ? 1 : 0, 0);
            body.push(buf);
        },
        writeString: (val) => {
            const str = String(val || '');
            const strBuf = Buffer.from(str, 'utf-8');
            const lenBuf = Buffer.alloc(2);
            lenBuf.writeInt16BE(strBuf.length, 0);
            body.push(lenBuf);
            body.push(strBuf);
        }
    };

    if (encodeFn) encodeFn(writer);

    const bodyBuf = Buffer.concat(body);
    const packet = Buffer.alloc(4 + 2 + bodyBuf.length);
    packet.writeInt32BE(2 + bodyBuf.length, 0);
    packet.writeInt16BE(headerId, 4);
    bodyBuf.copy(packet, 6);
    return packet;
}

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

    ws.on('message', (raw, isBinary) => {
        // 1. Binary EVA Wire Format Packet from Nitro Client
        if (isBinary || (Buffer.isBuffer(raw) && raw.length >= 6)) {
            const buf = Buffer.isBuffer(raw) ? raw : Buffer.from(raw);
            if (buf.length >= 6) {
                const packetLen = buf.readInt32BE(0);
                const headerId = buf.readInt16BE(4);

                console.log(`[Habbo Packet IN] Header: ${headerId}, Len: ${packetLen}`);

                // SECURITY_TICKET (2419) or CLIENT_HELLO (4000)
                if (headerId === 2419 || headerId === 4000) {
                    console.log('[Habbo Packet OUT] Sending AUTHENTICATED (2491)');
                    ws.send(createEvaPacket(2491), { binary: true });
                    return;
                }

                // USER_INFO (357) -> InfoRetrieveMessageComposer
                if (headerId === 357) {
                    console.log('[Habbo Packet OUT] Sending USER_INFO (2725) & CREDITS (3475)');
                    // USER_INFO (2725)
                    ws.send(createEvaPacket(2725, (w) => {
                        w.writeInt(1001); // userId
                        w.writeString('Inmate_402'); // username
                        w.writeString('hr-115-42.hd-190-1.ch-215-62.lg-270-62.sh-290-62'); // figure
                        w.writeString('M'); // gender
                        w.writeString('TBS Kliniek Blok A'); // motto
                        w.writeString('Gedetineerde'); // realName
                        w.writeBoolean(false); // directMail
                        w.writeInt(42); // respectsReceived
                        w.writeInt(3); // respectsRemaining
                        w.writeInt(3); // respectsPetRemaining
                        w.writeBoolean(true); // streamPublishingAllowed
                        w.writeString('2026-11-11'); // lastAccessDate
                        w.writeBoolean(false); // canChangeName
                        w.writeBoolean(false); // safetyLocked
                    }), { binary: true });

                    // USER_CREDITS (3475)
                    ws.send(createEvaPacket(3475, (w) => {
                        w.writeString('2850.0');
                    }), { binary: true });

                    // USER_CURRENCY (2018)
                    ws.send(createEvaPacket(2018, (w) => {
                        w.writeInt(1); // total
                        w.writeInt(0); // duckets
                        w.writeInt(150); // amount
                    }), { binary: true });

                    // USER_PERMISSIONS (411)
                    ws.send(createEvaPacket(411, (w) => {
                        w.writeInt(2); // clubLevel
                        w.writeInt(7); // securityLevel
                        w.writeBoolean(false); // isAmbassador
                    }), { binary: true });

                    // USER_SUBSCRIPTION (954)
                    ws.send(createEvaPacket(954, (w) => {
                        w.writeString('habbo_club');
                        w.writeInt(999);
                        w.writeInt(1);
                        w.writeInt(1);
                        w.writeInt(1);
                        w.writeBoolean(true);
                        w.writeBoolean(true);
                        w.writeInt(100);
                        w.writeInt(100);
                        w.writeInt(999999);
                        w.writeInt(0);
                    }), { binary: true });
                    return;
                }

                // CLIENT_PONG or ping
                if (headerId === 2596) {
                    ws.send(createEvaPacket(2596), { binary: true });
                    return;
                }
            }
        }

        // 2. JSON Packets
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
                        isSmoking: data.isSmoking || false,
                        figure: data.figure || {
                            hair: 'short',
                            hairColor: '#3e2723',
                            skinColor: '#ffd1a4',
                            shirt: 'inmate_orange',
                            pants: 'inmate_orange',
                            shoes: 'black_boots',
                            accessory: 'joint'
                        },
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

                    // Retro Command Parser (:enable, :dance, :push, :pull, :smoke, :lay, :sit, etc.)
                    if (text.startsWith(':') || text.startsWith('/')) {
                        const parts = text.slice(1).split(' ');
                        const cmd = parts[0].toLowerCase();
                        const arg = parts[1];

                        if (cmd === 'enable') {
                            const effId = parseInt(arg, 10) || 0;
                            playerData.effectId = effId;
                            broadcastToRoom(currentRoomId, {
                                type: 'player_effect',
                                playerId: clientId,
                                effectId: effId,
                            });
                            return;
                        }

                        if (cmd === 'dance') {
                            const danceId = parseInt(arg, 10) || 0;
                            playerData.danceId = danceId;
                            broadcastToRoom(currentRoomId, {
                                type: 'player_dance',
                                playerId: clientId,
                                danceId: danceId,
                            });
                            return;
                        }

                        if (cmd === 'lay') {
                            playerData.isLying = !playerData.isLying;
                            if (playerData.isLying) playerData.isSitting = false;
                            broadcastToRoom(currentRoomId, {
                                type: 'player_lay',
                                playerId: clientId,
                                isLying: playerData.isLying,
                            });
                            return;
                        }

                        if (cmd === 'sit') {
                            playerData.isSitting = !playerData.isSitting;
                            if (playerData.isSitting) playerData.isLying = false;
                            broadcastToRoom(currentRoomId, {
                                type: 'player_sit',
                                playerId: clientId,
                                isSitting: playerData.isSitting,
                            });
                            return;
                        }

                        if (cmd === 'smoke') {
                            playerData.isSmoking = !playerData.isSmoking;
                            broadcastToRoom(currentRoomId, {
                                type: 'player_smoke',
                                playerId: clientId,
                                isSmoking: playerData.isSmoking,
                            });
                            return;
                        }

                        if (cmd === 'push' || cmd === 'pull') {
                            const room = rooms[currentRoomId];
                            if (room && arg) {
                                for (let [targetWs, targetPlayer] of room.players.entries()) {
                                    if (targetPlayer.name.toLowerCase() === arg.toLowerCase()) {
                                        let nx = targetPlayer.x;
                                        let ny = targetPlayer.y;
                                        if (cmd === 'push') {
                                            const dx = Math.sign(targetPlayer.x - playerData.x) || 1;
                                            const dy = Math.sign(targetPlayer.y - playerData.y);
                                            nx = Math.max(0, Math.min(room.width - 1, targetPlayer.x + dx));
                                            ny = Math.max(0, Math.min(room.height - 1, targetPlayer.y + dy));
                                        } else {
                                            // Pull towards caller
                                            nx = playerData.x;
                                            ny = playerData.y;
                                        }
                                        targetPlayer.x = nx;
                                        targetPlayer.y = ny;
                                        broadcastToRoom(currentRoomId, {
                                            type: 'player_move',
                                            playerId: targetPlayer.id,
                                            x: nx,
                                            y: ny,
                                            direction: targetPlayer.direction,
                                            isSitting: false,
                                        });
                                        broadcastToRoom(currentRoomId, {
                                            type: 'player_chat',
                                            playerId: clientId,
                                            name: playerData.name,
                                            text: `*${cmd === 'push' ? 'duwt' : 'trekt'} ${targetPlayer.name}*`,
                                        });
                                        return;
                                    }
                                }
                            }
                        }
                    }

                    // Normale chat
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
                    } else if (data.action === 'smoke') {
                        playerData.isSmoking = !playerData.isSmoking;
                        broadcastToRoom(currentRoomId, {
                            type: 'player_smoke',
                            playerId: clientId,
                            isSmoking: playerData.isSmoking,
                        });
                    }
                    break;
                }

                case 'update_figure': {
                    if (!playerData || !currentRoomId) return;
                    playerData.figure = data.figure;
                    playerData.isSmoking = data.isSmoking !== undefined ? data.isSmoking : playerData.isSmoking;
                    broadcastToRoom(currentRoomId, {
                        type: 'player_figure',
                        playerId: clientId,
                        figure: playerData.figure,
                        isSmoking: playerData.isSmoking,
                    });
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

