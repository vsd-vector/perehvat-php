<?php
# start timer. ADMIN ONLY
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
require 'db.php';
require 'check_password.php';
require 'check_admin.php';

$time = time();
$sth = $conn->prepare("UPDATE games SET game_time = ?, waiting_time = ?, start_time = ?, end_game = ? WHERE name = ?");
$sth->execute(array($_POST['game_time'],$_POST['waiting_time'],$time,$_POST['end_game'],$_POST['game']));


?>