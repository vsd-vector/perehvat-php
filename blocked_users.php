<?php
# Get all blocked users in this game
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
require 'db.php';
require 'check_password.php';
require 'check_admin.php';

$stmt = "SELECT markers.id, markers.user_name FROM markers INNER JOIN blocked ON markers.id=blocked.user_id WHERE blocked.game = ?";
$q = $conn->prepare($stmt);
$q->execute(array($_GET['game']));
$result = $q->setFetchMode(PDO::FETCH_ASSOC);
$result = $q->fetchAll();

echo json_encode($result);

?>