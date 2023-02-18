<?php
header('Access-Control-Allow-Origin: *');  
require 'db.php';
require_once 'utils.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start(array("cookie_samesite"=>"none", "cookie_secure"=>"1"));
	if ($_POST['password'] == sha1($history_password)) {
		$_SESSION["user"] = array("type" => "admin");
	}
} else {
    $user = check_super_admin();
	echo json_encode($user);	
}