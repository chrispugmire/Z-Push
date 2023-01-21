<?php
require "backend/imap/myover.php";

$client = myover_open("192.168.1.51",143,"test1@small.com","Mytest2","/notls");

$ov = myoverview($client,"INBOX","100:110");

foreach ($ov as $overview) {
	echo var_dump($overview),"\n";
}


?>
