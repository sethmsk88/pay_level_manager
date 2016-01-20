<?php
	
	// Connect to DB
	$conn = mysqli_connect($dbInfo['dbIP'], $dbInfo['user'], $dbInfo['password'], $dbInfo['dbName']);
	if ($conn->connect_errno){
		echo "Failed to connect to MySQL: (" . $conn->connect_errno . ") " . $conn->connect_error;
	}

	$sql = "
		SELECT *
		FROM " . $payLevels_table . "
		ORDER BY JobCode ASC";

	// Run Query
	if (!($qry_result = $conn->query($sql))){
		echo "Query failed: (" . $conn->errno . ") " . $conn->error;
		echo "<br />" . $sql;
	}

	// Close DB connection
	mysqli_close($conn);
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
			<form name="payLevelForm" action="">

				<?php

				$benchID = 0; // Incremental benchIDs
				// For each row in query
				while ($row = $qry_result->fetch_assoc()){
					echo '<tr>';
						echo '<td>' . $row['PayLevel'] . '</td>';
						echo '<td>' . $row['JobCode'] . '</td>';
						echo '<td>' . $row['JobTitle'] . '</td>';
						echo '<td>$' . number_format($row['MinSal'], 2, '.', ',') . '</td>';
						echo '<td>$' . number_format($row['MedSal'], 2, '.', ',') . '</td>';
						echo '<td>$' . number_format($row['MaxSal'], 2, '.', ',') . '</td>';
						echo '<td>' . '</td>';
						echo '<td>' . '</td>';
						echo '<td>' . '</td>';
						echo '<td>';
							echo '<input ' .
									'type="text" ' .
									'name="bench' . ++$benchID . '" ' .
									'id="bench' . ++$benchID .
									'">';
						echo '</td>';
						echo '<td>' . $row['FLSA'] . '</td>';
						echo '<td>' . $row['UnionCode'] . '</td>';
						echo '<td>' . $row['OldPayGrade'] . '</td>';
						echo '<td>' . $row['JobFamily'] . '</td>';
						echo '<td>' . $row['PayPlan'] . '</td>';
						echo '<td>' . $row['Contract'] . '</td>';
						echo '<td>' . $row['IPEDS_SOCs'] . '</td>';			

						echo '<td class="center">';
							echo '<button ' .
									'id="edit_' . $row['PLID'] . '" ' .
									'type="button" ' .
									'class="edit_button btn btn-default confirm" ' .
									'style="margin-right:4px;" ' .
									'data-toggle="confirmation" ' .
									'>';
								echo '<span class="edit_button glyphicon glyphicon-pencil"></span>';
							echo '</button>';
							echo '<button ' .
									'id="del_' . $row['PLID'] . '" ' .
									'type="button" ' .
									'class="del_button btn btn-default" '.
									'>';
								echo '<span class="del_button glyphicon glyphicon-remove"></span>';
							echo '</button>';
						echo '</td>';
					echo '</tr>';
				}
				?>
			</form>
		</tbody>
	</table>


</div>
		




		





