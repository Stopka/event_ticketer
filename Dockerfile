ARG DEBIAN_FRONTEND=noninteractive

FROM ubuntu:latest AS build

ENV TZ=Europe/Prague

ENV PHP_VERSION=7.4
ENV PHP_ETC=/etc/php/${PHP_VERSION}
ENV PHP_MODS_DIR=${PHP_ETC}/mods-available
ENV PHP_CLI_DIR=${PHP_ETC}/cli
ENV PHP_CLI_CONF_DIR=${PHP_CLI_DIR}/conf.d
ENV PHP_FPM_DIR=${PHP_ETC}/fpm/
ENV PHP_FPM_CONF_DIR=${PHP_FPM_DIR}/conf.d
ENV PHP_FPM_POOL_DIR=${PHP_FPM_DIR}/pool.d

ENV HOST_NAME='Event ticketer'
ENV HOST_DOMAIN='ticketer.localhost'
ENV MAIL_HOST='localhost'
ENV MAIL_PORT='25'
ENV MAIL_USER=''
ENV MAIL_PASS=''
ENV MAIL_FROM=system@${HOST_DOMAIN}
ENV MAIL_FROM_NAME=${HOST_NAME}
ENV MAIL_REPLY_TO=${MAIL_FROM}
ENV MAIL_REPLY_TO_NAME=${MAIL_FROM_NAME}
ENV FORMAT_DATE='Y-m-d'
ENV FORMAT_TIME='H:i:s'
ENV DATABASE_HOST='database:3306'
ENV DATABASE_NAME='event_ticketer'
ENV DATABASE_USER='root'
ENV DATABASE_PASSWORD=''

RUN \
    # INSTALLATION
    apt-get update && \
    apt-get install -y --no-install-recommends \
        software-properties-common && \
    add-apt-repository ppa:ondrej/php && \
    apt-get install -y --no-install-recommends \
        curl \
        nginx \
        cron \
        supervisor \
        msmtp \
        msmtp-mta \
        unzip \
        php${PHP_VERSION}-apcu \
        php${PHP_VERSION}-bz2 \
        php${PHP_VERSION}-bcmath \
        php${PHP_VERSION}-cli \
        php${PHP_VERSION}-dom \
        php${PHP_VERSION}-curl \
        php${PHP_VERSION}-fpm \
        php${PHP_VERSION}-gd \
        php${PHP_VERSION}-intl \
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-memcached \
        php${PHP_VERSION}-mysql \
        php${PHP_VERSION}-soap \
        php${PHP_VERSION}-sqlite3 \
        php${PHP_VERSION}-zip \
        php${PHP_VERSION}-imagick \
        php${PHP_VERSION}-phpdbg \
        ghostscript && \
    rm -rf /var/lib/apt/lists/* /var/lib/log/* /tmp/* /var/tmp/* \
    # PHP MOD(s) ###############################################################
    rm ${PHP_FPM_POOL_DIR}/www.conf && \
    # NGINX ####################################################################
    ln -sf /dev/stdout /var/log/nginx/access.log && \
    ln -sf /dev/stderr /var/log/nginx/error.log && \
    # MSMTP ####################################################################
    ln -sf /dev/stdout /var/log/msmtp.log
    # CLEAN UP #################################################################
    #rm /etc/nginx/conf.d/default.conf
# PHP
ADD ./docker/php/php-fpm.conf ${PHP_FPM_DIR}/
# NGINX
ADD ./docker/nginx/* /etc/nginx/
ADD ./docker/nginx/sites.d /etc/nginx/sites.d/
# SUPERVISOR
ADD docker/supervisor/supervisord.conf /etc/supervisor/
ADD docker/supervisor/services /etc/supervisor/services/
# MSMTP
ADD ./docker/msmtp/msmtprc.template /etc/
ADD ./docker/msmtp/generate_msmtprc.sh /usr/sbin/
# ENTRY
ADD ./docker/entry/fix_permissions.sh /usr/sbin/
RUN chmod ug+rx /usr/sbin/fix_permissions.sh
# APP
WORKDIR /srv
ADD ./src .
EXPOSE 80
CMD ["supervisord", "--nodaemon", "--configuration", "/etc/supervisor/supervisord.conf"]


FROM build AS libtools
RUN \
    # COMPOSER
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer && \
    # YARN
    apt-get update && \
    apt-get install -y --no-install-recommends \
        gpg-agent && \
    curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - && \
    echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list && \
    apt-get install -y --no-install-recommends \
        yarn \
        git &&\
    rm -rf /var/lib/apt/lists/* /var/lib/log/* /tmp/* /var/tmp/*


FROM libtools AS dev
RUN \
    # INSTALLATION
    apt-get update && \
    apt-get install -y --no-install-recommends \
        php-xdebug \
        vim \
        wget && \
    rm -rf /var/lib/apt/lists/* /var/lib/log/* /tmp/* /var/tmp/*
ADD ./docker/php/xdebug-custom.ini ${PHP_FPM_CONF_DIR}/
ADD ./docker/php/xdebug-custom.ini ${PHP_CLI_CONF_DIR}/

FROM libtools AS libs
RUN \
    composer install && \
    yarn install


FROM build AS test
COPY --from=libs ./srv/vendor ./srv/bin ./
#COPY --from=node ./srv/public/build ./public/build


FROM test AS prod
RUN rm -r ./tests ./node_modules composer* package*
