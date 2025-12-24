# IP Information Website

A self-hosted web application that displays connection and IP information, running on Raspberry Pi with Docker.

## Overview

This project provides a webpage that shows detailed information about the visitor's connection, including IP address, headers, and other network details. It's designed to run in a Docker container behind an Nginx reverse proxy with SSL/TLS support.

## Architecture

- **Backend**: Go application (main.go) that handles HTTP requests and returns connection information
- **Frontend**: HTML pages with connection information display
- **Web Server**: Nginx serving as reverse proxy with SSL/TLS termination
- **Deployment**: Docker Compose orchestration

## Files

- `main.go` - Go backend application
- `docker-compose.yml` - Main Docker Compose configuration
- `go.Dockerfile` - Dockerfile for Go application
- `html/` - Frontend HTML files
- `nginx/` - Nginx configuration files
- Various alternative implementations (Node.js, PHP, Python) for reference

## Deployment

The application runs on port 443 (HTTPS) using Let's Encrypt certificates.

See `docker-compose.yml` for the complete container setup.

## Features

- Display client IP address
- Show HTTP headers
- Zscaler detection and handling
- SSL/TLS encryption

## License

Personal project
