<?php
# Get closed districts
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
require 'db.php';
require 'check_password.php';

$sth = $conn->prepare("SELECT district FROM districts WHERE game = ?");
$sth->execute(array($_GET['game']));
$result = $sth->setFetchMode(PDO::FETCH_ASSOC);
$result = $sth->fetchAll();

echo json_encode($result);
?>