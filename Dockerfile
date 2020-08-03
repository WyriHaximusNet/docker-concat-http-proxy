FROM wyrihaximusnet/php:7.4-zts-alpine3.12-dev-root AS install-dependencies

WORKDIR /opt/app
RUN mkdir /opt/app/vendor

COPY ./proxy/composer.lock /opt/app/composer.lock
COPY ./proxy/composer.json /opt/app/composer.json
RUN composer install --ansi --no-interaction --prefer-dist --no-dev -o

FROM wyrihaximusnet/php:7.4-zts-alpine3.12-root AS runtime

COPY proxy/composer.json /composer.json
COPY proxy/composer.lock /composer.lock
COPY proxy/proxy.php /proxy.php
COPY --from=install-dependencies /opt/app/vendor/ /vendor/

ENTRYPOINT ["php", "/proxy.php"]
