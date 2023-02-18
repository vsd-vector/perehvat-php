<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
require 'db.php';
require 'check_password.php';
require_once 'utils.php';

$stmt = "SELECT user_id FROM admins WHERE game = ?";
$q = $conn->prepare($stmt);
$q->execute(array($_GET['game']));
$result = $q->setFetchMode(PDO::FETCH_ASSOC);
$result = $q->fetchAll();

$me = check_super_admin();
if ($me["type"] == "admin") {
	$result[] = array("user_id" => $_GET["id"]);
}

echo json_encode($result);
?>