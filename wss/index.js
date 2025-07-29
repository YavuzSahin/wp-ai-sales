const fs = require('fs');
const https = require('https');
const WebSocket = require('ws');
const express = require('express');
const bodyParser = require('body-parser');

const server = https.createServer({
    cert: fs.readFileSync('/home/bariatric/ssl/certs/bariatricistanbul_com_tr.crt'),
    key: fs.readFileSync('/home/bariatric/ssl/keys/bariatricistanbul.key')
});

const wss = new WebSocket.Server({ server });

wss.on('connection', function connection(ws) {
    console.log('✅ Yeni WebSocket bağlantısı');
    ws.send(JSON.stringify({ type: 'system', message: 'WebSocket bağlantısı başarılı 🎉' }));
});

const app = express();
app.use(bodyParser.json());

app.post('/broadcast', (req, res) => {
    const payload = req.body;
    wss.clients.forEach(client => {
        if (client.readyState === WebSocket.OPEN) {
            client.send(JSON.stringify(payload));
        }
    });
    console.log('📢 Broadcast gönderildi:', payload);
    res.status(200).json({ status: 'ok' });
});

server.on('request', app);
server.listen(9443, () => {
    console.log('🚀 WSS sunucusu hazır: https://wss.bariatricistanbul.com.tr:9443');
});
