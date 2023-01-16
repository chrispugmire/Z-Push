<?php
require_once('vendor/autoload.php');
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\IMAP;

class MINFO {
	public $uid;
	public $udate;
	public $seen,$recent,$deleted,$answered,$flagged;
}
function cleanupDate($receiveddate) {
	if (is_array($receiveddate)) {
	    // Header Date could be repeated in the message, we only check the first
	    $receiveddate = $receiveddate[0];
	}
	$oldtimezone = date_default_timezone_get();
	date_default_timezone_set('UTC');
	$receiveddate = substr($receiveddate,0,27);
	$receivedtime = strtotime(preg_replace('/\(.*\)/', "", $receiveddate));
	date_default_timezone_set($oldtimezone);
	if ($receivedtime === false || $receivedtime == -1) {
	    return 0;
	}
	return $receivedtime;
}
function myoverview($host,$port,$user,$pass,$folder,$range,$op)
{
    $max_imap_size = 10000000;  // THIS LIMITS THE SIZE OF MESSAGES, WHICH PREVENTS OUT OF MEMORY ISSUE... 
	$ret = array();
	$cm = new ClientManager($options = []);
	$enc = "tls";
	if (str_contains($op,"/notls")) $enc = "false";
	if ($port==993) $enc = "ssl";

	$client = $cm->make([
	    'host'          => $host,
	    'port'          => $port,
	    'encryption'    => $enc,
	    'validate_cert' => false,
	    'username'      => $user,
	    'password'      => $pass,
	    'protocol'      => 'imap'
	]);

// cheat as real function fails with bad return type.
//	$client->connection->setConnectionTimeout(60);
//$this->connection->setConnectionTimeout($timeout);

	//Connect to the IMAP Server
	try {
		$client->connect();
	} catch (Exception $e) {
                ZLog::Write(LOGLEVEL_INFO, sprintf("Unable to scan folder %s %s",$folder,$e->getMessage()));
		return $ret;
	} 
	$client->openFolder($folder,false);

	// NOT USING RANGE YET!! CHANGE TO UID SEARCH...
	$msgs = $client->connection->fetch(["FLAGS","INTERNALDATE","RFC822.SIZE","UID"],explode(",",$range),null,IMAP::ST_UID); // st_uid == serch based on uid number...

	foreach ($msgs as $m) {
//echo var_dump($m);
		$sz = intval($m["RFC822.SIZE"]); // this will fail if the case of responses is wrong... crap. 
		if ($sz>$max_imap_size) {
 	               ZLog::Write(LOGLEVEL_INFO, sprintf("Dropped message too big for php mime %s %s",$folder,$m["UID"]) );
			continue;
		}	
//echo "size was ",$sz,"\n";
		$x = new MINFO();
		$x->uid = intval($m["UID"]); 
		$x->udate = cleanupDate($m["INTERNALDATE"]);
		$x->seen = 0;
		$x->recent = 0;
		$x->deleted = 0;
		$x->answered = 0;
		$x->flagged = 0;
		foreach ($m["FLAGS"] as $w) {
			if ($w=="\Seen") $x->seen = 1;
			if ($w=="\Recent") $x->recent = 1;
			if ($w=="\Deleted") $x->deleted = 1;
			if ($w=="\Answered") $x->answered = 1;
			if ($w=="\Flagged") $x->flagged = 1;
		}
		array_push($ret,$x);

	}
	$client->disconnect();
	return $ret;
}
?>