<?php
require_once 'db.php';

function now_time() {
  $tz = 'Europe/Riga'; //timezone
  $dt = new DateTime("now", new DateTimeZone($tz)); //now time
  
  return $dt;
}

function get_game_state($game) {
	// get current time
	$dt = now_time();

	if ($game["end_game"] == '0') { //if not end of the game		
        $time_diff = ($dt->getTimestamp() - intval($game["start_time"])); //time from game start
        if ((intval($game["waiting_time"])*60) >= $time_diff) { //if still is waiting time          
          return "waiting";
        } else { //game is on          
          return "inprogress";
        }             
      } else {
        return "nogame";
      } 
}

function get_prey_lockin_distance($game_id) {
  return 500;
}

function get_prey_acc_scale($game_id) {
  return 0.8;
}

function get_game_info($game_id) {
	global $conn;

	$stmt = "SELECT * FROM games WHERE name = ?";
    $q = $conn->prepare($stmt);
    $q->execute(array($game_id));
    $game_info = $q->setFetchMode(PDO::FETCH_ASSOC);
    $game_info = $q->fetch();

    $game_info["state"] = get_game_state($game_info);

    return $game_info;
}

function get_marker_info($marker_id) {
  global $conn;

  $now_time = now_time()->format("Y-m-d H:i:s");

  $stmt = "SELECT *, (TIME_TO_SEC(TIMEDIFF(?, last_activity)) < 120) AS updated, 
                  TIME_TO_SEC(TIMEDIFF(?, last_activity)) AS age
         FROM markers WHERE id = ?";
  $q = $conn->prepare($stmt);
  $q->execute(array($now_time, $now_time, $marker_id));
  $info = $q->setFetchMode(PDO::FETCH_ASSOC);
  $info = $q->fetch();
  return $info;
}

function distance_on_geoid($lat1, $lon1, $lat2, $lon2) {
 
  // Convert degrees to radians
  $lat1 = $lat1 * pi() / 180.0;
  $lon1 = $lon1 * pi() / 180.0;
 
  $lat2 = $lat2 * pi() / 180.0;
  $lon2 = $lon2 * pi() / 180.0;
 
  // radius of earth in metres
  $r = 6378100;
 
  // P
  $rho1 = $r * cos($lat1);
  $z1 = $r * sin($lat1);
  $x1 = $rho1 * cos($lon1);
  $y1 = $rho1 * sin($lon1);
 
  // Q
  $rho2 = $r * cos($lat2);
  $z2 = $r * sin($lat2);
  $x2 = $rho2 * cos($lon2);
  $y2 = $rho2 * sin($lon2);
 
  // Dot product
  $dot = ($x1 * $x2 + $y1 * $y2 + $z1 * $z2);
  $cos_theta = $dot / ($r * $r);
 
  $theta = acos($cos_theta);
 
  // Distance in Metres
  return $r * $theta;
}

function calculate_average_speed($lat1, $lon1, $lat2, $lon2, $age) {
  $dist = distance_on_geoid($lat1, $lon1, $lat2, $lon2);
  $time_s = $age;
  $speed_mps = $dist / $time_s;
  $speed_kph = ($speed_mps * 3600.0) / 1000.0;

  return $speed_kph;
}

?>