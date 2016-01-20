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
			$benchID = 0; // Incremental benchIDs
			// For each row in query
			while ($row = $sel_all_payLevels_res->fetch_assoc()){
			?>
			<tr>
				<td><?php echo $row['PayLevel']; ?></td>
					<td><?php echo $row['JobCode']; ?></td>
					<td><?php echo $row['JobTitle']; ?></td>
					<td><?php echo '$' . number_format($row['MinSal'], 2, '.', ','); ?></td>
					<td><?php echo '$' . number_format($row['MedSal'], 2, '.', ','); ?></td>
					<td><?php echo '$' . number_format($row['MaxSal'], 2, '.', ','); ?></td>
					<td><?php echo '$' . number_format($row['ActMinSal'], 2, '.', ','); ?></td>
					<td><?php echo '$' . number_format($row['ActMedSal'], 2, '.', ','); ?></td>
					<td><?php echo '$' . number_format($row['ActMaxSal'], 2, '.', ','); ?></td>
					<td><?php /* Benchmark */ ?></td>
					<td>
						<?php 
						echo convertFLSA($row['FLSA'], 'descr');
						?>
					</td>
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

<?php mysqli_close($conn); ?>
		