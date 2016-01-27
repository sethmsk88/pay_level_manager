<?php
	/* If no variables were posted, stop loading page */
	if (count($_POST) == 0)
		exit;
	
	include_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap/apps/shared/db_connect.php';
	include_once '../includes/functions.php';

	$param_double_MinSalAdjusted = parseMoney($_POST['recMinSal']);
	$param_double_MedSalAdjusted = parseMoney($_POST['recMedSal']);
	$param_double_MaxSalAdjusted = parseMoney($_POST['recMaxSal']);
	$param_double_Benchmark = parseMoney($_POST['benchmark']);
	$param_str_JobCode = $_POST['_jobCode'];

	$update_payLevel_sql = "
		UPDATE hrodt.pay_levels
		SET MinSalAdjusted = ?,
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
	else if (!$stmt->bind_param("dddds",
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


	mysqli_close($conn);
?>
