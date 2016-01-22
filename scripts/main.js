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

				// Get values from each text input in the modal
				// Populate respective cells in the table
				$('#editPayLevel-form').find('input[type="text"]').each(function(idx, el) {

					$el = $(el); // Convert to jQuery object

					var cell = payLevel_dataTable.cell(row_idx, $el.attr('col-idx'));
					cell.data($el.val());
					//console.log(el.value);
				});

				// Redraw table
				payLevel_dataTable.draw();
				
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
