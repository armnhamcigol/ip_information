FROM php:8.2-fpm-alpine

RUN apk add --no-cache wget && \
    date -s "$(wget -qSO- --max-redirect=0 google.com 2>&1 | grep Date: | cut -d' ' -f 5-8)Z" && \
    apk add --no-cache tzdata && \
    cp /usr/share/zoneinfo/America/Chicago /etc/localtime && \
    echo "America/Chicago" > /etc/timezone

WORKDIR /var/www/html

CMD ["php-fpm"]

