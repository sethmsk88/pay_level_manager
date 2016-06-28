$(document).ready(function() {
	// Activate datatable
	var payLevel_dataTable = $('#payLevels').DataTable({
		'order': [1, 'asc']
	});

	// Activate popovers
	$('[data-toggle="popover"]').popover();
});
