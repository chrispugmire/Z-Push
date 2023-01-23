<?php
require_once('backend/imap/handmadeimap.php');


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
	$receiveddate = str_replace('"', "", $receiveddate);
	$receiveddate = substr($receiveddate,0,27);
	$receivedtime = strtotime(preg_replace('/\(.*\)/', "", $receiveddate));
	date_default_timezone_set($oldtimezone);
	if ($receivedtime === false || $receivedtime == -1) {
	    return 0;
	}
	return $receivedtime;
}
function mytoken($str)
{
	$tokens = [];
	$token = strtok($str,' ()');

	while ($token) {
		if (substr($token,0,1)=='"') { $token .= ' '.strtok('"').'"'; }
		if (substr($token,0,1)=="'") { $token .= ' '.strtok("'")."'"; }
		$tokens[] = $token;
		$token = strtok(' ()');
	}
	return $tokens;
}

function nextline_timed($c,$tout): string {
	$line = "";
	$next_char = "";
	$data = '';
	$stR = array($c);
	$stW = null;
	while (is_resource($c) && !feof($c)) {
		if (!stream_select($stR, $stW, $stW, $tout)) {
			return "";
		}
		$next_char = fread($c, 1);
		if ($next_char===false) return "";
		if ($next_char==="\n") break;
		$line .= $next_char;
		
	}
	if ($line === "\n" && $next_char === false) {
		return "";
	}
	return $line;
}
function myover_open($mailserver,$port,$user,$password,$op)
{
		$connection = handmadeimap_open_connection($mailserver, $port);
		if ($connection==null) {
			ZLog::Write(LOGLEVEL_INFO, "Connection failed: ".handmadeimap_get_error());
			return null;
		}
		handmadeimap_login($connection, $user, $password);
		if (!handmadeimap_was_ok()) {
			ZLog::Write(LOGLEVEL_INFO, "Login failed: ".handmadeimap_get_error());
			return null;
		}
		return $connection;
}
function myover_close($client)
{
	handmadeimap_close_connection($client);
}


function myidle($client,$foldername,$tout)
{
	try {
	$r = false;
	$info = handmadeimap_select($client, $foldername);
	if (!$info) return false;

	$cmdid = handmadeimap_send_command($client,"IDLE");
	while (true) {
			$line = nextline_timed($client,$tout);			
			if ($line=="") {
				break;
			} else if (($pos = strpos($line, "EXISTS")) !== false) {
				$r = true;
				break;
			} 
	}
	fwrite($client,"done\r\n");
    $fetchresult = handmadeimap_get_command_result($client, $cmdid);
	return $r;

	} catch (Exception $e) { // For PHP 7
		echo $e->getMessage();
		ZLog::Write(LOGLEVEL_INFO, $e->getMessage());
		return false;
	}
}

function handmadeimap_fetch_flags($connection, $range)
{
    $fetchcommand = "UID FETCH ".$range." (UID INTERNALDATE RFC822.SIZE FLAGS)";
    $fetchid = handmadeimap_send_command($connection, $fetchcommand);
    $fetchresult = handmadeimap_get_command_result($connection, $fetchid);
    $fetchwasok = handmadeimap_was_command_ok($fetchresult['resultline']);
    if (!$fetchwasok)
        handmadeimap_set_error("FETCH failed with '".$fetchresult['resultline']."'");
    else
        handmadeimap_set_error(null);
    
    $result = array();
    foreach ($fetchresult['infolines'] as $infoline)
        $result[] = $infoline;
    
    return $result;
}


function myoverview($client,$folder,$range)
{
   // $max_imap_size = MAX_MSG_SIZE*1000000;  // THIS LIMITS THE SIZE OF MESSAGES, WHICH PREVENTS OUT OF MEMORY ISSUE... 

    $max_imap_size = 10000000;	
	$ret = array();
	$info = handmadeimap_select($client, $folder);

	if (!$info) return false;
	$n = intval($info["totalcount"]);
	if ($n==0) return $ret;

	$msgs = handmadeimap_fetch_flags($client, $range);

	// "* 28 FETCH (UID 100 FLAGS (\Seen))"
	foreach ($msgs as $m) {
		$x = new MINFO();
		$inuid = $inflags = $insize = $inudate = false;
		$words = mytoken($m);
		$x->seen = 0;
		$x->recent = 0;
		$x->deleted = 0;
		$x->answered = 0;
		$x->flagged = 0;
		$sz = 0;
		foreach ($words as $w) {
			if ($w=="FLAGS") {$inflags = true; continue;} 
			if ($w=="UID") {$inuid = true; continue;}
			if ($w=="RFC822.SIZE") {$insize = true; continue;}
			if ($w=="INTERNALDATE") {$inudate = true; continue;}
			if ($inuid) {$x->uid = intval(str_replace('"', "",$w)); $inuid = false;}
			if ($inudate) {$x->udate = cleanupDate($w); $inudate= false;}
			if ($w=="\Seen") $x->seen = 1;
			if ($w=="\Recent") $x->recent = 1;
			if ($w=="\Deleted") $x->deleted = 1;
			if ($w=="\Answered") $x->answered = 1;
			if ($w=="\Flagged") $x->flagged = 1;
			if ($insize) {$sz = intval($w); $insize = false;} 

		}
		if ($sz>$max_imap_size) {
			continue;
		}	
		array_push($ret,$x);
	}
	return $ret;
}
?>