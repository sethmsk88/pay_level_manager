<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap/apps/shared/db_connect.php';

	// Get pay level descriptions
	$sel_payLevel_descr = "
		SELECT PayLevel, Descr
		FROM hrodt.pay_levels_descr
	";
	if (!$stmt = $conn->prepare($sel_payLevel_descr)){
		echo 'Prepare failed: (' . $conn->errno . ') ' . $conn->error;
	} else{
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($payLevel, $payLevel_descr);
	}

	// Convert query results into associative array
	$payLevels_descrs = array(); // payLevel => descr
	while ($stmt->fetch()) {
		$payLevels_descrs[$payLevel] = $payLevel_descr;
	}

	// Select highest and lowest OldPayGrade for each Pay Level
	// and insert them into an array indexed by Pay Level
	$sel_oldPayGrade_sql = "
		SELECT MIN(CAST(OldPayGrade AS SIGNED)) AS OldPayGrade_Min,
			MAX(CAST(OldPayGrade AS SIGNED)) AS OldPayGrade_Max
		FROM hrodt.pay_levels
		WHERE OldPayGrade IS NOT NULL AND
			PayLevel = ?
	";

	if (!$stmt = $conn->prepare($sel_oldPayGrade_sql)){
		echo 'Prepare failed: (' . $conn->errno . ') ' . $conn->error . '<br />';
	} else{
		$oldPayGrade_ranges = array(); // NOTE: $payLevel => array("min" => $minOldPayGrade, "max" => $maxOldPayGrade)
		foreach ($payLevels_descrs as $payLevel => $descr) {
			
			$stmt->bind_param("i", $payLevel);
			$stmt->execute();
			$stmt->store_result();
			$stmt->bind_result($minOldPayGrade, $maxOldPayGrade);
			$stmt->fetch();

			// Insert min and max OldPayGrades into array,
			// indexed by payLevel
			$oldPayGrade_ranges[$payLevel] = array();
			$oldPayGrade_ranges[$payLevel]["min"] = $minOldPayGrade;
			$oldPayGrade_ranges[$payLevel]["max"] = $maxOldPayGrade;
		}
	}

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
		<?php
			foreach ($payLevels_descrs as $payLevel => $descr) {
		?>

		<tr>
			<td rowspan="3">Core Operational and Support Staff, Specialized &amp; Technical Operational and Support Staff, and Tradesworkers</td>
			<td>USPS(23)</td>
			<td><?= $payLevel_descr ?></td>
			<td><?= $payLevel ?></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td>
				<?= $oldPayGrade_ranges[$payLevel]["min"] . ' to ' .  $oldPayGrade_ranges[$payLevel]["max"] ?>
			</td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<?php
			}
		?>
	</tbody>
</table>




