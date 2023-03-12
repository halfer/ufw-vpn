FROM php:8.1-alpine

RUN apk update && apk upgrade

WORKDIR /root

# Install Composer here
COPY docker/install/composer.sh /tmp/composer-install.sh
RUN sh /tmp/composer-install.sh
RUN ls -l /root
RUN /root/composer.phar --version

# Create symlink for Composer
RUN ln -s /root/composer.phar /usr/bin/composer

# Prefer Bash
RUN apk add bash

WORKDIR /project

CMD "/bin/sh"
