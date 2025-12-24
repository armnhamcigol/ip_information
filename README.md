# IP Information Website

A simple web application that displays connection and IP information for visitors.

## Overview

This project provides a webpage that shows detailed information about the visitor's connection, including:
- Client IP address
- Proxy detection (X-Forwarded-For, X-Real-IP, etc.)
- Zscaler corporate proxy detection
- All HTTP headers received by the server
- Request metadata (method, URL, timestamp)

## Architecture

Simple Go web server with embedded HTML template. No external dependencies required.

## Installation

### Prerequisites
- Go 1.16 or higher

### Running Locally

1. Clone the repository:
```bash
git clone https://github.com/armnhamcigol/ip_information.git
cd ip_information
```

2. Run the server:
```bash
go run main.go
```

3. Open your browser to `http://localhost:8080`

### Building

```bash
go build -o ip-info main.go
./ip-info
```

## API Endpoints

- `GET /` - HTML interface showing connection information
- `GET /api` - JSON API returning connection data
- `GET /health` - Health check endpoint

## Features

- Display client IP address
- Detect and display proxy information
- Identify Zscaler corporate proxy connections
- Show all HTTP headers
- JSON API for programmatic access
- CORS-enabled API
- Health check endpoint for monitoring

## Configuration

The server runs on port 8080 by default. To change the port, modify the `ListenAndServe` call in `main.go`.

## License

Personal project
