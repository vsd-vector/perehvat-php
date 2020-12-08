<?php
# edit game settings ADMIN ONLY
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
require 'db.php';
require 'check_password.php';
require 'check_admin.php';

$sth = $conn->prepare("UPDATE games SET radius = ?, game_type = ? WHERE name = ?");
$sth->execute(array($_POST['radius'],$_POST['game_type'],$_POST['game']));


?>