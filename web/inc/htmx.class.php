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
            'advanceddns' => $this->advancedDNS(),
            default => '404',
        };
        return $return;
    }

    private function advancedDNS(){
        $hostname = $_REQUEST['hostname']?:$this->url[1];
        $domain = $_REQUEST['domain']?:$this->url[2];
        $fulldomain = $hostname.'.'.$domain;
        if(!in_array($domain,explode(',', DOMAINS))) return error('Invalid domain');
        if(!preg_match('/^[a-z0-9-.]+$/',$hostname)) return error('Invalid hostname');
        $hostdata = getHostData($fulldomain);
        if($_REQUEST['submit'])
        {
            $new_hostname = $_REQUEST['new_hostname'];
            $new_type = $_REQUEST['new_type'];
            $new_value = addslashes($_REQUEST['new_value']);
            // $new_ttl = $_REQUEST['new_ttl'];
            // $new_priority = $_REQUEST['new_priority'];

            if(!preg_match('/^[a-z0-9-.]+$/',$new_hostname)) return error('Invalid hostname');
            if(!in_array($new_type,['A','AAAA','CNAME','MX','TXT','SRV','NS','CAA'])) return error('Invalid type');
            // if(!preg_match('/^[0-9]+$/',$new_ttl)) return error('Invalid TTL');
            // if(!preg_match('/^[0-9]+$/',$new_priority)) return error('Invalid priority');

            $hostdata['advanceddns'][] = [
                'hostname'=>$new_hostname,
                'type'=>$new_type,
                'value'=>$new_value,
                // 'ttl'=>$new_ttl,
                // 'priority'=>$new_priority
            ];
            updateHostname($fulldomain,$hostdata);
        }

        return renderTemplate('advanced_dns.html',[
            'hostname'=>$hostname,
            'domain'=>$domain,
            'fulldomain'=>$fulldomain,
            'hostdata'=>$hostdata
        ]);
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