<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
require 'db.php';
require 'check_password.php';

// $stmt = "SELECT prey FROM games WHERE name = ?";
$stmt = "SELECT id FROM markers WHERE game = ? AND is_prey = 1";
$q = $conn->prepare($stmt);
$q->execute(array($_GET['game']));
$result = $q->setFetchMode(PDO::FETCH_ASSOC);
$result = $q->fetchAll();

echo json_encode($result);
?>