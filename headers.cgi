#!/bin/bash

# Set content type for JSON response
echo "Content-Type: application/json"
echo "Access-Control-Allow-Origin: *"
echo ""

# Start JSON output
echo "{"
echo "  \"headers\": {"

# Output HTTP headers from environment variables
first=true
for var in $(env | grep "^HTTP_" | sort); do
    if [ "$first" = false ]; then
        echo ","
    fi
    first=false
    
    # Extract header name and value
    header_name=$(echo "$var" | cut -d'=' -f1 | sed 's/HTTP_//' | sed 's/_/-/g')
    header_value=$(echo "$var" | cut -d'=' -f2-)
    
    # Output as JSON
    echo -n "    \"$header_name\": \"$header_value\""
done

# Add common CGI environment variables
echo ""
echo "  },"
echo "  \"server_info\": {"
echo "    \"REQUEST_METHOD\": \"${REQUEST_METHOD:-Unknown}\","
echo "    \"REQUEST_URI\": \"${REQUEST_URI:-Unknown}\","
echo "    \"SERVER_PROTOCOL\": \"${SERVER_PROTOCOL:-Unknown}\","
echo "    \"REMOTE_ADDR\": \"${REMOTE_ADDR:-Unknown}\","
echo "    \"REMOTE_PORT\": \"${REMOTE_PORT:-Unknown}\","
echo "    \"SERVER_NAME\": \"${SERVER_NAME:-Unknown}\","
echo "    \"SERVER_PORT\": \"${SERVER_PORT:-Unknown}\","
echo "    \"HTTPS\": \"${HTTPS:-No}\","
echo "    \"REQUEST_TIME\": \"$(date -Iseconds)\""
echo "  },"
echo "  \"timestamp\": \"$(date -Iseconds)\""
echo "}"
