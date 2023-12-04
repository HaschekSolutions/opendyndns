<?php

function restartDNSMASQ() {
    exec("sudo /etc/restart-dnsmasq.sh");
}

function setIP($hostname,$ip){
    $file = ROOT.DS.'..'.DS.'data'.DS.'configs'.DS."$hostname.conf";
    $data = "address=/$hostname/$ip";
    file_put_contents($file, $data);
}