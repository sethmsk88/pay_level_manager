<?php
	/* If no variables were posted, stop loading page */
	if (count($_POST) == 0)
		exit;
	
	include_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap/apps/shared/db_connect.php';
	include_once '../includes/functions.php';

	$actMedSal = parseMoney($_POST['_actMed']);
	$recMinSal = parseMoney($_POST['recMinSal']);
	$recMedSal = parseMoney($_POST['recMedSal']);
	$recMaxSal = parseMoney($_POST['recMaxSal']);
	$benchmark = parseMoney($_POST['benchmark']);

	/* Calculate adjusted recommended salaries */
	if ($benchmark > 0) {
		if (($actMedSal < ($benchmark * 0.9)) ||
			($actMedSal > ($benchmark * 1.1))) {
			$adjRecMinSal = 0.8 * $benchmark; // 80% of benchmark
			$adjRecMedSal = $benchmark;       // 100% of benchmark
			$adjRecMaxSal = 1.2 * $benchmark; // 120% of benchmark*/
		}
		else {
			$adjRecMinSal = $actMedSal * 0.8; // 80% of actual median
			$adjRecMedSal = $actMedSal;       // 100% of actual median
			$adjRecMaxSal = $actMedSal * 1.2; // 120% of actual median
		}
	}
	else {
		$adjRecMinSal = $actMedSal * 0.8; // 80% of actual median
		$adjRecMedSal = $actMedSal;       // 100% of actual median
		$adjRecMaxSal = $actMedSal * 1.2; // 120% of actual median
	}

	// Do not allow min recommended salary to fall below minimum wage
	$minimumWageSal = 16744;
	if ($adjRecMinSal < $minimumWageSal) {
		echo json_encode(array("error"=>"minWage",
			"minWage"=>$minimumWageSal,
			"jobCode"=>$_POST['_jobCode']));
		exit;
	}

	$param_int_payLevel = $_POST['payLevel'];
	$param_double_MinSalAdjusted = $adjRecMinSal;
	$param_double_MedSalAdjusted = $adjRecMedSal;
	$param_double_MaxSalAdjusted = $adjRecMaxSal;
	$param_double_Benchmark = $benchmark;
	$param_str_JobCode = $_POST['_jobCode'];

	$update_payLevel_sql = "
		UPDATE hrodt.pay_levels
		SET PayLevel = ?,
			MinSalAdjusted = ?,
			MedSalAdjusted = ?,
			MaxSalAdjusted = ?,
			Benchmark = ?
		WHERE JobCode = ? AND
			Active = 1
	";
	
	if (!$stmt = $conn->prepare($update_payLevel_sql)) {
		echo 'Prepare failed: (' . $conn->errno . ') ' . $conn->error;
		exit;
	}
	else if (!$stmt->bind_param("ddddds",
		$param_int_payLevel,
		$param_double_MinSalAdjusted,
		$param_double_MedSalAdjusted,
		$param_double_MaxSalAdjusted,
		$param_double_Benchmark,
		$param_str_JobCode)) {
		echo 'Binding parameters failed: (' . $stmt->errno . ') ' . $stmt->error;
		exit;
	}
	else if (!$stmt->execute()) {
		echo 'Execute failed: (' . $stmt->errno . ') ' . $stmt->error;
		exit;
	}
	$stmt->close();


	$sel_payLevel_sql = "
		SELECT *
		FROM hrodt.pay_levels
		WHERE JobCode = ? AND
			Active = 1
	";

	if (!$stmt = $conn->prepare($sel_payLevel_sql)) {
		echo 'Prepare failed: (' . $conn->errno . ') ' . $conn->error;
		exit;
	}
	else if (!$stmt->bind_param("s", $param_str_JobCode)) {
		echo 'Binding parameters failed: (' . $stmt->errno . ') ' . $stmt->error;
		exit;
	}
	else if (!$stmt->execute()) {
		echo 'Execute failed: (' . $stmt->errno . ') ' . $stmt->error;
		exit;
	}

	$payLevel_res = $stmt->get_result();
	$payLevel_row = $payLevel_res->fetch_assoc();
	$stmt->close();

	/* Create associative array to hold updated values */
	$returnValues = array();

	$returnValues['payLevel'] = $payLevel_row['PayLevel'];

	if (is_null($payLevel_row['Benchmark']) ||
		$payLevel_row['Benchmark'] == 0) {

		// if adjusted salaries are not null, use them instead
		if (!is_null($payLevel_row['MinSalAdjusted']))
			$returnValues['recMinSal'] = $payLevel_row['MinSalAdjusted'];
		else
			$returnValues['recMinSal'] = $payLevel_row['MinSal'];

		if (!is_null($payLevel_row['MedSalAdjusted']))
			$returnValues['recMedSal'] = $payLevel_row['MedSalAdjusted'];
		else
			$returnValues['recMedSal'] = $payLevel_row['MedSal'];

		if (!is_null($payLevel_row['MaxSalAdjusted']))
			$returnValues['recMaxSal'] = $payLevel_row['MaxSalAdjusted'];
		else
			$returnValues['recMaxSal'] = $payLevel_row['MaxSal'];
	}
	else {
		$returnValues['recMinSal'] = $payLevel_row['MinSalAdjusted'];
		$returnValues['recMedSal'] = $payLevel_row['MedSalAdjusted'];
		$returnValues['recMaxSal'] = $payLevel_row['MaxSalAdjusted'];
	}
	$returnValues['benchmark'] = $payLevel_row['Benchmark'];

	echo json_encode($returnValues);

	mysqli_close($conn);
?>
