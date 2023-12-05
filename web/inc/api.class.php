<?php 

class API {
    private $url;
    function __construct($url)
    {
        $this->url = $url;
    }

    function act()
    {
        $return = match ($this->url[1]) {
            'setip' => $this->setIP(),
            default => '404',
        };
        return $return;
    }

    private function setIP()
    {
        $secret = $_SERVER['HTTP_SECRET'];
        $hostname = preg_replace("/[^A-Za-z0-9-.]/", '', $this->url[2]);
        $data = getHostData($hostname);
        if(!$data['secret']) return 'Invalid hostname';
        if($data['secret'] != $secret) return 'Invalid secret';
        $ip = getUserIP();
        var_dump($_SERVER);
        if(!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return 'Invalid IP (reserved and private ranges)';

        if(filter_var($ip, FILTER_VALIDATE_IP,FILTER_FLAG_IPV4))
            $data['ipv4'] = $ip;
        else if(filter_var($ip, FILTER_VALIDATE_IP,FILTER_FLAG_IPV6))
            $data['ipv6'] = $ip;

        $data['lastupdated'] = date('Y-m-d H:i:s');

        

        updateHostname($hostname,$data);
        return 'OK';
    }
}