<?php
# Get all blocked users in this game
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
require 'db.php';
require 'check_password.php';
require 'check_admin.php';

$sth = $conn->prepare("DELETE FROM blocked WHERE user_id = ? AND game = ?");
$sth->execute(array($_POST['user_id'],$_POST['game']));

$sth = $conn->prepare("UPDATE markers SET blocked = ? WHERE id = ?");
$sth->execute(array('0',$_POST['user_id']));

echo json_encode('unblocked');

?>