ARG ENV="prod"
ARG PHP_VERSION="8.1"

FROM base as dev

ARG ENV="prod"
ARG COMPOSER_SCENARIO=""

RUN apk --no-cache add \
    nano \
    optipng \
    jpegoptim

RUN curl -sSLf \
        -o /usr/local/bin/install-php-extensions \
        https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions \
    && chmod +x /usr/local/bin/install-php-extensions \
    && install-php-extensions  \
      pcntl \
      apcu \
      amqp \
      pdo_mysql \
      zip \
      sockets \
      gd \
      igbinary \
      soap \
      imap \
      imagick \
      bcmath \
    && rm /usr/local/bin/install-php-extensions

ADD docker/webserver/php/imagickpolicy.xml /etc/ImageMagick-7/policy.xml

RUN apk --no-cache add xvfb-run
RUN mv /bin/wkhtmltopdf /usr/local/bin/wkhtmltopdf

RUN apk --no-cache add nodejs yarn npm

RUN npm install -g uglify-js
RUN npm install -g uglifycss

RUN chown www-data:www-data . .

RUN mkdir -p /data/nfs/attachments && chown www-data:www-data /data/nfs/attachments
RUN mkdir -p /data/nfs_read_only/attachments && chown www-data:www-data /data/nfs_read_only/attachments

USER www-data

ADD composer.json composer.lock ./

RUN composer install --no-scripts --no-cache --no-autoloader

ADD --chown=www-data:www-data . .

RUN composer dump-autoload --optimize

RUN set -a; . ./.env.dist; set +a; XDEBUG_MODE=off bin/console cache:warmup --env=${ENV} --no-optional-warmers $([ "${ENV}" = 'prod' ] && echo --no-debug)
RUN set -a; . ./.env.dist; set +a; XDEBUG_MODE=off bin/console assets:install --env=${ENV}
RUN set -a; . ./.env.dist; set +a; XDEBUG_MODE=off bin/console assetic:dump --env=${ENV}


FROM base as yarn

ADD package.json yarn.lock ./

RUN yarn install --frozen-lockfile

RUN if [ "${ENV}" = 'prod' ]; then yarn build; else yarn dev; fi


FROM base as prod

ARG IMPORT_TRANSLATIONS=false

COPY --from=yarn /var/www/html/web/build /var/www/html/web/build

RUN echo 'autoload -U colors && colors' >> ~/.zshrc
RUN echo -e 'PS1="%{$fg[red]%}zir-store%{$reset_color%} ${PS1}"' >> ~/.zshrc

RUN if [ "$IMPORT_TRANSLATIONS" = true ]; then ( \
      bin/console translation:generate -c --env=prod \
    ); fi


FROM base as dev

COPY --from=yarn /var/www/html/node_modules /var/www/html/node_modules
COPY --from=yarn /var/www/html/web/build /var/www/html/web/build
