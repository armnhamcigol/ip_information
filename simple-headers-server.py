#!/usr/bin/env python3
import json
import os
from http.server import HTTPServer, BaseHTTPRequestHandler
from urllib.parse import urlparse

class HeadersHandler(BaseHTTPRequestHandler):
    def do_GET(self):
        if self.path == '/api/headers':
            # Set response headers
            self.send_response(200)
            self.send_header('Content-Type', 'application/json')
            self.send_header('Access-Control-Allow-Origin', '*')
            self.end_headers()
            
            # Collect headers
            headers = {}
            for header, value in self.headers.items():
                headers[header] = value
            
            # Collect server info
            server_info = {
                'REQUEST_METHOD': self.command,
                'REQUEST_URI': self.path,
                'SERVER_PROTOCOL': self.request_version,
                'REMOTE_ADDR': self.client_address[0],
                'REMOTE_PORT': str(self.client_address[1]),
                'SERVER_NAME': self.headers.get('Host', 'localhost'),
                'SERVER_PORT': '8080',
                'HTTPS': 'No',  # This will be proxied through nginx HTTPS
                'REQUEST_TIME': '2025-07-11T19:00:00Z'
            }
            
            response_data = {
                'headers': headers,
                'server_info': server_info,
                'timestamp': '2025-07-11T19:00:00Z'
            }
            
            # Send JSON response
            self.wfile.write(json.dumps(response_data, indent=2).encode())
        else:
            self.send_error(404)

if __name__ == '__main__':
    server = HTTPServer(('0.0.0.0', 8080), HeadersHandler)
    print("Headers server running on port 8080")
    server.serve_forever()
