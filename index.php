<?php
header('Access-Control-Allow-Origin: *');  
require 'db.php';
require 'check_password.php';
require 'utils.php';

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
    $game_info = get_game_info($_GET["game"]);

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
				$max_offset = 0;
			}

			if ($array['is_prey'] == '1' // and marker is prey
	   		   && $array['id'] != $_GET["id"] // user should see himself accurately even if he is prey
			   && $game_info["game_type"] == "2" // if is new game version)
			   ) {
			   	$max_offset = ($distance_to_prey - get_prey_lockin_distance($_GET['game'])) * get_prey_acc_scale($_GET['game']);
			   	if ($max_offset < 0) {
			   		$max_offset = 0;
			   	}
			   	// move marker by some random offset
			    $lat_offset = (rand(0, 2*$max_offset) - $max_offset); // random offset for latitude [-max_offset;max_offset]
			    $lat_offset = $lat_offset / 111111; // convert to "latitude degrees"
			    $lng_offset = (rand(0, 2*$max_offset) - $max_offset); // random offset for longtitude [-max_offset;max_offset]
			    $lng_offset = $lng_offset / (111111 * cos(deg2rad($x[$array['id']]['geolocation_lat']))); // convert to degrees 
			    $x[$array['id']]['geolocation_lat'] += $lat_offset;
			    $x[$array['id']]['geolocation_lng'] += $lng_offset;			    
            }
		}
	}
	
	# my distance to prey
	$x[$_GET['id']]['prey_info'] = array("distance" => $distance_to_prey, "last_activity" => $prey_marker_age, "speed" => $prey_speed, "max_offset" => $max_offset);

	$markers = json_encode($x);
	echo "$markers";
} else {
	$result = [];
	echo json_encode($result);
}
?>