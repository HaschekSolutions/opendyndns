<?php

// URL to this service
define('URL', 'http://localhost:8080');

// comma separated list of domains you want to allow (eg have a DNS entry for)
define('DOMAINS', 'example.com,example.org,sub.example.com');

// if set to true, the API will allow you to set private IPs
// this would allow you to use this service to update your local DNS
define('ALLOW_PRIVATE_IP', false);

// if set to true, the API will allow you to create new hostnames
// on the fly while calling the API. This is useful if you want to
// automatically create hostnames for your local network without using the web interface
define('ALLOW_DYNAMIC_CREATION', true);

// if set to true, you can use all API calls without a needing a secret
// secrets will still be created, just not checked
// please only use this in local environments
define('NO_SECRET', false);