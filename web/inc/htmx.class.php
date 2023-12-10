<?php 

class HTMX {
    private $url;
    function __construct($url)
    {
        $this->url = $url;
    }

    function act()
    {
        if($this->url[0]=='htmx')
            array_shift($this->url);
        $return = match ($this->url[0]) {
            'host' => $this->renderHost(),
            'updateip' => $this->updateIP(),
            default => '404',
        };
        return $return;
    }

    private function updateIP(){
        $hostname = $_REQUEST['hostname']?:$this->url[1];
        $ip = getUserIP();
        if(!$_SESSION[$hostname]) return error('Invalid session');
        $hostdata = getHostData($hostname);
        if(filter_var($ip, FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)) //is the IP an IPv4?
            $hostdata['ipv4'] = $ip;
        else if(filter_var($ip, FILTER_VALIDATE_IP,FILTER_FLAG_IPV6)) //is the IP an IPv6?
            $hostdata['ipv6'] = $ip;
        $hostdata['lastupdated'] = date('Y-m-d H:i:s');
        updateHostname($hostname,$hostdata);

        return '
        <label>IPv4: '.($hostdata['ipv4']?:'Not set').'</label>
        <label>IPv6: '.($hostdata['ipv6']?:'Not set').'</label>
        ';

    }

    private function renderHost()
    {
        $hostname = $_REQUEST['hostname']?:$this->url[1];
        $domain = $_REQUEST['domain']?:$this->url[2];
        $fulldomain = $hostname.'.'.$domain;
        if(!in_array($domain,explode(',', DOMAINS))) return error('Invalid domain');
        if(!preg_match('/^[a-z0-9-.]+$/',$hostname)) return error('Invalid hostname');
        header('HX-Push-Url: /host/'.$hostname.'/'.$domain);
        $hostdata = getHostData($fulldomain);
        if($_REQUEST['savedata'])
        {
            $password = $_REQUEST['password'];
            $note = $_REQUEST['note'];

            $hostdata['password'] = $password;
            $hostdata['note'] = $note;
            updateHostname($fulldomain,$hostdata);
        }

        if(!$hostdata['secret'])
        {
            $hostdata['secret'] = bin2hex(random_bytes(32));
            updateHostname($fulldomain,$hostdata);
        }

        return renderTemplate('host.html',[
            'hostname'=>$hostname,
            'domain'=>$domain,
            'fulldomain'=>$fulldomain,
            'hostdata'=>$hostdata,
            'url'=>URL
        ]);
    }
}