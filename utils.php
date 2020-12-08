<?php
require_once 'db.php';

function get_game_state($game) {
	// get current time
	$tz = 'Europe/Riga'; //timezone
	$dt = new DateTime("now", new DateTimeZone($tz)); //now time	

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

?>