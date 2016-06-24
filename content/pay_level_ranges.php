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

	// Get Min/Med/Max Salaries for each pay level
	$sel_minMedMaxSal_sql = "
		SELECT c.PayLevel, MIN(c.MinSal) AS ActMinSal, (
			SUBSTRING_INDEX(				/* left median: max value in lower half */
				SUBSTRING_INDEX(
					GROUP_CONCAT(			/* list all values in ascending order */
						c.MedSal
                		ORDER BY c.MedSal
					),
            		',',
            		CEILING(COUNT(*)/2)		/* left half of the list */
				),
		        ',',
		        -1							/* keep only the last value in the list */
			) +
		    SUBSTRING_INDEX(				/* right median: min value in upper half */
				SUBSTRING_INDEX(
					GROUP_CONCAT(			/* list all values in ascending order */
						c.MedSal
		                ORDER BY c.MedSal
					),
		            ',',
		            -CEILING(COUNT(*)/2)	/* right half of the list */
				),
		        ',',
		        1							/* keep only the first value in the list */
			))/2
			AS ActMedSal,
			MAX(c.MaxSal) AS ActMaxSal
		FROM (
			SELECT a.PayLevel,
				COALESCE(a.MinSalAdjusted, b.MinSal) AS MinSal,
		        COALESCE(a.MedSalAdjusted, b.MedSal) AS MedSal,
		        COALESCE(a.MaxSalAdjusted, b.MaxSal) AS MaxSal
			FROM hrodt.pay_levels a
			JOIN hrodt.pay_levels b
				ON a.JobCode = b.JobCode
		) AS c
		WHERE c.MinSal IS NOT NULL
			AND c.MedSal IS NOT NULL
			AND c.MaxSal IS NOT NULL
		GROUP BY c.PayLevel
		HAVING c.PayLevel IS NOT NULL
	";

	if (!$stmt = $conn->prepare($sel_minMedMaxSal_sql)){
		echo 'Prepare failed: (' . $conn->errno . ') ' . $conn->error . '<br />';
	} else{
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($payLevel, $actMinSal, $actMedSal, $actMaxSal);

		// Convert query results into associative array
		// payLevel => array("min"=>MinSal, "med"=>MedSal, "max"=>MaxSal)
		$minMedMaxSals = array();
		while ($stmt->fetch()) {
			$minMedMaxSals[$payLevel] = array(
				"min" => $actMinSal,
				"med" => $actMedSal,
				"max" => $actMaxSal
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
			<td>$<?= number_format($minMedMaxSals[$payLevel]["min"], 2, '.', ',') ?></td>
			<td>$<?= number_format($minMedMaxSals[$payLevel]["max"], 2, '.', ',') ?></td>
			<td>$<?= number_format($minMedMaxSals[$payLevel]["med"], 2, '.', ',') ?></td>
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
			<td>Num Emps</td>
			<td>% of Staff</td>
			<td>Num Classifications</td>
		</tr>
		<?php
			}
		?>
	</tbody>
</table>




