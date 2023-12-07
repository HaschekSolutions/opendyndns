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
            'clearips' => $this->clearIPs(),
            default => '404',
        };
        return $return;
    }

    private function clearIPs()
    {
        $secret = $_SERVER['HTTP_SECRET'];
        $hostname = preg_replace("/[^A-Za-z0-9-.]/", '', $this->url[2]);
        $data = getHostData($hostname);
        if(!$data['secret']) return 'Invalid hostname';
        if($data['secret'] != $secret) return 'Invalid secret';
        $data['ipv4'] = '';
        $data['ipv6'] = '';
        $data['lastupdated'] = date('Y-m-d H:i:s');
        updateHostname($hostname,$data);
        return 'OK';
    }

    private function setIP()
    {
        $secret = $_SERVER['HTTP_SECRET'];
        $hostname = preg_replace("/[^A-Za-z0-9-.]/", '', $this->url[2]);
        $data = getHostData($hostname);
        if(!$data['secret']) return 'Invalid hostname';
        if($data['secret'] != $secret) return 'Invalid secret';
        $updatedip = [];
        
        if($_REQUEST['ipv4'])
        {
            if(!filter_var($_REQUEST['ipv4'], FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)) return 'Invalid IPv4 provided';
            $data['ipv4'] = $_REQUEST['ipv4'];

            $updatedip[] = $data['ipv4'];
        }
        if($_REQUEST['ipv6'])
        {
            if(!filter_var($_REQUEST['ipv6'], FILTER_VALIDATE_IP,FILTER_FLAG_IPV6)) return 'Invalid IPv6 provided';
            $data['ipv6'] = $_REQUEST['ipv6'];

            $updatedip[] = $data['ipv6'];
        }

        if(!$_REQUEST['ipv4'] && !$_REQUEST['ipv6'])
        {
            // no IP provided, autodetecting
            $ip = getUserIP();
            if(!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return 'Invalid IP '.$ip.' (reserved and private ranges)';

            if(filter_var($ip, FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)) //is the IP an IPv4?
                $data['ipv4'] = $ip;
            else if(filter_var($ip, FILTER_VALIDATE_IP,FILTER_FLAG_IPV6)) //is the IP an IPv6?
                $data['ipv6'] = $ip;

            $updatedip[] = $ip;
        }

        $data['lastupdated'] = date('Y-m-d H:i:s');

        

        updateHostname($hostname,$data);
        return 'OK'.(count($updatedip)>0?', updated '.implode(', ',$updatedip):'');
    }
}