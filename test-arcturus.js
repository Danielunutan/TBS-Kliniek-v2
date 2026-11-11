import WebSocket from 'ws';

const ws = new WebSocket('ws://127.0.0.1:2096');

ws.on('open', () => {
    console.log('Connected to Arcturus WebSocket on 2096!');
    // Send Release version packet (4000)
    const release = 'NITRO-1-6-6';
    const buf = Buffer.alloc(4 + 2 + 2 + release.length);
    buf.writeInt32BE(buf.length - 4, 0);
    buf.writeInt16BE(4000, 4);
    buf.writeInt16BE(release.length, 6);
    buf.write(release, 8);
    ws.send(buf);

    // Send SSO Ticket packet (2419)
    const ticket = 'tbs_test_token';
    const ssoBuf = Buffer.alloc(4 + 2 + 2 + ticket.length + 4);
    ssoBuf.writeInt32BE(ssoBuf.length - 4, 0);
    ssoBuf.writeInt16BE(2419, 4);
    ssoBuf.writeInt16BE(ticket.length, 6);
    ssoBuf.write(ticket, 8);
    ssoBuf.writeInt32BE(0, 8 + ticket.length);
    ws.send(ssoBuf);
});

ws.on('message', (data) => {
    const buf = Buffer.isBuffer(data) ? data : Buffer.from(data);
    const len = buf.readInt32BE(0);
    const header = buf.readInt16BE(4);
    console.log(`Arcturus Response -> Header: ${header}, Len: ${len}`);
});

ws.on('error', (err) => {
    console.error('Error:', err.message);
});

setTimeout(() => {
    ws.close();
    process.exit(0);
}, 2000);

