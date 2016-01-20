<?php
	
	include_once $_SERVER['DOCUMENT_ROOT'] . 'bootstrap/apps/shared/db_connect.php';

	$sel_all_payLevels = "
		SELECT *
		FROM hrodt.pay_levels
		ORDER BY JobCode ASC
	";

	// Run Query
	if (!$qry_result = $conn->query($sel_all_payLevels)){
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
				<th></th>
			</tr>
		</thead>
		<tbody>
			<form
				name="payLevelForm"
				id="payLevelForm"
				role="form"
				method="post"
				action="">

				<?php

				$benchID = 0; // Incremental benchIDs
				// For each row in query
				while ($row = $qry_result->fetch_assoc()){
				?>
				<tr>
					<td><?php echo $row['PayLevel']; ?></td>
						<td><?php echo $row['JobCode']; ?></td>
						<td><?php echo $row['JobTitle']; ?></td>
						<td><?php echo '$' . number_format($row['MinSal'], 2, '.', ','); ?></td>
						<td><?php echo '$' . number_format($row['MedSal'], 2, '.', ','); ?></td>
						<td><?php echo '$' . number_format($row['MaxSal'], 2, '.', ','); ?></td>
						<td></td>
						<td></td>
						<td></td>
						<td>
							<input
								type="text"
								name="bench<?php echo ++$benchID; ?>"
								id="bench<?php echo $benchID; ?>">
						</td>
						<td><?php echo $row['FLSA']; ?></td>
						<td><?php echo $row['UnionCode']; ?></td>
						<td><?php echo $row['OldPayGrade']; ?></td>
						<td><?php echo $row['JobFamily']; ?></td>
						<td><?php echo $row['PayPlan']; ?></td>
						<td><?php echo $row['Contract']; ?></td>
						<td><?php echo $row['IPEDS_SOCs']; ?></td>			

						<td class="center">
							<button
								id="edit_<?php echo $row['PLID']; ?>"
								type="button"
								class="edit_button btn btn-default confirm"
								style="margin-right:4px;"
								data-toggle="confirmation">
								
								<span class="edit_button glyphicon glyphicon-pencil"></span>
							</button>
							<button
								id="del_<?php echo $row['PLID']; ?>"
								type="button"
								class="del_button btn btn-default">
								
								<span class="del_button glyphicon glyphicon-remove"></span>
							</button>
						</td>
					</tr>
				<?php } ?>
			</form>
		</tbody>
	</table>


</div>
		



<?php mysqli_close($conn); ?>
		