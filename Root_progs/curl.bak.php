<?php

$url = "http://libis-t-rosetta-1.libis.kuleuven.be:8983/solr";
$process = curl_init($url);
$fp = fopen("/www/libis/vol03/collectiveaccess/crkc_media/example_homepage.txt", "w");
curl_setopt($process, CURLOPT_FILE, $fp);
curl_setopt($process, CURLOPT_PROXY, 'icts-http-gw.cc.kuleuven.be:8080');
/* curl_setopt($process, CURLOPT_HEADER, 0);
curl_setopt($process,CURLOPT_ENCODING , $this->compression);
curl_setopt($process, CURLOPT_TIMEOUT, 30);
if ($this->proxy) curl_setopt($process, CURLOPT_PROXY, $this->proxy);		
if ($this->proxy) curl_setopt($process,CURLOPT_PROXYPORT, 8080); 
//		if ($this->proxy) curl_setopt($process, CURLOPT_HTTPPROXYTUNNEL, 1);
curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($process, CURLOPT_FOLLOWLOCATION, 0); */
$return = curl_exec($process);
echo "return: " . $return;
curl_close($process);	
fclose($fp);

?>
