<?php
# Stop timer. ADMIN ONLY
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
require 'db.php';
require 'check_password.php';
require 'check_admin.php';

$sth = $conn->prepare("UPDATE games SET end_game = ? WHERE name = ?");
$sth->execute(array(true,$_POST['game']));


?>