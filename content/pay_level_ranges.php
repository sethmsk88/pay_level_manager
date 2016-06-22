<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap/apps/shared/db_connect.php';

	// Get pay level descriptions
	$sel_payLevel_descr = "
		SELECT PayLevel, Descr
		FROM hrodt.pay_levels_descr
		WHERE PayLevel = 10
	";

	if (!$stmt = $conn->prepare($sel_payLevel_descr)){
		echo 'Prepare failed: (' . $conn->errno . ') ' . $conn->error;
	} else{
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($payLevel, $payLevel_descr);
		$stmt->fetch();
	}

	// For each Pay Level, get lowest and highest OldPayGrade,
	// then create string like "10 to 19", where 10 is lowest and
	// 19 is highest

	// $update_sql = "
	// 	UPDATE hrodt.pay_levels
	// 	SET OldPayGrade = '3,4,5'
	// 	WHERE OldPayGrade = '345'
	// ";
	// $stmt = $conn->prepare($update_sql);
	// $stmt
	
?>

<table>
	<thead>
		<tr>
			<th>Job Category</th>
			<th>PayPlan</th>
			<th>Level Description</th>
			<th>Pay Level</th>
			<th>Min</th>
			<th>Max</th>
			<th>Med</th>
			<th>Range % lowest to hightest paid EE in pay level</th>
			<th>Old Pay Grade</th>
			<th>Approx. # of Employees</th>
			<th>% of Staff</th>
			<th>Number of Classifications</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td rowspan="3">Core Operational and Support Staff, Specialized &amp; Technical Operational and Support Staff, and Tradesworkers</td>
			<td>USPS(23)</td>
			<td><?= $payLevel_descr ?></td>
			<td><?= $payLevel ?></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	</tbody>
</table>




