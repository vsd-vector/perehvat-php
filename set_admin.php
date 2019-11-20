<?php
#ADMIN ONLY. Change/add admin
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
require 'db.php';
require 'check_password.php';
require 'check_admin.php';

$stmt = "SELECT creator FROM games WHERE name = ?";
$q = $conn->prepare($stmt);
$q->execute(array($_POST['game']));
$creator = $q->setFetchMode(PDO::FETCH_ASSOC);
$creator = $q->fetch();

if ($_POST['admin'] == 'true') {
	# insert new admin
	$sth = $conn->prepare("INSERT INTO admins SET user_id = ?, game = ?");
	$sth->execute(array($_POST['user_id'],$_POST['game']));
} else if ($_POST['admin'] == 'false') {
	# delete admin
	if ($_POST['id'] != $_POST['user_id'] AND $creator['creator'] != $_POST['user_id']) {	
	
		$sth = $conn->prepare("DELETE FROM admins WHERE user_id = ? AND game = ?");
		$sth->execute(array($_POST['user_id'],$_POST['game']));

		echo json_encode('deleted');
	}
}


?>