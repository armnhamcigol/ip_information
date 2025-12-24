#!/bin/bash

# Certificate monitoring script for mccarville.com

DOMAIN="mccarville.com"
LOG_FILE="/var/log/cert-check.log"

echo "[$(date)] Checking certificate status for $DOMAIN" >> $LOG_FILE

# Check certificate expiration
CERT_INFO=$(echo | openssl s_client -servername $DOMAIN -connect $DOMAIN:443 2>/dev/null | openssl x509 -noout -dates 2>/dev/null)

if [ $? -eq 0 ]; then
    echo "[$(date)] Certificate check successful:" >> $LOG_FILE
    echo "$CERT_INFO" >> $LOG_FILE
    
    # Extract expiry date and calculate days remaining
    EXPIRY=$(echo "$CERT_INFO" | grep notAfter | sed  s/notAfter=//)
    EXPIRY_EPOCH=$(date -d "$EXPIRY" +%s)
    NOW_EPOCH=$(date +%s)
    DAYS_LEFT=$(( (EXPIRY_EPOCH - NOW_EPOCH) / 86400 ))
    
    echo "[$(date)] Days until expiration: $DAYS_LEFT" >> $LOG_FILE
    
    if [ $DAYS_LEFT -lt 30 ]; then
        echo "[$(date)] WARNING: Certificate expires in less than 30 days!" >> $LOG_FILE
    fi
else
    echo "[$(date)] ERROR: Could not check certificate" >> $LOG_FILE
fi
EOF
