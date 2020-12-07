<?php
header('Access-Control-Allow-Origin: *');  
require 'db.php';
require 'check_password.php';

# get markers only if last_activity < 10 minutes
$update_age = 60 * 10;
$tz = 'Europe/Riga'; //timezone

$dt = new DateTime("now", new DateTimeZone($tz)); //now time
$now_time = $dt->format("Y-m-d H:i:s");

#am i prey? 
$stmt = "SELECT is_prey, (TIME_TO_SEC(TIMEDIFF(?, last_activity)) < 120) AS updated,
                geolocation_lat, geolocation_lng
         FROM markers WHERE game = ? AND id = ?";
$q = $conn->prepare($stmt);
$q->execute(array($now_time,$_GET['game'],$_GET['id']));
$my_info = $q->setFetchMode(PDO::FETCH_ASSOC);
$my_info = $q->fetch();

#am i blocked?
$stmt = "SELECT user_id FROM blocked WHERE game = ? AND user_id = ?";
$q = $conn->prepare($stmt);
$q->execute(array($_GET['game'],$_GET['id']));
$blocked = $q->setFetchMode(PDO::FETCH_ASSOC);
$blocked = $q->fetch();

if($my_info['updated'] == '0') {
	$blocked = $_GET['id'];
} 
if(empty($blocked)) {	
	$stmt = "SELECT creator, game_time, waiting_time, start_time, end_game, radius FROM games WHERE name = ?";
    $q = $conn->prepare($stmt);
    $q->execute(array($_GET['game']));
    $game_info = $q->setFetchMode(PDO::FETCH_ASSOC);
    $game_info = $q->fetch();	

	if ($my_info['is_prey'] == '1') {
		$stmt = "SELECT id, user_name, color, geolocation_lat, geolocation_lng, TIME_TO_SEC(TIMEDIFF(?, last_activity)) AS last_activity, 
		                ROUND(speed, 1) as speed, is_prey, accuracy 
		         FROM markers M 
		WHERE game = ? AND is_prey = ? AND TIME_TO_SEC(TIMEDIFF(?, last_activity)) < ? AND id NOT IN (SELECT user_id FROM blocked B WHERE B.game = M.game AND B.user_id = M.id)";
		$q = $conn->prepare($stmt);
		$q->execute(array($now_time, $_GET['game'], '1', $now_time, $update_age));
		$result = $q->setFetchMode(PDO::FETCH_ASSOC);
		$result = $q->fetchAll();

	} else {		
		$stmt = "SELECT id, user_name, color, geolocation_lat, geolocation_lng, TIME_TO_SEC(TIMEDIFF(?, last_activity)) AS last_activity, 
		                ROUND(speed, 1) as speed, is_prey, accuracy,
						( 6371000 * acos( cos( radians(?) ) * cos( radians( `geolocation_lat` ) ) 
                          * cos( radians(`geolocation_lng`) - radians(?)) + sin(radians(?)) 
                          * sin( radians(`geolocation_lat`)))) as distance
		         FROM markers M 
		         WHERE game = ? AND TIME_TO_SEC(TIMEDIFF(?, last_activity)) < ? 
				       AND id NOT IN (SELECT user_id FROM blocked B WHERE B.game = M.game AND B.user_id = M.id)";
		$q = $conn->prepare($stmt);				
		$q->execute(array($now_time, $my_info["geolocation_lat"], $my_info["geolocation_lng"], 
		                  $my_info["geolocation_lat"], $_GET['game'], $now_time, $update_age));        
		$result = $q->setFetchMode(PDO::FETCH_ASSOC);
		$result = $q->fetchAll();		
	}

	$x = array();
    
	foreach ($result as $key => $array) {
		if (intval($array['distance']) > $game_info["radius"] && $array['is_prey'] == '1') {
			$distance_to_prey = intval($array['distance']);
		} else {
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
				$distance_to_prey = intval($array['distance']);
			}
		}
	}
	
	# my distance to prey
	$x[$_GET['id']]['distance'] = $distance_to_prey;

	$markers = json_encode($x);
	echo "$markers";
} else {
	$result = [];
	echo json_encode($result);
}
?>