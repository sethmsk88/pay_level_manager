<?php
	
	include_once $_SERVER['DOCUMENT_ROOT'] . 'bootstrap/apps/shared/db_connect.php';
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
				<th>Pay Level</th>
				<th>Job Code</th>
				<th>Job Title</th>
				<th>Recommended<br />Min Salary</th>
				<th>Recommended<br />Med Salary</th>
				<th>Recommended<br />Max Salary</th>
				<th>Actual Min Salary</th>
				<th>Actual Med Salary</th>
				<th>Actual Max Salary</th>
				<th>Benchmark</th>
				<th>FLSA</th>
				<th>Union Code</th>
				<th>Old Pay Grade</th>
				<th>Job Family</th>
				<th>Pay Plan</th>
				<th>Contract</th>
				<th>IPEDS SOCs</th>
			</tr>
		</thead>
		<tbody>
			<?php
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
				if (!is_null($row['MinSal']) && $row['MinSal'] > 0)
					$minSal = '$' . number_format($row['MinSal'], 2, '.', ',');
				
				if (!is_null($row['MedSal']) && $row['MedSal'] > 0)
					$medSal = '$' . number_format($row['MedSal'], 2, '.', ',');
				
				if (!is_null($row['MaxSal']) && $row['MaxSal'] > 0)
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

			<tr class="editable" onclick="rowClickHandler(event);">
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
			<?php } ?>
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
				<td id="payLevel-modalForm"></td>
			</tr>
			<tr>
				<td class="modalLabel">Job Code</td>
				<td id="jobCode-modalForm"></td>
			</tr>
			<tr>
				<td class="modalLabel">Job Title</td>
				<td id="jobTitle-modalForm"></td>
			</tr>
			<tr>
				<td class="modalLabel">Recommeded Min Salary</td>
				<td>
					<input
						type="text"
						name="recMinSal"
						id="recMinSal-modalForm"
						class="form-control">
				</td>
			</tr>
			<tr>
				<td class="modalLabel">Recommeded Med Salary</td>
				<td>
					<input
						type="text"
						name="recMedSal"
						id="recMedSal-modalForm"
						class="form-control">
				</td>
			</tr>
			<tr>
				<td class="modalLabel">Recommeded Max Salary</td>
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
		