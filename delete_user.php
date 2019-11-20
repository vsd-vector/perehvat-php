<?php
#ADMIN ONLY. Admin sets new prey for the game
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
require 'db.php';
require 'check_password.php';
require 'check_admin.php';

# am i creator of the game?
$stmt = "SELECT creator FROM games WHERE name = ?";
$q = $conn->prepare($stmt);
$q->execute(array($_POST['game']));
$creator = $q->setFetchMode(PDO::FETCH_ASSOC);
$creator = $q->fetch();

if($creator['creator'] != $_POST['user_id']) {
	$sth = $conn->prepare("INSERT INTO blocked SET user_id = ?, game = ?");
	$sth->execute(array($_POST['user_id'],$_POST['game']));

	$sth = $conn->prepare("UPDATE markers SET blocked = ? WHERE id = ?");
	$sth->execute(array('1',$_POST['user_id']));
}
?>