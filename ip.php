<?php
$host = '127.0.0.1';
try {
  $DBH = new PDO("mysql:host=$host;dbname=loogares_new;",'root', 'L0ogar3s'); 
  $DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );  
}  
catch(PDOException $e) {  
    echo $e->getMessage();
    file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);  
}

function ip2int($ip)
{
    //Localhost ipv6 mac fix
    if($ip == '::1') { $ip = "31.201.0.176"; }
    if ($ip == "") {
        return null;
    } else {
        $ips = explode (".", "$ip");
        return ($ips[3] + $ips[2] * 256 + $ips[1] * 256 * 256 + $ips[0] * 256 * 256 * 256);
    }
}

$_GET['ip'] = filter_var($_GET['ip'], FILTER_SANITIZE_STRING); 

$ip = ip2int($_GET['ip']);

$STH = $DBH->query("select * from ip2loc where range_to >= $ip LIMIT 1");
$STH->setFetchMode(PDO::FETCH_ASSOC);

while($row = $STH->fetch()){
  $json['country'] = $row['country'];
  $json['code'] = $row['country_code'];
}

return(json_encode($json));
?>