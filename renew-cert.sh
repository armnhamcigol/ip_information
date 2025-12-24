#!/bin/bash

# Certificate renewal script for mccarville.com
# This script should be run as root via cron

DOMAIN="mccarville.com-0001"
WEBROOT_PATH="/home/pi/connection-info-site/certbot/www"
CERT_PATH="/home/pi/connection-info-site/certbot/conf/live/mccarville.com"
LOG_FILE="/var/log/certbot-renewal.log"

echo "[$(date)] Starting certificate renewal check for $DOMAIN" >> $LOG_FILE

# Check if certificate needs renewal (30 days before expiry)
if /usr/bin/certbot renew --cert-name $DOMAIN --webroot --webroot-path=$WEBROOT_PATH --quiet 2>> $LOG_FILE; then
    echo "[$(date)] Certificate renewal check completed successfully" >> $LOG_FILE
    
    # Copy renewed certificates to docker volume if they were actually renewed
    if [ -f "/etc/letsencrypt/live/$DOMAIN/fullchain.pem" ]; then
        cp -L /etc/letsencrypt/live/$DOMAIN/fullchain.pem $CERT_PATH/
        cp -L /etc/letsencrypt/live/$DOMAIN/privkey.pem $CERT_PATH/
        chmod 644 $CERT_PATH/privkey.pem
        chown -R pi:users /home/pi/connection-info-site/certbot/conf
        echo "[$(date)] Certificates copied to Docker volume" >> $LOG_FILE
        
        # Restart nginx container
        cd /home/pi/connection-info-site && docker compose restart nginx
        echo "[$(date)] Nginx container restarted" >> $LOG_FILE
    fi
    echo "[$(date)] Certificate renewal process completed" >> $LOG_FILE
else
    echo "[$(date)] Certificate renewal failed or was not needed" >> $LOG_FILE
fi
EOF
