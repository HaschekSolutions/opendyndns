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
            default => '404',
        };
        return $return;
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