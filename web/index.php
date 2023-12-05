<?php
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));

require_once(ROOT . DS . 'inc' . DS . 'core.php');
require_once(ROOT . DS . 'inc' . DS . 'htmx.class.php');
require_once(ROOT . DS . 'inc' . DS . 'api.class.php');
require_once(ROOT . DS . 'inc' . DS . 'config.inc.php');

$url = array_filter(explode('/',ltrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),'/')));

if(file_exists(implode('/',$url)))
{
    return false;
}

session_start();

$return = match ($url[0]) {
    'htmx' => (new HTMX($url))->act(),
    'api' => (new API($url))->act(),
    '',NULL => renderTemplate('index.html',[
        'domains'=>explode(',', DOMAINS),
    ]),
    default => '404',
};

echo $return;

// updateHostname('orf.at','192.168.15.1',[
//     'name' => 'ORF',
//     'description' => 'ORF.at',
//     'icon' => 'http://orf.at/favicon.ico',
//     'color' => '#ff0000',
//     'priority' => 1
// ]);

// restartDNSMASQ();