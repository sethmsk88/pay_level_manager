<?php
/**
 * Get the string Yes/No represenation of a boolean value
 *
 * @param val  Boolean value (or integers 0 or 1)
 * @return  String "Yes" or "No"
 */
function convertYesNo($val) {
	if ($val == 0)
		return "No";
	else if ($val == 1)
		return "Yes";
	else
		return "";
}

/**
 * Convert the FLSA value into the format specified by
 * the format parameter
 *
 * @param flsa  string or int representing the pay plan
 * @param format  format tow hich the user would like to
 *		convert the FSLA value
 * @return the converted form of the FLSA value
 */
function convertFLSA($flsa, $format) {

	$convertedFLSA = ''; // Return value

	if ($format == 'numeric') {
		switch ($flsa) {
			case 'N':
			case 'NE':
				$convertedFLSA = 0;
				break;
			case 'X':
			case 'E':
				$convertedFLSA = 1;
				break;
			case '1X N':
			case 'both':
				$convertedFLSA = 2;
				break;
		}
	}
	else if ($format == 'symbolic') {
		switch ($flsa) {
			case 0:
				$convertedFLSA = 'N';
				break;
			case 1:
				$convertedFLSA = 'X';
				break;
			case 2:
				$convertedFLSA = 'both';
				break;
		}
	}
	else if ($format == 'descr') {
		switch ($flsa) {
			case 'N':
			case 'NE':
				$convertedFLSA = 'Non-Exempt';
				break;
			case 'X':
			case 'E':
				$convertedFLSA = 'Exempt';
				break;
			case '1X N':
			case 'both':
				$convertedFLSA = 'Both';
				break;
		}
	}
	return $convertedFLSA;
}

// Is position A&P?
// Are there emps in this position that are under threshold?
	// flsa_string .= "NE"
// Are there emps in this position that are over threshold?
	// If flsa_string != ""
		// flsa_string .= "/"
	// flsa_string .= "E"

function getFLSA(&$conn, $jobCode, $payPlan, $flsa_status) {
	
	// If pay plan is A&P, do the calculations below, otherwise just return the FLSA status
	if ($payPlan == "A&P") {

		// select the most recent threshold
		$sel_threshold_sql = "
			SELECT threshold
			FROM hrodt.flsa_threshold
			ORDER BY dateUpdated DESC
			LIMIT 1
		";
		if (!$stmt = $conn->prepare($sel_threshold_sql)) {
			echo 'Prepare failed: (' . $conn->errno . ') ' . $conn->error;
		} else if (!$stmt->execute()) {
			echo 'Execute failed: (' . $stmt->errno . ') ' . $stmt->error;
		}
		$stmt->bind_result($threshold);
		$stmt->fetch();
		$stmt->close();

		// select all salaries for employees in this position
		$sel_salaries_sql = "
			SELECT Annual_Rt
			FROM hrodt.all_active_fac_staff
			WHERE JobCode = ?
		";
		if (!$stmt = $conn->prepare($sel_salaries_sql)) {
			echo 'Prepare failed: (' . $conn->errno . ') ' . $conn->error;
		} else if (!$stmt->bind_param('s', $jobCode)) {
			echo 'Binding parameters failed: (' . $stmt->errno . ') ' . $stmt->error;	
		} else if (!$stmt->execute()) {
			echo 'Execute failed: (' . $stmt->errno . ') ' . $stmt->error;
		}
		$stmt->bind_result($salary);

		// Test all salaries to see if they are above or below threshold
		$flsa_exempt = false;
		$flsa_nonexempt = false;
		while ($stmt->fetch()) {
			if ($salary < $threshold)
				$flsa_nonexempt = true;
			else
				$flsa_exempt = true;
		}

		// Create FLSA status string
		$new_flsa_status = "";
		if ($flsa_exempt && $flsa_nonexempt) {
			$new_flsa_status = "Both";
		} else if ($flsa_exempt) {
			$new_flsa_status = "Exempt";
		} else if ($flsa_nonexempt) {
			$new_flsa_status = "Non-exempt";
		}

		return $new_flsa_status;

 	} else {
 		// Return FLSA status
 		return convertFLSA($flsa_status, 'descr');
 	}
}

/**
 *	Convert a string representation of money into a float
 *	representation. Remove all characters except decimals
 *	and integers.
 *	
 *	@param money 	String representation of money
 *	@return Float representation of money
 */
function parseMoney($money) {
	return preg_replace("/[^0-9.]/", "", $money);
}

