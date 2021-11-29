<?php
/**
 * Calculates the great-circle distance between two points, with
 * the Haversine formula.
 */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
require 'db.php';
require 'check_password.php';
require 'utils.php';

# get markers only if last_activity < 20 minutes
$update_age = 60 * 20;
$tz = 'Europe/Riga'; //timezone

$dt = new DateTime("now", new DateTimeZone($tz)); //now time
$now_time = $dt->format("Y-m-d H:i:s");

$stmt = "SELECT geolocation_lat, geolocation_lng FROM markers WHERE game = ? AND id = ?";
$q = $conn->prepare($stmt);
$q->execute(array($_GET['game'],$_GET['id']));
$result = $q->setFetchMode(PDO::FETCH_ASSOC);
$result = $q->fetch();
$prey_lat = $result['geolocation_lat'];
$prey_lng = $result['geolocation_lng'];

$radius = get_prey_lockin_distance($_GET['game']);

$stmt = "SELECT 
  `user_name`, 
   ( 6371000 * acos( cos( radians(?) ) * cos( radians( `geolocation_lat` ) ) 
   * cos( radians(`geolocation_lng`) - radians(?)) + sin(radians(?)) 
   * sin( radians(`geolocation_lat`)))) AS distance 
FROM markers M
WHERE is_prey = 0 AND TIME_TO_SEC(TIMEDIFF(?, last_activity)) < ? AND game = ? AND id NOT IN (SELECT user_id FROM blocked B WHERE B.game = M.game AND B.user_id = M.id)
HAVING distance < ?
LIMIT 1";
$q = $conn->prepare($stmt);
$q->execute(array($prey_lat, $prey_lng, $prey_lat, $now_time, $update_age, $_GET['game'], $radius));
$result = $q->setFetchMode(PDO::FETCH_ASSOC);
$result = $q->fetch();

if (!empty($result)) {
  echo json_encode(true);
} else {
  echo json_encode(false);
}

?>