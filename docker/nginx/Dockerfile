FROM nginx:alpine

ARG DOMAIN

# Copy custom Nginx configuration
# -----------------------------------------------------------
# Replace the default Nginx configuration with our custom one
# that is optimized for serving a Laravel application.
# -----------------------------------------------------------
COPY ./docker/nginx/includes /etc/nginx/includes
COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf

RUN sed -i "s/__DOMAIN__/$DOMAIN/g" /etc/nginx/includes/ssl.conf

# Set the working directory to the public folder
WORKDIR /var/www/public

COPY ./docker/nginx/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/bin/sh", "/usr/local/bin/entrypoint.sh"]

CMD ["nginx", "-g", "daemon off;"]
