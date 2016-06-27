<link href="./css/pay_level_ranges.css" rel="stylesheet" />

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

	// Get Min/Mid/Max Salaries for each pay level
	$sel_minMidMaxSal_sql = "
		SELECT PayLevel, PayLevelMin, PayLevelMid, PayLevelMax
		FROM hrodt.pay_levels_descr
	";

	if (!$stmt = $conn->prepare($sel_minMidMaxSal_sql)){
		echo 'Prepare failed: (' . $conn->errno . ') ' . $conn->error . '<br />';
	} else{
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($payLevel, $plMinSal, $plMidSal, $plMaxSal);

		// Convert query results into associative array
		// payLevel => array("min"=>MinSal, "med"=>MedSal, "max"=>MaxSal)
		$minMidMaxSals = array();
		while ($stmt->fetch()) {
			$minMidMaxSals[$payLevel] = array(
				"min" => $plMinSal,
				"mid" => $plMidSal,
				"max" => $plMaxSal
			);
		}
	}

	// Get the min and max salaries of the active employees in each pay level
	$sel_salRangeMinMax_sql = "
		SELECT p.PayLevel, MIN(e.Annual_Rt) AS MinSal, MAX(e.Annual_Rt) AS MaxSal
		FROM hrodt.all_active_fac_staff e
		JOIN hrodt.pay_levels p
			ON e.JobCode = p.JobCode
		WHERE p.PayLevel IS NOT NULL
		GROUP BY p.PayLevel
	";
	if (!$stmt = $conn->prepare($sel_salRangeMinMax_sql)){
		echo 'Prepare failed: (' . $conn->errno . ') ' . $conn->error . '<br />';
	} else{
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($payLevel, $minSal, $maxSal);

		// Convert query results into associative array
		// payLevel => array("min"=>MinSal, "max"=>MaxSal)
		$salRangeMinMax_arr = array();
		while ($stmt->fetch()) {
			$salRangeMinMax_arr[$payLevel] = array(
				"min" => $minSal,
				"max" => $maxSal
			);
		}
	}

	// Get the number of employees in each pay level
	$sel_numEmps_sql = "
		SELECT p.PayLevel, COUNT(e.EmplID) AS NumEmps
		FROM hrodt.all_active_fac_staff e
		LEFT JOIN hrodt.pay_levels p
			ON e.JobCode = p.JobCode
		WHERE p.PayLevel IS NOT NULL
		GROUP BY p.PayLevel
		ORDER BY p.PayLevel
	";
	if (!$stmt = $conn->prepare($sel_numEmps_sql)){
		echo 'Prepare failed: (' . $conn->errno . ') ' . $conn->error . '<br />';
	} else{
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($payLevel, $numEmps);

		// Convert query results into associative array
		// payLevel => numEmps
		$numEmps_arr = array();
		while ($stmt->fetch()) {
			$numEmps_arr[$payLevel] = $numEmps;
		}
	}

	// Get the total number of staff
	$sel_numStaff_sql = "
		SELECT COUNT(e.EmplID) AS NumStaff
		FROM hrodt.all_active_fac_staff e
		JOIN hrodt.pay_levels p
			ON e.JobCode = p.JobCode
		WHERE p.PayLevel IS NOT NULL
			AND (e.JobFamily = 'USPS' OR e.JobFamily = 'A&P')
	";
	if (!$stmt = $conn->prepare($sel_numStaff_sql)){
		echo 'Prepare failed: (' . $conn->errno . ') ' . $conn->error . '<br />';
	} else{
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($numStaff);
		$stmt->fetch();
	}

	// Get the number of classifications for each pay level
	$sel_numClass_sql = "
		SELECT PayLevel, COUNT(JobCode) AS NumClassifications
		FROM hrodt.pay_levels
		WHERE PayLevel IS NOT NULL
		GROUP BY PayLevel
		ORDER BY PayLevel
	";
	if (!$stmt = $conn->prepare($sel_numClass_sql)){
		echo 'Prepare failed: (' . $conn->errno . ') ' . $conn->error . '<br />';
	} else{
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($payLevel, $numClass);

		// Convert query results into associative array
		// payLevel => numClass
		$numClass_arr = array();
		while ($stmt->fetch()) {
			$numClass_arr[$payLevel] = $numClass;
		}
	}
?>

<table id="payLevelRanges-table" class="table table-striped">
	<thead>
		<tr>
			<th>Job Category</th>
			<th>PayPlan</th>
			<th>Level Description</th>
			<th>Pay Level</th>
			<th>Min</th>
			<th>Max</th>
			<th>Mid</th>
			<th>Range % lowest to hightest paid EE in pay level</th>
			<th>Old Pay Grade</th>
			<th>Approximate<br /># of Employees</th>
			<th>Approximate<br />% of USPS/A&amp;P Staff</th>
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
			<td class="highlight">$<?= number_format($minMidMaxSals[$payLevel]["min"], 2, '.', ',') ?></td>
			<td class="highlight">
				<?php
					if ($minMidMaxSals[$payLevel]["max"] == -1)
						echo "No max";
					else
						echo number_format($minMidMaxSals[$payLevel]["max"], 2, '.', ',');
				?>
			</td>
			<td>$<?= number_format($minMidMaxSals[$payLevel]["mid"], 2, '.', ',') ?></td>
			<td>
				<?php
					// calculate percentage using
					$salRangePercentage = $salRangeMinMax_arr[$payLevel]["max"] / $salRangeMinMax_arr[$payLevel]["min"] * 100;
					echo number_format($salRangePercentage, 1, '.', ',') . '%';
				?>
			</td>
			<td>
				<?= $oldPayGrade_ranges[$payLevel]["min"] . ' to ' .  $oldPayGrade_ranges[$payLevel]["max"] ?>
			</td>
			<td><?= $numEmps_arr[$payLevel] ?></td>
			<td>
				<?php
					$staffPercentage = ($numEmps_arr[$payLevel] / $numStaff) * 100;
					echo number_format($staffPercentage, 1, '.', ',') . '%';
				?>
			</td>
			<td><?= $numClass_arr[$payLevel] ?></td>
		</tr>
		<?php
			}
		?>
	</tbody>
</table>




