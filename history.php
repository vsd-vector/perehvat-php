<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');  
require 'db.php';
require 'utils.php';
if ($_GET["password"] !== $history_password) {
  die ("Wrong password");
}
header('Content-Disposition: attachment; filename="history.gpx"');
?><?xml version="1.0" encoding="UTF-8"?>
<gpx
xmlns="http://www.topografix.com/GPX/1/0"
version="1.0"
creator="Perehvat.lv"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation="http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd">
 
 <metadata>
  <name>Perehvat.lv game</name>
  <desc></desc>
  <author>
   <name>Perehvat.lv</name>
  </author>
 </metadata>
 
<?php

$tz = 'Europe/Riga'; //timezone
$utc_tz = new DateTimeZone('UTC');

$timespan_begin = new DateTime($_GET["begin"], new DateTimeZone($tz)); 
$timespan_begin = $timespan_begin->format("Y-m-d H:i:s");

$timespan_end = new DateTime($_GET["end"], new DateTimeZone($tz)); 
$timespan_end = $timespan_end->format("Y-m-d H:i:s");

$game_info = get_game_info($_GET["game"]);

$values = array($_GET['game'], $timespan_begin, $timespan_end);

if (isset($_GET["marker"]) && $_GET["marker"]) {
  $marker_clause = " and marker_id LIKE ?";
  $values[] = $_GET["marker"]."%";
} else {
  $marker_clause = "";
}

$stmt = "SELECT marker_id, user_name, color, geolocation_lat, geolocation_lng, last_activity, 
                ROUND(speed, 1) as speed, is_prey, accuracy				
         FROM tracks M 
         WHERE game = ? and last_activity > ? and last_activity < ? $marker_clause
         ORDER BY marker_id, last_activity ASC";
$q = $conn->prepare($stmt);				
$q->execute($values);
$result = $q->setFetchMode(PDO::FETCH_ASSOC);
$result = $q->fetchAll();		


$x = array();

$id="";
foreach ($result as $key => $array) {	
	if ( $id != $array["marker_id"] ) {
	   if ($id) {
         echo "</trkseg></trk>";         
	   }
	   $id=$array["marker_id"];
       echo "<trk>";
       echo "<name>".htmlspecialchars($array['user_name'])."</name><cmt>".$array['marker_id']."</cmt><trkseg>";
	}
  $time = (new DateTime($array['last_activity'], new DateTimeZone($tz)))->setTimezone($utc_tz)->format(DateTime::ISO8601);  
	echo '<trkpt lat="'.$array['geolocation_lat'].'" lon="'.$array['geolocation_lng'].'">
        <speed>'.$array['speed'].'</speed>        
        <time>'.str_replace("+0000", "Z", $time).'</time>
        <cmt>'.$array['accuracy'].'</cmt>
      </trkpt>';	
}
echo "</trkseg></trk>";

?> 
</gpx>