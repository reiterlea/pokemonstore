FROM php:8.3-cli

RUN apt-get update && \
    apt-get install -y wget && \
    wget https://get.symfony.com/cli/installer -O - | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pdo_pgsql

WORKDIR /api
COPY . /api

CMD ["symfony", "server:start"]