#!/bin/ash

echo 'Starting Open DynDNS'

cd /var/www/opendyndns

echo ' [+] Starting php'
php-fpm81

echo ' [+] Starting nginx'
nginx

echo ' [+] Starting dnsmasq'
if [ "$DNS_OPENRESOLVE" = "true" ]; then
    echo ' [+] Allowing openresolve'
    echo 'server=1.1.1.1' >> /etc/dnsmasq.conf
    echo 'server=1.0.0.1' >> /etc/dnsmasq.conf
fi
dnsmasq

echo ' [+] Setting up config.ini'

# permission corrections
chmod 0440 /etc/sudoers

_buildConfig() {
    echo "<?php"
    echo "define('DOMAINS','${DOMAINS:-localhost}');"
    echo "define('URL','${URL:-http://localhost:8080}');"
    echo "define('ALLOW_PRIVATE_IP',${ALLOW_PRIVATE_IP:-false});"
    echo "define('ALLOW_DYNAMIC_CREATION',${ALLOW_DYNAMIC_CREATION:-true});"
    echo "define('NO_SECRET',${NO_SECRET:-false});"
}



_buildConfig > web/inc/config.inc.php

tail -f logs/*.log