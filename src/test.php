<?php
require "backend/imap/myover.php";

define('MAX_MSG_SIZE',20);
$client = myover_open("192.168.1.50",143,"test1@small.com","Mytest2","/notls");

// Idle test...
if (myidle($client,"INBOX",30)) {
	echo "myidle returned true\n";
} else echo "myidle returned false\n";

echo "done\n";


/* over view test...
$ov = myoverview($client,"INBOX","100:110");

foreach ($ov as $overview) {
	echo var_dump($overview),"\n";
}
*/

?>
