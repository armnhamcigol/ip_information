#!/bin/bash

# Script to restore working nginx configuration
# Run this script on the Raspberry Pi if you need to restore the working setup

echo "Stopping current nginx container..."
docker stop connection-info-nginx 2>/dev/null || true
docker rm connection-info-nginx 2>/dev/null || true

echo "Restoring configuration files..."
cd /home/pi/connection-info-site
sudo tar -xzf backup-working-ssl-fixed.tar.gz

echo "Starting nginx container from backup image..."
docker run -d \
  --name connection-info-nginx \
  -p 80:80 \
  -p 443:443 \
  -v /home/pi/connection-info-site/html:/usr/share/nginx/html:rw \
  -v /home/pi/connection-info-site/nginx/default.conf:/etc/nginx/conf.d/default.conf:rw \
  -v /home/pi/connection-info-site/certbot/conf:/etc/letsencrypt:rw \
  -v /home/pi/connection-info-site/certbot/www:/var/www/certbot:rw \
  connection-info-nginx-backup:working-ssl-fixed

echo "Waiting for nginx to start..."
sleep 3

echo "Testing HTTPS connection..."
if curl -k -I https://localhost >/dev/null 2>&1; then
    echo "✅ HTTPS is working!"
    echo "✅ Nginx restored successfully!"
else
    echo "❌ HTTPS test failed. Check docker logs connection-info-nginx"
fi

echo "Container status:"
docker ps | grep connection-info-nginx
