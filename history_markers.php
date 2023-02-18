<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
header('Access-Control-Allow-Origin: *');  
require 'db.php';
require 'check_password.php';
require 'check_admin.php';
require_once 'utils.php';

# get markers only if last_activity < 10 minutes
$update_age = 60 * 10;
$tz = 'Europe/Riga'; //timezone

$time = $_GET['time'];
$hide_time = $time - $update_age;
$dt = new DateTime("@$time"); //now time
$now_time = $dt->setTimezone(new DateTimeZone($tz))->format("Y-m-d H:i:s");
$dt = new DateTime("@$hide_time"); //hide time
$hide_time = $dt->setTimezone(new DateTimeZone($tz))->format("Y-m-d H:i:s");

#am i prey? 
$stmt = "SELECT is_prey, (TIME_TO_SEC(TIMEDIFF(?, last_activity)) < 120) AS updated,
                geolocation_lat, geolocation_lng
         FROM markers WHERE game = ? AND id = ?";
$q = $conn->prepare($stmt);
$q->execute(array($now_time,$_GET['game'],$_GET['id']));
$my_info = $q->setFetchMode(PDO::FETCH_ASSOC);
$my_info = $q->fetch();

$game_info = get_game_info($_GET["game"]);

if($game_info["state"] == "nogame" && $my_info['is_prey'] != '1') {    
	
	$stmt = "SELECT M.marker_id as id, M.user_name, M.color, M.geolocation_lat, M.geolocation_lng, TIME_TO_SEC(TIMEDIFF(?, M.last_activity)) AS last_activity, 
	                ROUND(M.speed, 1) as speed, M.is_prey, M.accuracy,
					( 6371000 * acos( cos( radians(?) ) * cos( radians( M.geolocation_lat ) ) 
                      * cos( radians(M.geolocation_lng) - radians(?)) + sin(radians(?)) 
                      * sin( radians(M.geolocation_lat)))) as distance
	         FROM tracks M 	         
	         WHERE M.last_activity <= ? AND M.game = ? AND M.last_activity > ? 	               
			       AND M.marker_id NOT IN (SELECT user_id FROM blocked B WHERE B.game = M.game AND B.user_id = M.id) 
			       AND M.id IN (SELECT max(id) from tracks where game=? and last_activity<=? and last_activity>=? GROUP BY marker_id)";    
	$q = $conn->prepare($stmt);
	$q->execute(array($now_time, $my_info["geolocation_lat"], $my_info["geolocation_lng"], 
	                  $my_info["geolocation_lat"], 
	                  $now_time, $_GET['game'], $hide_time,
	                  $_GET['game'], $now_time, $hide_time));
	$result = $q->setFetchMode(PDO::FETCH_ASSOC);
	$result = $q->fetchAll();		

	$x = array();
    
	foreach ($result as $key => $array) {
		if ((intval($array['distance']) > $game_info["radius"]  // if marker is outside radius
		       || $game_info["state"] == "waiting") // or game is in waiting state
			&& $array['is_prey'] == '1' // and marker is prey
			&& $array['id'] != $_GET["id"] // user should see himself even if he is prey
			&& $game_info["game_type"] == "1" // if is new game version
		    ) {
			// then hide the prey marker

            // if game is not in waiting return distance to prey
			if ($game_info["state"] == "waiting") {
				$distance_to_prey = 0;
			} else {
			    $distance_to_prey = intval($array['distance']);
		    }
			$prey_marker_age = intval($array['last_activity']);
			$prey_speed = floatval($array['speed']);
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
				$prey_marker_age = intval($array['last_activity']);
				$prey_speed = floatval($array['speed']);
			}
		}
	}
		
	# my distance to prey
	if (!empty($x) and isset($x[$_GET['id']])) {
	    $x[$_GET['id']]['prey_info'] = array("distance" => $distance_to_prey, "last_activity" => $prey_marker_age, "speed" => $prey_speed);
	}

	$markers = json_encode($x);
	echo "$markers";
} else {
	$result = [];
	echo json_encode($result);
}
?>