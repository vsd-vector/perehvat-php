<?php
# Join game
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
require 'db.php';
require 'check_password.php';

$sth = $conn->prepare("SELECT name, creator FROM games WHERE name = ?");
$sth->execute(array($_GET['name']));
$result = $sth->setFetchMode(PDO::FETCH_ASSOC);
$result = $sth->fetch();

if(empty($result)) {
	echo json_encode(false);
} else {	
	$sth = $conn->prepare("SELECT user_id FROM blocked WHERE game = ? AND user_id = ?");
	$sth->execute(array($_GET['name'], $_GET['id']));
	$in_block = $sth->setFetchMode(PDO::FETCH_ASSOC);
	$in_block = $sth->fetch();

	if(empty($in_block) && $_GET['id'] != $result['creator']) {
		$sth = $conn->prepare("INSERT INTO blocked SET user_id = ?, game = ?");
		$sth->execute(array($_GET['id'],$_GET['name']));
	}
	echo json_encode(true);
}


	

?>