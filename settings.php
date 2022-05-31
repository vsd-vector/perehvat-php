<?php
header('Access-Control-Allow-Origin: *');  
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	session_start();
	if(!isset($_SESSION["user"]) || $_SESSION["user"]["type"] != "admin") {
		http_response_code(401);
		exit;
	}
	$settings = json_decode($_POST['settings']);	
	
	foreach ($settings as $key => $value) {
		$stmt = "UPDATE settings SET value=? WHERE setting=?";		
		$q = $conn->prepare($stmt);
		$q->execute(array($value, $key));
	}
} else {
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
}