function sec_session_start() {
	$session_name = 'sec_session_id'; // Set a custom session name
	$secure = SECURE;

	// This stops JavaScript being able to access the session id
	$httponly = true;

	/* Forces sessions to only use cookies */
	if (ini_set('session.use_only_cookies', 1) === false) {
		header("Location: ../error.php?err=Could not initiate a safe session (ini_set)");
		exit();
	}

	// Get current cookies params
	$cookieParams = session_get_cookie_params();
	session_set_cookie_params($cookieParams["lifetime"],
		$cookieParams["path"],
		$cookieParams["domain"],
		$secure,
		$httponly);

	// Set the session name to the one set above
	session_name($session_name);

	// Start the PHP session
	session_start();

	// Regenerate the session, delete the old one
	session_regenerate_id(true);
}

function login($email, $password, $conn) {

	sec_session_start();

	/* Get user record with matching email */
	$sel_user_sql = "
		SELECT id, password, firstName, lastName
		FROM secure_login.users
		WHERE email = ?
		LIMIT 1";

	if ($stmt = $conn->prepare($sel_user_sql)) {
		$stmt->bind_param("s", $email);
		$stmt->execute();
		$stmt->store_result();

		// Get variables from result
		$stmt->bind_result($user_id, $db_password, $firstName, $lastName);
		$stmt->fetch();

		// If email address exists in users table
		if ($stmt->num_rows == 1) {

			/*
				If the user exists we check if the account is locked
				from too many login attempts.
			*/
			if (checkbrute($user_id, $conn) == true) {
				// Account is locked
				// Send email to user saying their account is locked
				return false;
			}
			else {
				/*
					Check if the password in the DB matches the
					password the user submitted
				*/

				if ($db_password == $password) {

					// Password is correct
					// Get the user-agent string of the user
					$user_browser = $_SERVER['HTTP_USER_AGENT'];

					$user_id = preg_replace("/[^0-9]+/", "", $user_id);
                    $_SESSION['user_id'] = $user_id;
                    
                    // XSS protection as we might print these values
                    $firstName = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $firstName);
                    $lastName = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $lastName);

					$_SESSION['firstName'] = $firstName;
					$_SESSION['lastName'] = $lastName;
					$_SESSION['login_string'] = hash('sha512', $password . $user_browser);

					// Login successful
					return true;
				}
				else {

					// Password is not correct
					// We record this attempt in the database
					$now = time();

					$ins_login_attempt_sql = "
						INSERT INTO secure_login.login_attempts(user_id, time)
						VALUES ('$user_id', '$now')
					";
					$conn->query($ins_login_attempt_sql);

					return false;
				}
			}
		}
		else {

			// No user exists
			return false;
		}
	}
}

function checkbrute($user_id, $conn) {
	$now = time();

	// All login attempts are counted from the past 2 hours
	$valid_attempts = $now - (2 * 60 * 60);

	$sel_login_times_sql = "
		SELECT time
		FROM secure_login.login_attempts
		WHERE user_id = ? AND
			time > '$valid_attempts'
	";

	if ($stmt = $conn->prepare($sel_login_times_sql)) {
		$stmt->bind_param('i', $user_id);

		$stmt->execute();
		$stmt->store_result();

		// If there are more than 5 failed logins
		if ($stmt->num_rows > 5) {
			return true;
		}
		else {
			return false;
		}
	}
}

function login_check($conn) {
	
	$loggedIn = false; // Default

	// Check if all session variables are set
	if (isset($_SESSION['user_id'],
			$_SESSION['firstName'],
			$_SESSION['lastName'],
			$_SESSION['login_string'])) {

		$user_id = $_SESSION['user_id'];
		$firstName = $_SESSION['firstName'];
		$lastName = $_SESSION['lastName'];
		$login_string = $_SESSION['login_string'];

		// Get the user-agent string of the user
		$user_browser = $_SERVER['HTTP_USER_AGENT'];

		$sel_user_pw_sql = "
			SELECT password
			FROM secure_login.users
			WHERE id = ?
			LIMIT 1
		";

		if ($stmt = $conn->prepare($sel_user_pw_sql)) {
			$stmt->bind_param('i', $user_id);
			$stmt->execute();
			$stmt->store_result();

			if ($stmt->num_rows == 1) {
				// If the user exists get variables from result
				$stmt->bind_result($password);
				$stmt->fetch();
				$login_check = hash('sha512', $password . $user_browser);

				if ($login_check == $login_string) {
					// Logged in
					$loggedIn = true;
				}
			}
		}
	}
	return $loggedIn;
}

function esc_url($url) {
	if ('' == $url) {
		return $url;
	}

	$url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);

	$strip = array('%0d', '%0a', '%0D', '%0A');
	$url = (string) $url;

	$count = 1;
	while ($count) {
		$url = str_replace($strip, '', $url, $count);
	}

	$url = str_replace(';//', '://', $url);
	$url = htmlentities($url);
	$url = str_replace('&amp;', '&#038;', $url);
    $url = str_replace("'", '&#039;', $url);

    if ($url[0] !== '/') {
    	// We're only interested in relative links from $_SERVER['PHP_SELF']
    	return '';
    }
    else {
    	return $url;
    }
}


?>
