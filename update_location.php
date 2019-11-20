<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
require 'db.php';
require 'check_password.php';
$tz = 'Europe/Riga'; //timezone
$dt = new DateTime("now", new DateTimeZone($tz)); //now time -30 minutes
$now_time = $dt->format("Y-m-d H:i:s");

if(!empty($_POST)) {
	$sth = $conn->prepare("INSERT INTO markers 
						   (id, user_name, color, geolocation_lat, geolocation_lng, speed, accuracy, last_activity, game) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
						   ON DUPLICATE KEY UPDATE 
						   user_name = ?, color = ?, geolocation_lat = ?, geolocation_lng = ?, speed = ?, accuracy = ?, last_activity = ?, game = ?");

	#Execute the insert
	$sth->execute(array($_POST['id'], $_POST['user_name'], $_POST['color'], $_POST['geolocation_lat'], $_POST['geolocation_lng'], $_POST['speed'], $_POST['accuracy'], $now_time, $_POST['game'],
						$_POST['user_name'], $_POST['color'], $_POST['geolocation_lat'], $_POST['geolocation_lng'], $_POST['speed'], $_POST['accuracy'], $now_time, $_POST['game']));

	echo json_encode("location updated");
} else {
	echo json_encode("no location update");
}


?>