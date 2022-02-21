<?php
header('Access-Control-Allow-Origin: *');  
require 'db.php';

$stmt = "SELECT *
         FROM settings";
$q = $conn->prepare($stmt);
$q->execute();
$info = $q->setFetchMode(PDO::FETCH_ASSOC);
$info = $q->fetchAll();

$settings = array();
foreach ($info as $value) {
    $settings[$value["setting"]] = $value["value"];
}


echo json_encode($settings);