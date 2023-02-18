<?php
# Get all unblocked active users in this game
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
require 'db.php';
require 'check_password.php';
require_once 'utils.php';

# get markers only if last_activity < 10 minutes
$update_age = 60 * 60;
$tz = 'Europe/Riga'; //timezone

$dt = new DateTime("now", new DateTimeZone($tz)); //now time
$now_time = $dt->format("Y-m-d H:i:s");

$me = check_super_admin();

if ($me["type"] !== "admin") {
	$not_blocked_clause = "AND id NOT IN (SELECT user_id FROM blocked B WHERE B.game = M.game AND B.user_id = M.id)";	
} else {
	$not_blocked_clause = "";
}

$stmt = "SELECT id, user_name, color, TIME_TO_SEC(TIMEDIFF(?, last_activity)) AS last_activity, 
		                ROUND(speed, 1) as speed, is_prey, accuracy
		         FROM markers M 
		         WHERE game = ? AND TIME_TO_SEC(TIMEDIFF(?, last_activity)) < ? 
				       ".$not_blocked_clause;
$q = $conn->prepare($stmt);
$q->execute(array($now_time, $_GET['game'], $now_time, $update_age));
$result = $q->setFetchMode(PDO::FETCH_ASSOC);
$result = $q->fetchAll();

$x = array();
foreach ($result as $key => $array) {	
	$x[$array['id']] = $array;			
	$x[$array['id']]['accuracy'] = floatval($array['accuracy']);
	$x[$array['id']]['last_activity'] = intval($array['last_activity']);		
	$x[$array['id']]['color'] = strval($array['color']);
	$x[$array['id']]['speed'] = floatval($array['speed']);
}
	
echo json_encode($x);

?>