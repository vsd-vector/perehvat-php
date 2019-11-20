<?php
#ADMIN ONLY. Change/add admin
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
require 'db.php';
require 'check_password.php';
require 'check_admin.php';


if ($_POST['close'] == 'true') {
	# insert district as closed
	$sth = $conn->prepare("INSERT INTO districts SET district = ?, game = ?");
	$sth->execute(array($_POST['district'],$_POST['game']));

	echo json_encode('district is closed');
} else if ($_POST['close'] == 'false') {
	# delete district from closed
	
	$sth = $conn->prepare("DELETE FROM districts WHERE district = ? AND game = ?");
	$sth->execute(array($_POST['district'],$_POST['game']));

	echo json_encode('district deleted');
}


?>