<?php
# returns info about timer 
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
require 'db.php';
require 'check_password.php';

$stmt = "SELECT * FROM games WHERE name = ?";
$q = $conn->prepare($stmt);
$q->execute(array($_GET['name']));
$result = $q->setFetchMode(PDO::FETCH_ASSOC);
$result = $q->fetchAll();

$result[0]['now_time'] = time();

echo json_encode($result[0]);
?>