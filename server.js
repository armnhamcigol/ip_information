const express = require('express');
const https = require('https');
const http = require('http');
const fs = require('fs');
const path = require('path');

const app = express();

// Serve static files from the html directory
app.use(express.static('/app/html'));

// API endpoint to capture HTTP headers
app.get('/api/headers', (req, res) => {
    const headers = req.headers;
    const serverInfo = {
        REQUEST_METHOD: req.method,
        REQUEST_URI: req.originalUrl,
        SERVER_PROTOCOL: `HTTP/${req.httpVersion}`,
        REMOTE_ADDR: req.ip || req.connection.remoteAddress,
        REMOTE_PORT: req.socket.remotePort,
        SERVER_NAME: req.hostname,
        SERVER_PORT: req.secure ? '443' : '80',
        HTTPS: req.secure ? 'Yes' : 'No',
        REQUEST_TIME: new Date().toISOString()
    };

    res.json({
        headers: headers,
        server_info: serverInfo,
        timestamp: new Date().toISOString()
    });
});

// Redirect HTTP to HTTPS
app.use((req, res, next) => {
    if (!req.secure && req.get('x-forwarded-proto') !== 'https') {
        return res.redirect(301, `https://${req.get('host')}${req.url}`);
    }
    next();
});

// SSL certificate paths
const sslOptions = {
    key: fs.readFileSync('/etc/letsencrypt/live/mccarville.com/privkey.pem'),
    cert: fs.readFileSync('/etc/letsencrypt/live/mccarville.com/fullchain.pem')
};

// Start HTTPS server
const httpsServer = https.createServer(sslOptions, app);
httpsServer.listen(443, () => {
    console.log('HTTPS Server running on port 443');
});

// Start HTTP server for redirects
const httpServer = http.createServer((req, res) => {
    res.writeHead(301, {
        Location: `https://${req.headers.host}${req.url}`
    });
    res.end();
});

httpServer.listen(80, () => {
    console.log('HTTP Server running on port 80 (redirects to HTTPS)');
});

module.exports = app;
