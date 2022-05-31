<?php
header('Access-Control-Allow-Origin: *');  
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	session_start();
	if(!isset($_SESSION["user"]) || $_SESSION["user"]["type"] != "admin") {
		http_response_code(401);
		exit;
	}
	
	if (empty($_GET['id'])) {
		$stmt = "INSERT INTO articles (title, text) VALUES (?,?)";
		$vars=array($_POST['title'], $_POST['text']);
	} else {
		$stmt = "UPDATE articles SET title=?, text=?, date=? WHERE id=?";
		$vars=array($_POST['title'], $_POST['text'], $_POST['date'], $_GET['id']);
	}
	$q = $conn->prepare($stmt);
	$q->execute($vars);
} else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
	session_start();
	if(!isset($_SESSION["user"]) || $_SESSION["user"]["type"] != "admin") {
		http_response_code(401);
		exit;
	}	
	
	$stmt = "DELETE FROM articles WHERE id=?";
	$q = $conn->prepare($stmt);
	$q->execute(array($_GET['id']));
} else {
	$stmt = "SELECT *
	         FROM articles
	         ORDER BY date DESC
	         LIMIT 10";
	$q = $conn->prepare($stmt);
	$q->execute();
	$info = $q->setFetchMode(PDO::FETCH_ASSOC);
	$info = $q->fetchAll();

	echo json_encode($info);
}