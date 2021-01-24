#!/usr/bin/env bash

if [ -z "${MAIL_USER}" ] && [ -z "${MAIL_PASS}" ];
then
MAIL_AUTH="off";
else
MAIL_AUTH="on";
fi

/bin/sed -e "s/<tls>/$MAIL_TLS/" /etc/msmtprc.template |
/bin/sed -e "s/<auth>/$MAIL_AUTH/" |
/bin/sed -e "s/<host>/$MAIL_HOST/" |
/bin/sed -e "s/<port>/$MAIL_PORT/" |
/bin/sed -e "s/<user>/$MAIL_USER/" |
/bin/sed -e "s/<from>/$MAIL_FROM_ADDRESS/" |
/bin/sed -e "s/<password>/$MAIL_PASS/" > /etc/msmtprc;
