$(document).ready(function() {
	$('[data-toggle="popover"]').popover({
		trigger: 'hover',
		placement: function(context, source) {
			var position = $(source).position();

			if (position.top < 200) {
				return "bottom";
			} else {
				return "top";
			}
		}
	});
});
