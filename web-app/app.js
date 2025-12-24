const http = require('http');
const url = require('url');

const server = http.createServer((req, res) => {
    const requestUrl = url.parse(req.url, true);
    
    // Collect connection information
    const connectionInfo = {
        timestamp: new Date().toISOString(),
        method: req.method,
        url: req.url,
        path: requestUrl.pathname,
        query: requestUrl.query,
        headers: req.headers,
        remoteAddress: req.connection.remoteAddress,
        remotePort: req.connection.remotePort,
        localAddress: req.connection.localAddress,
        localPort: req.connection.localPort,
        clientIP: req.headers['x-forwarded-for'] || req.connection.remoteAddress,
        userAgent: req.headers['user-agent'] || 'Unknown',
        host: req.headers.host || 'Unknown',
        protocol: req.headers['x-forwarded-proto'] || 'http'
    };
    
    if (requestUrl.pathname === '/json') {
        // JSON response
        res.writeHead(200, {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*'
        });
        res.end(JSON.stringify(connectionInfo, null, 2));
    } else {
        // HTML response
        const html = generateHTML(connectionInfo);
        res.writeHead(200, {
            'Content-Type': 'text/html'
        });
        res.end(html);
    }
});

function generateHTML(info) {
    const formatHeaders = (headers) => {
        return Object.entries(headers)
            .map(([key, value]) => ${key}: )
            .join('\\n');
    };
    
    return <!DOCTYPE html>
<html>
<head>
    <title>Connection Information</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; text-align: center; margin-bottom: 30px; }
        .info-section { margin: 25px 0; }
        .info-title { font-weight: bold; color: #666; margin-bottom: 10px; border-bottom: 2px solid #eee; padding-bottom: 5px; font-size: 1.1em; }
        .info-content { background: #f9f9f9; padding: 15px; border-radius: 5px; font-family: monospace; white-space: pre-wrap; border-left: 4px solid #007cba; }
        .timestamp { text-align: center; color: #999; font-size: 0.9em; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
        .highlight { background-color: #fff3cd; padding: 2px 4px; border-radius: 3px; }
        .json-link { text-align: center; margin: 20px 0; }
        .json-link a { color: #007cba; text-decoration: none; padding: 10px 20px; border: 2px solid #007cba; border-radius: 5px; }
        .json-link a:hover { background-color: #007cba; color: white; }
    </style>
    <script>
        function refreshPage() { location.reload(); }
        setInterval(refreshPage, 30000);
    </script>
</head>
<body>
    <div class= container>
        <h1>🌐 Connection Information Display</h1>
        
        <div class=json-link>
            <a href=/json target=_blank>View JSON API</a>
        </div>
        
        <div class=info-section>
            <div class=info-title>🔍 Client Information</div>
            <div class=info-content>Client IP: <span class=highlight></span>
Remote Address: :
X-Forwarded-For: </div>
        </div>
        
        <div class=info-section>
            <div class=info-title>🌐 Request Information</div>
            <div class=info-content>Method: 
URL: 
Path: 
Protocol: 
Host: 
User Agent: </div>
        </div>
        
        <div class=info-section>
            <div class=info-title>📡 HTTP Headers</div>
            <div class=info-content></div>
        </div>
        
        <div class=info-section>
            <div class=info-title>🖥️ Server Information</div>
            <div class=info-content>Server Address: :</div>
        </div>
        
        <div class=timestamp>🕒 Generated at:  | Auto-refreshes every 30 seconds</div>
    </div>
</body>
</html>;
}

const PORT = 5000;
server.listen(PORT, '0.0.0.0', () => {
    console.log(\Server running on port \\);
});
