<?php
require_once 'db.php';

if (!empty($_POST)) {
	$id = $_POST['id'];
	$game = $_POST['game'];
} else {
	$id = $_GET['id'];
	$game = $_GET['game'];
}

$stmt = "SELECT user_id FROM admins WHERE user_id = ? AND game = ?";
$q = $conn->prepare($stmt);
$q->execute(array($id, $game));
$result = $q->setFetchMode(PDO::FETCH_ASSOC);
$result = $q->fetch();

if (empty($result)) {
	http_response_code(403);
	exit();
}

?>