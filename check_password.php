<?php
require_once 'db.php';
require_once 'utils.php';

if (!empty($_POST)) {
	$id = $_POST['id'];
	$password = $_POST['password'];
} else {
	$id = $_GET['id'];
	$password = $_GET['password'];
}

$user = check_super_admin();

if ( $user["type"] == "admin" ) {
	// we are super-admin! no need to check password
} else {
	$stmt = "SELECT password FROM users WHERE id = ?";
	$q = $conn->prepare($stmt);
	$q->execute(array($id));
	$result = $q->setFetchMode(PDO::FETCH_ASSOC);
	$result = $q->fetch();

	if (empty($result)) {
		$sth = $conn->prepare("INSERT INTO users SET id = ?, password = ?");
		$sth->execute(array($id,$password));
	} else {
		if ($password != $result['password']) {
			http_response_code(403);
			exit();
		}
	}
}

?>