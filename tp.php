#!/usr/bin/env php

<?php 

require_once('HttpRequest.php');

$url = 'https://2ip.ru';
$hr = new HttpRequest();
$content = $hr->setUrl($url)->setProxyServer('62.109.2.4:3129','Buffa4ok', 'lAs5ok7y')->send();
$matches = null;
preg_match('/<big id="d_clip_button".*/', $content, $matches);

if (is_array($matches) && isset($matches[0])){
	echo $matches[0];
}else{
	echo 'Nothing found' . PHP_EOL;
	var_dump($content);
	echo $hr->getLastError() . PHP_EOL; 
}
	



