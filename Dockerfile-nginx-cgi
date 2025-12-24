FROM nginx:alpine

# Install fcgiwrap and spawn-fcgi
RUN apk add --no-cache fcgiwrap spawn-fcgi bash

# Create fcgiwrap socket directory
RUN mkdir -p /var/run/fcgiwrap

# Create startup script
RUN echo '#!/bin/sh' > /start.sh && \
    echo 'spawn-fcgi -s /var/run/fcgiwrap.socket -M 666 -f fcgiwrap &' >> /start.sh && \
    echo 'nginx -g "daemon off;"' >> /start.sh && \
    chmod +x /start.sh

CMD ["/start.sh"]
