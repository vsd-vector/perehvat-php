<?php
header('Access-Control-Allow-Origin: *');  
require 'db.php';
require 'check_password.php';

# get markers only if last_activity < 30 minutes
$update_age = 60 * 30;
$tz = 'Europe/Riga'; //timezone

$dt = new DateTime("now", new DateTimeZone($tz)); //now time
$now_time = $dt->format("Y-m-d H:i:s");

#am i prey? 
$stmt = "SELECT is_prey FROM markers WHERE game = ? AND id = ?";
$q = $conn->prepare($stmt);
$q->execute(array($_GET['game'],$_GET['id']));
$my_role = $q->setFetchMode(PDO::FETCH_ASSOC);
$my_role = $q->fetch();

#am i blocked?
$stmt = "SELECT user_id FROM blocked WHERE game = ? AND user_id = ?";
$q = $conn->prepare($stmt);
$q->execute(array($_GET['game'],$_GET['id']));
$blocked = $q->setFetchMode(PDO::FETCH_ASSOC);
$blocked = $q->fetch();

#last marker update
$stmt = "SELECT (TIME_TO_SEC(TIMEDIFF(?, last_activity)) < 120) AS updated FROM markers WHERE game = ? AND id = ?";
$q = $conn->prepare($stmt);
$q->execute(array($now_time,$_GET['game'],$_GET['id']));
$last_marker_update = $q->setFetchMode(PDO::FETCH_ASSOC);
$last_marker_update = $q->fetch();

if($last_marker_update['updated'] == '0') {
	$blocked = $_GET['id'];
} 
if(empty($blocked)) {
	if ($my_role['is_prey'] == '1') {
		$stmt = "SELECT id, user_name, color, geolocation_lat, geolocation_lng, TIME_TO_SEC(TIMEDIFF(?, last_activity)) AS last_activity, 
		                ROUND(speed, 1) as speed, is_prey, accuracy 
		         FROM markers M 
		WHERE game = ? AND is_prey = ? AND last_activity > ? AND id NOT IN (SELECT user_id FROM blocked B WHERE B.game = M.game AND B.user_id = M.id)";
		$q = $conn->prepare($stmt);
		$q->execute(array($now_time, $_GET['game'], '1', $update_age));
		$result = $q->setFetchMode(PDO::FETCH_ASSOC);
		$result = $q->fetchAll();

	} else {
		$stmt = "SELECT id, user_name, color, geolocation_lat, geolocation_lng, TIME_TO_SEC(TIMEDIFF(?, last_activity)) AS last_activity, 
		                ROUND(speed, 1) as speed, is_prey, accuracy
		         FROM markers M 
		         WHERE game = ? AND last_activity > ? AND id NOT IN (SELECT user_id FROM blocked B WHERE B.game = M.game AND B.user_id = M.id)";
		$q = $conn->prepare($stmt);
		$q->execute(array($now_time,$_GET['game'], $update_age));
		$result = $q->setFetchMode(PDO::FETCH_ASSOC);
		$result = $q->fetchAll();
	}

	$x = array();
    
	foreach ($result as $key => $array) {
		$x[$array['id']] = $array;
		$x[$array['id']]['geolocation_lat'] = floatval($array['geolocation_lat']);
		$x[$array['id']]['geolocation_lng'] = floatval($array['geolocation_lng']);
		$x[$array['id']]['accuracy'] = floatval($array['accuracy']);
		$x[$array['id']]['last_activity'] = intval($array['last_activity']);	
		$x[$array['id']]['color'] = strval($array['color']);
		$x[$array['id']]['speed'] = floatval($array['speed']);
		if ($x[$array['id']]['is_prey'] == '1') {
			$x[$array['id']]['color'] = "#dd0000";
			$x[$array['id']]['user_name'] = "Угонщик";
		}	
	}

	$markers = json_encode($x);
	echo "$markers";
} else {
	$result = [];
	echo json_encode($result);
}
?>