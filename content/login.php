<?php
require_once $_SERVER['DOCUMENT_ROOT'] . 'bootstrap/apps/shared/db_connect.php';
require_once './includes/functions.php';

if (login_check($conn) == true) {
	$logged = 'in';
}
else {
	$logged = 'out';
}

if (isset($_GET['error]'])) {
	echo '<p class="error">Error Logging In!</p>';
}
?>
<?php
/*
	Testing User
	username: test_user
	email: test@example.com
	pw: testing
*/
?>
<form
	name="login_form"
	method="post"
	action="./content/act_process_login.php">
	
	Email: <input type="text" name="email">
	Password: <input type="password" name="password" id="password">
	<input
		type="button"
		value="Login"
		onclick="formhash(this.form, this.form.password);">

</form>

<?php
if (login_check($conn) == true) {
	echo '<p>Currently logged ' . $logged . ' as ' . htmlentities($_SESSION['username']) . '.</p>';
	echo '<p>Do you want to change user? <a href="./content/act_logout.php">Logout</a>.</p>';
}
else {
	echo '<p>Currently logged ' . $logged . '.</p>';
	echo '<p>If you don\'t have a login, please <a href="?page=register">register</a></p>';
}
?>
