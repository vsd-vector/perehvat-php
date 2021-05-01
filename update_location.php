<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
require 'db.php';
require 'check_password.php';
require 'utils.php';

$dt = now_time();
$now_time = $dt->format("Y-m-d H:i:s");

$my_info = get_marker_info($_POST["id"]);

if ($my_info) {
    $avg_speed = calculate_average_speed($my_info['geolocation_lat'],$my_info['geolocation_lng'], $_POST['geolocation_lat'], $_POST['geolocation_lng'], $my_info['age']);
} else {
	$avg_speed = $_POST['speed'];
}

if(!empty($_POST)) {
	$sth = $conn->prepare("INSERT INTO markers 
						   (id, user_name, color, geolocation_lat, geolocation_lng, speed, avg_speed, accuracy, last_activity, game) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
						   ON DUPLICATE KEY UPDATE 
						   user_name = ?, color = ?, geolocation_lat = ?, geolocation_lng = ?, speed = ?, avg_speed = ?, accuracy = ?, last_activity = ?, game = ?");

	#Execute the insert
	$sth->execute(array($_POST['id'], $_POST['user_name'], $_POST['color'], $_POST['geolocation_lat'], $_POST['geolocation_lng'], $_POST['speed'], $avg_speed, 
		                $_POST['accuracy'], $now_time, $_POST['game'],
						$_POST['user_name'], $_POST['color'], $_POST['geolocation_lat'], $_POST['geolocation_lng'], $_POST['speed'], $avg_speed, 
						$_POST['accuracy'], $now_time, $_POST['game']));

	echo json_encode("location updated");
} else {
	echo json_encode("no location update");
}


?>