#!/usr/bin/env bash

WRITABLE_DIRS="var public/var";

cd /srv;
chmod -R uog+r .;
chown -R www-data:www-data ${WRITABLE_DIRS};
chmod -R g+rwx ${WRITABLE_DIRS};

#executables
chown root:root \
    /usr/sbin/generate_msmtprc.sh \

chmod ug+x \
    /usr/sbin/generate_msmtprc.sh \
