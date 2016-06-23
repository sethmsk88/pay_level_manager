<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap/apps/shared/db_connect.php';

	// Array mapping pay levels with their respective pay plans
	$payLevel_payPlans = array(
		10 => 'USPS(23)',
		11 => 'USPS(23)',
		12 => 'USPS(23)',
		13 => 'USPS(23)',
		14 => 'USPS(23)',
		15 => 'A&amp;P(21)',
		16 => 'A&amp;P(21)',
		17 => 'A&amp;P(21) EXC(24)',
		18 => 'USPS(23)',
		19 => 'A&amp;P(21)'
	);

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
	$payLevels_descrs = array(); // payLevel => array(descr, payPlan)
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

<table class="table table-striped">
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
			$jobCategories = array(
				"Core Operational and Support Staff, Specialized &amp; Technical Operational and Support Staff, and Tradesworkers",
				"Specialized Professional and Other Exempt/Non-exempt USPS*",
				"Administrative Exempt, Professional, Managerial, Executive (*some non-exempt at position level)",
				"Executive",
				"Special Categories"
			);

			// Num of rows each Job Category should span
			$jobCatRowSpan = array(3,1,3,1,2);

			// Array mapping pay levels to their respective job categories
			$payLevel_jobCatIndexes = array(
				10 => 0,
				11 => 0,
				12 => 0,
				13 => 1,
				14 => 2,
				15 => 2,
				16 => 2,
				17 => 3,
				18 => 4,
				19 => 4
			);

			$currentJobCatIndex = -1; // init to value smaller than smallest index

			foreach ($payLevels_descrs as $payLevel => $descr) {

				echo '<tr>';

				// If this pay level belongs to a new job category, output job category
				if ($currentJobCatIndex < $payLevel_jobCatIndexes[$payLevel]) {
					$currentJobCatIndex = $payLevel_jobCatIndexes[$payLevel];

					echo '<td rowspan="' . $jobCatRowSpan[$currentJobCatIndex] . '">' . $jobCategories[$currentJobCatIndex] . '</td>';
				}
		?>

			<td><?= $payLevel_payPlans[$payLevel] ?></td>
			<td><?= $descr ?></td>
			<td><?= $payLevel ?></td>
			<td>Min Sal</td>
			<td>Max Sal</td>
			<td>Med Sal</td>
			<td>Salary Range</td>
			<td>
				<?= $oldPayGrade_ranges[$payLevel]["min"] . ' to ' .  $oldPayGrade_ranges[$payLevel]["max"] ?>
			</td>
			<td>Num Emps</td>
			<td>% of Staff</td>
			<td>Num Classifications</td>
		</tr>
		<?php
			}
		?>
	</tbody>
</table>




