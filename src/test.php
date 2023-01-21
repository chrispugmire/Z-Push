<?php
require "backend/imap/myover.php";

define('MSG_MAX_SIZE', 2000000);
$client = myover_open("192.168.1.50",143,"test1@small.com","Mytest2","/notls");

$ov = myoverview($client,"INBOX","100:110");

foreach ($ov as $overview) {
	echo var_dump($overview),"\n";
}


?>
