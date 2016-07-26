<?php
/***  CHECK IF PAGE WAS POSTED TO  ***/
if (!isset($_SERVER["REQUEST_METHOD"]) ||
	$_SERVER["REQUEST_METHOD"] != "POST") {
	exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap/apps/shared/db_connect.php';
require_once '../includes/functions.php';

if (isset($_POST['email'], $_POST['p'])) {
	$email = $_POST['email'];
	$password = $_POST['p']; // hashed password

	if (login($email, $password, $conn) == true) {
		echo 1;
	}
	else {
		echo 0;
	}
}
else {
	// The correct POST variables were not sent to this page
	header('Location: ../index.php?err=invalid_request');
}
?>
