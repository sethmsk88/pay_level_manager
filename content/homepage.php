<script src="./scripts/homepage.js"></script>

<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap/apps/shared/db_connect.php';
	include_once './includes/functions.php';

	/*
		Select all columns in pay_levels table, and
		select the Min, Med, and Max for each JobCode
		in the all_active_fac_staff table
	*/
	$sel_all_payLevels_sql = "
		SELECT p.*, c.ActMinSal, c.ActMedSal, c.ActMaxSal, j.JobFamily_long
		FROM hrodt.pay_levels p
		LEFT JOIN (
			SELECT JobCode,
				MIN(a.Annual_Rt) AS ActMinSal,
				(SUBSTRING_INDEX(		-- left median: max value in lower half
					SUBSTRING_INDEX(
						GROUP_CONCAT(	-- list all values in ascending order
							a.Annual_Rt
							ORDER BY a.Annual_Rt
						),
						',',
						CEILING(COUNT(*)/2)		-- left half of the list
					),
					',',
					-1		-- keep only the last value in the list
				) +
				SUBSTRING_INDEX(	-- right median: min value in upper half
					SUBSTRING_INDEX(
						GROUP_CONCAT(	-- list all values in ascending order
							a.Annual_Rt
							ORDER BY a.Annual_Rt
						),
						',',
						-CEILING(COUNT(*)/2)	-- right half of the list
					),
					',',
					1	-- keep only the first value in the list
				)
			) /2
			AS ActMedSal,
			MAX(a.Annual_Rt) AS ActMaxSal
			FROM hrodt.all_active_fac_staff a
			GROUP BY JobCode
		) AS c
		ON p.JobCode = c.JobCode
		LEFT JOIN hrodt.job_families j
		ON p.JobFamily = j.JobFamily_short
		ORDER BY p.JobCode ASC, p.PayLevel
	";

	// Run Query
	if (!$sel_all_payLevels_res = $conn->query($sel_all_payLevels_sql)){
		echo "Query failed: (" . $conn->errno . ") " . $conn->error;
	}
?>	

<!-- Overlay for modals -->
<div id="overlay" style="display:none;"></div>

<div class="container-fluid">

	<br />
	
	<table id="payLevels" class="table table-striped">
		<thead>
			<tr>
				<th id="col-0">Pay Level</th>
				<th id="col-1">Job Code</th>
				<th id="col-2">Job Title</th>
				<th id="col-3">Recommended<br />Min Salary</th>
				<th id="col-4">Recommended<br />Med Salary</th>
				<th id="col-5">Recommended<br />Max Salary</th>
				<th id="col-6">Actual Min Salary</th>
				<th id="col-7">Actual Med Salary</th>
				<th id="col-8">Actual Max Salary</th>
				<th id="col-9">Benchmark</th>
				<th id="col-10">FLSA</th>
				<th id="col-12">Union Code</th>
				<th id="col-13">Old Pay Grade</th>
				<th id="col-14">Job Family</th>
				<th id="col-15">Pay Plan</th>
				<th id="col-16">Contract</th>
				<th id="col-17">IPEDS SOCs</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$row_idx = 0;

			// For each row in query
			while ($row = $sel_all_payLevels_res->fetch_assoc()){
				
				/* Initialize monetary variables */
				$minSal = '';
				$medSal = '';
				$maxSal = '';
				$actMinSal = '';
				$actMedSal = '';
				$actMaxSal = '';
				$benchmark = '';

				// If adjusted rec min salary is not null, use it
				if (!is_null($row['MinSalAdjusted']) && $row['MinSalAdjusted'] > 0)
					$minSal = $row['MinSalAdjusted'];
				else if (!is_null($row['MinSal']) && $row['MinSal'] > 0)
					$minSal = $row['MinSal'];

				// If adjusted rec med salary is not null, use it
				if (!is_null($row['MedSalAdjusted']) && $row['MedSalAdjusted'] > 0)
					$medSal = $row['MedSalAdjusted'];
				else if (!is_null($row['MedSal']) && $row['MedSal'] > 0)
					$row['MedSal'];

				// If adjusted rec max salary is not null, use it
				if (!is_null($row['MaxSalAdjusted']) && $row['MaxSalAdjusted'] > 0)
					$maxSal = $row['MaxSalAdjusted'];
				else if (!is_null($row['MaxSal']) && $row['MaxSal'] > 0)
					$maxSal = $row['MaxSal'];

				if (!is_null($row['ActMinSal']) && $row['ActMinSal'] > 0)
					$actMinSal = $row['ActMinSal'];

				if (!is_null($row['ActMedSal']) && $row['ActMedSal'] > 0)
					$actMedSal = $row['ActMedSal'];

				if (!is_null($row['ActMaxSal']) && $row['ActMaxSal'] > 0)
					$actMaxSal = $row['ActMaxSal'];

				if (!is_null($row['Benchmark']) && $row['Benchmark'] > 0)
					$benchmark = $row['Benchmark'];
			?>

			<tr
				id="row-<?= $row_idx ?>"
				class="editable"
				<?php if ($GLOBALS['LOGGED_IN'] AND !is_null($GLOBALS['ACCESS_LEVEL'])) {
					// Only include event handler for edit form if logged in
					echo 'onclick="rowClickHandler(event);"';
				} ?>
				>

				<td><?= $row['PayLevel'] ?></td>
					<td><?= $row['JobCode'] ?></td>
					<td><?= $row['JobTitle'] ?></td>
					<td>
						<?php 
							if (!is_null($minSal) && $minSal > 0)
								echo '$' . number_format($minSal, 2, '.', ',');
						?>
					</td>
					<td>
						<?php
							if (!is_null($medSal) && $medSal > 0)
								echo '$' . number_format($medSal, 2, '.', ',');
						?>
					</td>
					<td>
						<?php
							if (!is_null($maxSal) && $maxSal > 0)
								echo '$' . number_format($maxSal, 2, '.', ',');
						?>
					</td>
					<td>
						<?php
							if (!is_null($actMinSal) && $actMinSal > 0)
								echo '$' . number_format($actMinSal, 2, '.', ',');
						?>
					</td>
					<td>
						<?php
							if (!is_null($actMedSal) && $actMedSal > 0)
								echo '$' . number_format($actMedSal, 2, '.', ',');
						?>
					</td>
					<td>
						<?php
							if (!is_null($actMaxSal) && $actMaxSal > 0)
								echo '$' . number_format($actMaxSal, 2, '.', ',');
						?>
					</td>
					<?php
						if ($benchmark != "") {
							if ($benchmark < $actMedSal * .9)
								echo '<td class="redCircle">';
							else if ($benchmark > $actMedSal * 1.1)
								echo '<td class="blueCircle">';
							else
								echo '<td>';
							echo '$' . number_format($benchmark, 2, '.', ',');
							echo '</td>';
						} else {
							echo '<td></td>';
						}
					?>
					<td><?= getFLSA($conn, $row['JobCode'], $row['PayPlan'], $row['FLSA']) ?></td>
					<td><?= $row['UnionCode'] ?></td>
					<td><?= $row['OldPayGrade'] ?></td>
					<td><?= $row['JobFamily_long'] ?></td>
					<td><?= $row['PayPlan'] ?></td>
					<td><?= convertYesNo($row['Contract']) ?></td>
					<td><?= $row['IPEDS_SOCs'] ?></td>			
				</tr>
			<?php
				$row_idx++;
			}
			?>
		</tbody>
	</table>
