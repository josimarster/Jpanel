<?php
// http://shebangme.blogspot.com.br/2010/04/use-php-to-add-iptables-rules-to-block_15.html
date_default_timezone_set ( 'America/Sao_paulo' );
$badguys = array ();
$recent_count = 0;
$older_count = 0;
$dropped = 0;
$start_time = strtotime ( "-2 minute" ); // Change this if the cron interval is different
echo "Looking for failures since " . date ( "m/d/Y h:i:s", $start_time ) . "\n";
/* here is where we go open the log file. (/var/log/secure) in this case. Change that path if your log is located elsewhere */
exec ( "grep -i 'failed password for invalid user' /var/log/secure", $badguys );
echo count ( $badguys ) . " failed password records\n";
$badips = array ();
foreach ( $badguys as $line ) {
	if (strtotime ( substr ( $line, 0, 15 ) ) > $start_time) {
		$recent_count ++;
		preg_match ( '/from ([0-9.]*)/', $line, $match );
		$ip = $match [1];
		// echo "$ip\n";
		if (array_key_exists ( $ip, $badips ))
			$badips [$ip] ++;
		else
			$badips [$ip] = 1;
	} else {
		$older_count ++;
	}
}
echo $recent_count . " failures occured in last minute.\n";
echo $older_count . " failures were prior to that.\n";
if (count ( $badips ) > 0) {
	// echo "Here are the ip's to be added to the drop list:\n";
	foreach ( $badips as $ip => $count ) {
		echo "$count failed attempts from $ip\n";
		/* here, we are saying if there were more than 5 bad password attempts in one minute, then go add that ip to the currently running firewall script. Change this number if you think you yourself could possibly screwup logging in more that 5 times in a single minute. */
		if ($count > 5) {
			exec ( "iptables -I INPUT -s $ip -j DROP" );
			/*
			 * Note that adding the rule in this way does not make the change permanent, you are not adding it to your startup script, just to the running config. If you want to permanently block those IP addresses, you will need to go to a command shell, and issue the following command:
			 * # service iptables save
			 * or add this command to this script at the very end:
			 * exec("service iptables save");
			 * without one of those commands, the IP blocking effect will be lost at reboot.
			 */
			echo "dropping $ip\n";
			$dropped ++;
		}
	}
}
echo $dropped . " ips were dropped.\n";

?>
