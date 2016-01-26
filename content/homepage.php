<?php
	
	include_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap/apps/shared/db_connect.php';
	include_once './includes/functions.php';

	/*
		Select all columns in pay_levels table, and
		select the Min, Med, and Max for each JobCode
		in the all_active_fac_staff table
	*/
	$sel_all_payLevels_sql = "
		SELECT p.*, c.*
		FROM hrodt.pay_levels p
		JOIN (
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
				<th id="col-11">Union Code</th>
				<th id="col-12">Old Pay Grade</th>
				<th id="col-13">Job Family</th>
				<th id="col-14">Pay Plan</th>
				<th id="col-15">Contract</th>
				<th id="col-16">IPEDS SOCs</th>
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

				/*
					If queried values are not null, populate monetary
					variables with their respective formatted money string
				*/
				/* If adjusted rec min salary is not null, use it */
				if (!is_null($row['MinSalAdjusted']))
					$minSal = '$' . number_format($row['MinSalAdjusted'], 2, '.', ',');
				else if (!is_null($row['MinSal']) && $row['MinSal'] > 0)
					$minSal = '$' . number_format($row['MinSal'], 2, '.', ',');
				
				/* If adjusted rec med salary is not null, use it */
				if (!is_null($row['MedSalAdjusted']))
					$medSal = '$' . number_format($row['MedSalAdjusted'], 2, '.', ',');
				else if (!is_null($row['MedSal']) && $row['MedSal'] > 0)
					$medSal = '$' . number_format($row['MedSal'], 2, '.', ',');
				
				/* If adjusted rec max salary is not null, use it */
				if (!is_null($row['MaxSalAdjusted']))
					$maxSal = '$' . number_format($row['MaxSalAdjusted'], 2, '.', ',');
				else if (!is_null($row['MaxSal']) && $row['MaxSal'] > 0)
					$maxSal = '$' . number_format($row['MaxSal'], 2, '.', ',');
				
				if (!is_null($row['ActMinSal']) && $row['ActMinSal'] > 0)
					$actMinSal = '$' . number_format($row['ActMinSal'], 2, '.', ',');
				
				if (!is_null($row['ActMedSal']) && $row['ActMedSal'] > 0)
					$actMedSal = '$' . number_format($row['ActMedSal'], 2, '.', ',');
				
				if (!is_null($row['ActMaxSal']) && $row['ActMaxSal'] > 0)
					$actMaxSal = '$' . number_format($row['ActMaxSal'], 2, '.', ',');
				
				if (!is_null($row['Benchmark']) && $row['Benchmark'] > 0)
					$benchmark = '$' . number_format($row['Benchmark'], 2, '.', ',');

			?>

			<tr
				id="row-<?php echo $row_idx; ?>"
				class="editable"
				onclick="rowClickHandler(event);">

				<td><?php echo $row['PayLevel']; ?></td>
					<td><?php echo $row['JobCode']; ?></td>
					<td><?php echo $row['JobTitle']; ?></td>
					<td><?php echo $minSal; ?></td>
					<td><?php echo $medSal ?></td>
					<td><?php echo $maxSal; ?></td>
					<td><?php echo $actMinSal; ?></td>
					<td><?php echo $actMedSal; ?></td>
					<td><?php echo $actMaxSal; ?></td>
					<td><?php echo $benchmark; ?></td>
					<td><?php echo convertFLSA($row['FLSA'], 'descr');?></td>
					<td><?php echo $row['UnionCode']; ?></td>
					<td><?php echo $row['OldPayGrade']; ?></td>
					<td><?php echo $row['JobFamily']; ?></td>
					<td><?php echo $row['PayPlan']; ?></td>
					<td><?php echo convertYesNo($row['Contract']); ?></td>
					<td><?php echo $row['IPEDS_SOCs']; ?></td>			
				</tr>
			<?php
				$row_idx++;
			}
			?>
		</tbody>
	</table>
</div>

<!-- Edit Pay Level Form (absolutely positioned modal) -->
<div
	id="editPayLevel-cont"
	class="modalForm">

	<form
		name="editPayLevel-form"
		id="editPayLevel-form"
		role="form"
		method="post"
		action="">

		<table>
			<tr>	
				<td class="modalLabel">Pay Level</td>
				<td id="payLevel-modalForm" class="textField"></td>
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
				<td class="modalLabel">Recommeded Min Salary</td>
				<td>
					<input
						type="text"
						name="recMinSal"
						id="recMinSal-modalForm"
						class="form-control"
						col-idx="3">
				</td>
			</tr>
			<tr>
				<td class="modalLabel">Recommeded Med Salary</td>
				<td>
					<input
						type="text"
						name="recMedSal"
						id="recMedSal-modalForm"
						class="form-control"
						col-idx="4">
				</td>
			</tr>
			<tr>
				<td class="modalLabel">Recommeded Max Salary</td>
				<td>
					<input
						type="text"
						name="recMaxSal"
						id="recMaxSal-modalForm"
						class="form-control"
						col-idx="5">
				</td>
			</tr>
			<tr>
				<td class="modalLabel">Benchmark</td>
				<td>
					<input
						type="text"
						name="benchmark"
						id="benchmark-modalForm"
						class="form-control"
						col-idx="9">
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
						name="_col_idx"
						id="_col_idx"
						value="">

					<input
						type="hidden"
						name="_actMed"
						id="_actMed"
						value=""
						col-idx="7">

					<input
						type="submit"
						name="submitEdit"
						id="submitEdit"
						class="btn btn-md btn-primary"
						value="Submit Changes"
						style="margin:1em 0 .5em 0;">
				</td>
			</div>
		</table>
	</form>
</div>




<?php mysqli_close($conn); ?>
		