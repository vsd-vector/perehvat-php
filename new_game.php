<?php
#create new game
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
require 'db.php';
require 'check_password.php';

if(!empty($_POST)) {
	# checks if there already is game with such name
	$sth = $conn->prepare("SELECT name FROM games WHERE name = ?");
	$sth->execute(array($_POST['name']));
	$result = $sth->setFetchMode(PDO::FETCH_ASSOC);
	$result = $sth->fetchAll();

	if(empty($result)) {	
	# create new game	
		$sth = $conn->prepare("INSERT INTO games SET name = ?, creator = ?");
		$sth->execute(array($_POST['name'],$_POST['creator']));

		$sth = $conn->prepare("INSERT INTO admins SET user_id = ?, game = ?");
		$sth->execute(array($_POST['creator'],$_POST['name']));
		echo json_encode(true);	
	} else {
		echo json_encode(false);
	}		
} else {
	echo json_encode(false);
}

?>