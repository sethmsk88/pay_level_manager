/*
	When a row in the table is clicked,
	a modal is shown containing an edit form for that row.
*/
function rowClickHandler(e) {
//$('tr.editable').click(function() {
	
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

	// Activate data table
	$('#payLevels').DataTable({
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
				// callback
			}
		});
	});


});
