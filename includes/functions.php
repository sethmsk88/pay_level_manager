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
