<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
require 'db.php';
require 'check_password.php';

$post = $_POST;
var_dump($post);

if(!empty($post)) {
	#assign the sth variable (sth means statement handle) to insert the data
	$sth = $conn->prepare("UPDATE markers SET user_name = ?, last_activity = NOW() WHERE id = ?");

	#Execute the insert
	$sth->execute(array($_POST['user_name'],$_POST['id']));
}


?>