from flask import Flask, request, jsonify, render_template_string
import json
import datetime

app = Flask(__name__)

HTML_TEMPLATE = '''
<!DOCTYPE html>
<html>
<head>
    <title>Connection Information</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; text-align: center; }
        .info-section { margin: 20px 0; }
        .info-title { font-weight: bold; color: #666; margin-bottom: 10px; border-bottom: 2px solid #eee; padding-bottom: 5px; }
        .info-content { background: #f9f9f9; padding: 15px; border-radius: 5px; font-family: monospace; white-space: pre-wrap; }
        .timestamp { text-align: center; color: #999; font-size: 0.9em; margin-top: 20px; }
    </style>
</head>
<body>
    <div class=\ container\>
        <h1>🌐 Connection Information Display</h1>
        
        <div class=\info-section\>
            <div class=\info-title\>Client IP Address</div>
            <div class=\info-content\>{{ client_ip }}</div>
        </div>
        
        <div class=\info-section\>
            <div class=\info-title\>X-Forwarded-For</div>
            <div class=\info-content\>{{ x_forwarded_for }}</div>
        </div>
        
        <div class=\info-section\>
            <div class=\info-title\>HTTP Headers</div>
            <div class=\info-content\>{{ headers | safe }}</div>
        </div>
        
        <div class=\info-section\>
            <div class=\info-title\>Request Information</div>
            <div class=\info-content\>{{ request_info | safe }}</div>
        </div>
        
        <div class=\timestamp\>Generated at: {{ timestamp }}</div>
    </div>
</body>
</html>
'''

@app.route('/')
def connection_info():
    # Get client IP
    client_ip = request.headers.get('X-Forwarded-For', request.remote_addr)
    if ',' in client_ip:
        client_ip = client_ip.split(',')[0].strip()
    
    # Get X-Forwarded-For header
    x_forwarded_for = request.headers.get('X-Forwarded-For', 'Not present')
    
    # Format headers
    headers_formatted = ''
    for key, value in request.headers:
        headers_formatted += f'{key}: {value}\\n'
    
    # Request information
    request_info = f'''Method: {request.method}
URL: {request.url}
Path: {request.path}
Query String: {request.query_string.decode()}
User Agent: {request.headers.get('User-Agent', 'Not provided')}
Accept: {request.headers.get('Accept', 'Not provided')}
Accept-Language: {request.headers.get('Accept-Language', 'Not provided')}
Accept-Encoding: {request.headers.get('Accept-Encoding', 'Not provided')}
Connection: {request.headers.get('Connection', 'Not provided')}
'''
    
    # Current timestamp
    timestamp = datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S UTC')
    
    return render_template_string(
        HTML_TEMPLATE,
        client_ip=client_ip,
        x_forwarded_for=x_forwarded_for,
        headers=headers_formatted,
        request_info=request_info,
        timestamp=timestamp
    )

@app.route('/json')
def connection_info_json():
    # JSON API endpoint
    client_ip = request.headers.get('X-Forwarded-For', request.remote_addr)
    if ',' in client_ip:
        client_ip = client_ip.split(',')[0].strip()
    
    connection_details = {
        'timestamp': datetime.datetime.now().isoformat(),
        'client_ip': client_ip,
        'x_forwarded_for': request.headers.get('X-Forwarded-For'),
        'remote_addr': request.remote_addr,
        'method': request.method,
        'url': request.url,
        'path': request.path,
        'query_string': request.query_string.decode(),
        'headers': dict(request.headers),
        'user_agent': request.headers.get('User-Agent'),
        'accept': request.headers.get('Accept'),
        'accept_language': request.headers.get('Accept-Language'),
        'accept_encoding': request.headers.get('Accept-Encoding')
    }
    
    return jsonify(connection_details)

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)
