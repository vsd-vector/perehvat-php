<?php
#ADMIN ONLY. Admin sets new prey for the game
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
require 'db.php';
require 'check_password.php';
require 'check_admin.php';

if($_POST['is_prey'] == 'true') {
	$user_id = $_POST['prey_id'];
	$is_prey = 1;
} else {
	$user_id = '';
	$is_prey = 0;
}

$sth = $conn->prepare("UPDATE markers SET is_prey = ? WHERE game = ? AND id = ?");
$sth->execute(array($is_prey,$_POST['game'],$_POST['prey_id']));




?>