<?php

function restartDNSMASQ() {
    exec("sudo /etc/restart-dnsmasq.sh");
    error_log("[i] Restarted dnsmasq");
}

function updateHostname($hostname,$config)
{
    $file = ROOT.DS.'..'.DS.'data'.DS."$hostname.conf";
    $data = "# ".json_encode($config);
    if($config['ipv4'])
        $data.= "\naddress=/$hostname/".$config['ipv4'];
    if($config['ipv6'])
        $data.= "\naddress=/$hostname/".$config['ipv6'];

    var_dump($config['advanceddns']);

    if($config['advanceddns'])
        foreach($config['advanceddns'] as $entry)
        {
            switch($entry['type']){
                case 'TXT':
                    $data.= "\ntxt-record=".$entry['hostname'].".$hostname,".$entry['value'];
                break;
                case 'CNAME':
                    $data.= "\ncname=".$entry['hostname'].".$hostname,".$entry['value'];
                break;
            }
        }

    if(!file_put_contents($file, $data))exit('Failed to write to file');
    //time to restart the service?
    if($config['ipv4'] || $config['ipv6'])
        restartDNSMASQ();
    error_log("[i] Updated hostfile for $hostname");
}

function getUserIP()
{
    if($_SERVER['HTTP_CF_CONNECTING_IP'])
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
	$client  = @$_SERVER['HTTP_CLIENT_IP'];
	$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
	$remote  = $_SERVER['REMOTE_ADDR'];
	
    if(strpos($forward,','))
    {
        $a = explode(',',$forward);
        $forward = trim($a[0]);
    }
	if(filter_var($forward, FILTER_VALIDATE_IP))
	{
		$ip = $forward;
	}
    elseif(filter_var($client, FILTER_VALIDATE_IP))
	{
		$ip = $client;
	}
	else
	{
		$ip = $remote;
	}
	return $ip;
}

/**
 * Check if a given IPv4 or IPv6 is in a network
 * @param  string $ip    IP to check in IPV4 format eg. 127.0.0.1
 * @param  string $range IP/CIDR netmask eg. 127.0.0.0/24, or 2001:db8::8a2e:370:7334/128
 * @return boolean true if the ip is in this range / false if not.
 * via https://stackoverflow.com/a/56050595/1174516
 */
function isIPInRange( $ip, $range ) {

    if(strpos($range,',')!==false)
    {
        // we got a list of ranges. splitting
        $ranges = array_map('trim',explode(',',$range));
        foreach($ranges as $range)
            if(isIPInRange($ip,$range)) return true;
        return false;
    }
    // Get mask bits
    list($net, $maskBits) = explode('/', $range);

    // Size
    $size = (strpos($ip, ':') === false) ? 4 : 16;

    // Convert to binary
    $ip = inet_pton($ip);
    $net = inet_pton($net);
    if (!$ip || !$net) {
        throw new InvalidArgumentException('Invalid IP address');
    }

    // Build mask
    $solid = floor($maskBits / 8);
    $solidBits = $solid * 8;
    $mask = str_repeat(chr(255), $solid);
    for ($i = $solidBits; $i < $maskBits; $i += 8) {
        $bits = max(0, min(8, $maskBits - $i));
        $mask .= chr((pow(2, $bits) - 1) << (8 - $bits));
    }
    $mask = str_pad($mask, $size, chr(0));

    // Compare the mask
    return ($ip & $mask) === ($net & $mask);
}

function escape($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function renderTemplate($templatename,$variables=[])
{
    ob_start();
    if(is_array($variables))
        extract($variables);
    if(file_exists(ROOT.DS.'templates'.DS.$templatename.'.php'))
        include(ROOT.DS.'templates'.DS.$templatename.'.php');
    else if(file_exists(ROOT.DS.'templates'.DS.$templatename))
        include(ROOT.DS.'templates'.DS.$templatename);
    $rendered = ob_get_contents();
    ob_end_clean();

    return $rendered;
}

function getVersion()
{
    if(file_exists(ROOT.DS.'..'.DS.'VERSION'))
        return trim(file_get_contents(ROOT.DS.'..'.DS.'VERSION'));
    else return '';
}

function error($text)
{
    return renderTemplate('error.html',['errormessage'=>$text]);
}

function getHostData($hostname,$createifnotexist=false)
{
    $file = ROOT.DS.'..'.DS.'data'.DS."$hostname.conf";
    if(!file_exists($file) && $createifnotexist===false) return [];
    else if(!file_exists($file) && $createifnotexist===true)
    {
        $hostdata = ['secret'=>bin2hex(random_bytes(32))];
        updateHostname($hostname,$hostdata);
        $hostdata['dynamicallycreated'] = true;
        return $hostdata;
    }
    $lines = explode("\n",file_get_contents($file));
    
    $json = json_decode(substr($lines[0],2),true);
    return $json;
}
