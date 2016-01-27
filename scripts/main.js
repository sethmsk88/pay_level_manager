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
	var row_idx = $targetRow.attr('id').match(/[0-9]+/)[0];
		
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
	$('#_row_idx').val(row_idx);
	$('#_actMed').val(val_array[7]);

	// Show overlay
	$('#overlay').fadeIn();

	$modal = $('#editPayLevel-cont');

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


/**
 *	Set the value in a cell
 *	
 *	@param row index of cell row
 *	@param col index of cell column
 *	@param val the value to set as the cell's contents
 */
function setCell(row, col, val) {
	var row = $('#row-' + row);
	var col = $('#col-' + col).index();

	row.find('td').eq(col).text(val);
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
			dataType: 'json', // data type for response
			success: function(response) {

				// Get index of clicked row
				var row_idx = $('#_row_idx').val();

				/* Update cells in table */
				setCell(row_idx, 3, response['recMinSal'].formatMoney());
				setCell(row_idx, 4, response['recMedSal'].formatMoney());
				setCell(row_idx, 5, response['recMaxSal'].formatMoney());

				if (response['benchmark'] > 0)
					setCell(row_idx, 9, response['benchmark'].formatMoney());
				else
					setCell(row_idx, 9, ''); // Clear cell

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
