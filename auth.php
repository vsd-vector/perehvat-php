<?php
header('Access-Control-Allow-Origin: *');  
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start(array("cookie_samesite"=>"none", "cookie_secure"=>"1"));
	if ($_POST['password'] == sha1($history_password)) {
		$_SESSION["user"] = array("type" => "admin");
	}
} else {
	if ( !empty($_COOKIE["PHPSESSID"]) ) {
		session_start();
		echo json_encode($_SESSION["user"]);
	} else {		
		echo json_encode(array("type" => "guest"));
	}
}