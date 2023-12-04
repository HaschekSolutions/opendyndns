#!/bin/ash

echo 'Starting Open DynDNS'

cd /var/www/opendyndns

echo ' [+] Starting php'
php-fpm81

echo ' [+] Starting nginx'
nginx

echo ' [+] Starting dnsmasq'
dnsmasq

echo ' [+] Setting up config.ini'



_buildConfig() {
    echo "<?php"
    echo "define('DOMAINS','${DOMAINS:-localhost}');"
    echo "define('URL','${URL:-http://localhost:8080}');"
}

_buildConfig > web/inc/config.inc.php

tail -f logs/*.log