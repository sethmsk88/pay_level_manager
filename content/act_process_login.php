<?php
require_once $_SERVER['DOCUMENT_ROOT'] . 'bootstrap/apps/shared/db_connect.php';
require_once '../includes/functions.php';

define("APP_PATH", "http://" . $_SERVER['HTTP_HOST'] . "/bootstrap/apps/login_system/");

if (isset($_POST['email'], $_POST['p'])) {
	$email = $_POST['email'];
	$password = $_POST['p']; // hashed password

	if (login($email, $password, $conn) == true) {
		// Login success
		header('Location: ' . APP_PATH . '?page=protected_page');
		exit();
	}
	else {
		// Login failed
		header('Location: ../index.php?error=1');
		exit();
	}
}
else {
	// The correct POST variables were not sent to this page
	echo 'Invalid Request<br />';
}

?>
