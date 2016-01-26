/*
	When a row in the table is clicked,
	a modal is shown containing an edit form for that row.
*/
function rowClickHandler(e) {	
	/*
		Get reference to element that was clicked
		IE sometimes uses srcElement instead of target
	*/
	var $target = e.target ? $(e.target) : $(e.srcElement);
	var $targetRow = $target.parent();
	
	var val_array = [];

	/* Store all of the row's cell values in an array */
	$targetRow.children().each(function(i, val) {
		$val = $(val).text();
		val_array[i] = $val;
	});

	/* Populate fields in modal form */
	$('#payLevel-modalForm').text(val_array[0]);
	$('#jobCode-modalForm').text(val_array[1]);
	$('#jobTitle-modalForm').text(val_array[2]);
	$('#recMinSal-modalForm').val(val_array[3]);
	$('#recMedSal-modalForm').val(val_array[4]);
	$('#recMaxSal-modalForm').val(val_array[5]);
	$('#benchmark-modalForm').val(val_array[9]);
	$('#_jobCode-modalForm').val(val_array[1]);
	$('#_row_idx').val($targetRow[0].sectionRowIndex);
	$('#_col_idx').val($target[0].cellIndex);
	$('#_actMed').val(val_array[7]);

	/*console.log('r=' + $targetRow[0].sectionRowIndex);
	console.log('c=' + $target[0].cellIndex);*/

	// Show overlay
	$('#overlay').fadeIn();

	$modal = $('#editPayLevel-cont');

	// Set width of modal
	//$modal.width(400);

	// Set position of modal to be at center of screen
	var top = $target.offset().top / 2;
	var left = Math.max($(window).width() - $modal.outerWidth(), 0) / 2;
	$modal.css({
		"top": top,
		"left": left
	});

	// Show the new form
	$modal.slideDown();
}


/**
 * Update recommended salaries based on benchmark
 */
function updateRecSals(val_array) {
	var recMin = parseMoney(val_array[3]);
	var recMed = parseMoney(val_array[4]);
	var recMax = parseMoney(val_array[5]);
	var actMed = parseMoney(val_array[7]);
	var benchmark = parseMoney(val_array[9]);

	var newRecMin = benchmark * .8; // 80% of benchmark
	var newRecMax = benchmark * 1.2; // 120% of benchmark
	var newRecMed = recMed; // Default is old recommended median

	if ((actMed < (benchmark * .9)) ||
		(actMed > (benchmark * 1.1))) {
		newRecMed = median(newRecMin, newRecMax);
	}

	/* AJAX request to update values in table */
	$.ajax({
		type: 'post',
		url: './content/act_benchmark.php',
		data: {
			'newRecMin': newRecMin,
			'newRecMax': newRecMax,
			'newRecMed': newRecMed,
			'jobCode': val_array['jobCode']
		},
		success: function(response) {
			// Update fields in datatable
			console.log("Success");
		}
	});
	
}


/**
 *	Calculate median of two numbers
 *	
 *	@param num1
 *	@param num2
 *	@return median number
 */
function median(num1, num2) {
	
	/* Swap numbers if num1 is largest */
	if (num1 > num2) {
		var tmp = num1;
		num1 = num2;
		num2 = tmp;
	}
	return num1 + (num2 - num1) / 2;
}


/* After page finishes loading */
$(document).ready(function(){

	// Activate datatable
	var payLevel_dataTable = $('#payLevels').DataTable({
		'order': [1, 'asc']
	});

	/* Prepare overlay for modals */
	$overlay = $('<div id="overlay"></div>');
	$overlay.hide();
	$('body').append($overlay);

	/* Attach click handler to overlay */
	$('#overlay').click(function() {

		/*
			Hide each element with class="modal" that
			is currently visible, then hide the overlay.
		*/
		$('.modalForm:visible').each(function() {
			$(this).slideUp(function() {
				$('#overlay').fadeOut();
			});
		})
	});

	/* Attach onSubmit event handler to modal form */
	$('#editPayLevel-form').on('submit', function(e) {
		e.preventDefault();

		/* AJAX request to update table entry */
		$.ajax({
			type: 'post',
			url: './content/act_payLevel_edit.php',
			data: $('#editPayLevel-form').serialize(),
			success: function(response) {

				// Select the cell that was clicked
				var row_idx = $('#_row_idx').val();
				var col_idx = $('#_col_idx').val();
				var cell = payLevel_dataTable.cell(row_idx, col_idx);
				
				/*console.log('row: ' + row_idx);
				console.log('col: ' + col_idx);
				console.log('cell data: ' + cell.data());*/

				// Array for holding values from modal form
				var modalFields_array = [];

				// Get values from each text input in the modal
				// Populate respective cells in the table
				$('#editPayLevel-form').find('input[type="text"]').each(function(idx, el) {

					$el = $(el); // Convert to jQuery object

					// Add key value pair to array
					modalFields_array[$el.attr('col-idx')] = $el.val();

					// Update value in cell
					var cell = payLevel_dataTable.cell(row_idx, $el.attr('col-idx'));
					cell.data($el.val());
				});

				/* Add actual median salary to array */
				$actMed_field = $('#_actMed');
				modalFields_array[$actMed_field.attr('col-idx')] = $actMed_field.val();

				// Add job code to array
				modalFields_array['jobCode'] = $('#_jobCode-modalForm').val();

				var benchmark = parseMoney(modalFields_array[9]);
				
				/* If benchmark is not empty, update recommended salaries */
				if (benchmark.length)
					updateRecSals(modalFields_array);

				/* Clear all fields in modal */
				$('input[type="hidden"]').val('');
				$('input[type="text"]').val('');
				$('td.textField').text('');

				/*
					Hide each element with class="modal" that
					is currently visible, then hide the overlay.
				*/
				$('.modalForm:visible').each(function() {
					$(this).slideUp(function() {
						$('#overlay').fadeOut();
					});
				})
			}
		});
	});
});
