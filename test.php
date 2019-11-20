<?php
# Get all blocked users in this game
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
require 'db.php';

$sth = $conn->prepare("UPDATE markers SET last_activity = NOW() ");
$sth->execute();



?>