</div>


<!-- Edit Pay Level Form (absolutely positioned modal) -->
<?php if ($GLOBALS['LOGGED_IN'] AND !is_null($GLOBALS['ACCESS_LEVEL'])) { ?>
<div
	id="editPayLevel-cont"
	class="modalForm">

	<div class="modalForm-header">
		Edit Pay Level
		<a
			href="#"
			data-toggle="popover"
			tabindex="1"
			data-trigger="focus"
			title="<b>Description of the calculations that are performed when the Edit Pay Level form is submitted</b>"
			data-content="<b>If the Actual Median is between 90% and 110% of the Benchmark:</b><br />
			<div class='indent'>Set the Recommended Min/Med/Max salaries to 80%/100%/120% of the Actual Med salary respectively.</div><br />
			<b>Otherwise, if the case above is not true:</b><br />
			<div class='indent'>Set Recommended Min/Med/Max salaries to 80%/100%/120% of Benchmark respectively.</div><br />
			<b>Otherwise, if a Benchmark was not provided:</b><br />
			<div class='indent'>Set the Recommended Min/Med/Max salaries to 80%/100%/120% of the Actual Med salary respectively.</div>"
			data-placement="bottom"
			data-html="true"
			style="padding-left:6px;padding-right:6px;">
			<span class="glyphicon glyphicon-info-sign" style="color:#00824A;"></span>
		</a>
	</div>

	<div class="modalForm-content">

		<form
			name="editPayLevel-form"
			id="editPayLevel-form"
			role="form"
			method="post"
			action="">

			<table>
				<tr>	
					<td class="modalLabel">Pay Level</td>
					<td>
						<input
							type="text"
							name="payLevel"
							id="payLevel-modalForm"
							class="form-control">
					</td>
				</tr>
				<tr>
					<td class="modalLabel">Job Code</td>
					<td id="jobCode-modalForm" class="textField"></td>
				</tr>
				<tr>
					<td class="modalLabel">Job Title</td>
					<td id="jobTitle-modalForm" class="textField"></td>
				</tr>
				<tr>
					<td class="modalLabel">Recommended Min Salary</td>
					<td>
						<input
							type="text"
							name="recMinSal"
							id="recMinSal-modalForm"
							class="form-control">
					</td>
				</tr>
				<tr>
					<td class="modalLabel">Recommended Med Salary</td>
					<td>
						<input
							type="text"
							name="recMedSal"
							id="recMedSal-modalForm"
							class="form-control">
					</td>
				</tr>
				<tr>
					<td class="modalLabel">Recommended Max Salary</td>
					<td>
						<input
							type="text"
							name="recMaxSal"
							id="recMaxSal-modalForm"
							class="form-control">
					</td>
				</tr>
				<tr>
					<td class="modalLabel">Benchmark</td>
					<td>
						<input
							type="text"
							name="benchmark"
							id="benchmark-modalForm"
							class="form-control">
					</td>
				</tr>
				<tr>
					<td colspan="2" style="text-align:center;">
						<input
							type="hidden"
							name="_jobCode"
							id="_jobCode-modalForm"
							value="">

						<input
							type="hidden"
							name="_row_idx"
							id="_row_idx"
							value="">

						<input
							type="hidden"
							name="_actMed"
							id="_actMed"
							value="">

						<input
							type="submit"
							name="submitEdit"
							id="submitEdit"
							class="btn btn-md btn-primary"
							value="Submit Changes"
							style="margin:1em 0 .5em 0;">
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>
<?php } // END Must be logged in ?>

<div id="minWageError-cont" class="modalForm">
	<div class="modalForm-header">
		<span class="glyphicon glyphicon-exclamation-sign" style="font-size:1.3em; padding-right:6px;"></span><b>Minimum Wage Error</b>
	</div>
	<div class="modalForm-content">
		</span>Error! Job Code <b>%jobCode%</b> was NOT updated!<br />
		Minimum Recommended Salary Must Not Fall Below Minimum Wage (<span class="text-danger">%minWage%</span>)
	</div>
</div>
