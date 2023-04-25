$(document).ready(function() {
	
	if ($('.datepicker').length) {
		$('.datepicker').datepicker();
	}
	
	// edit/add toggle
	$('.click-tab').on('click touchend', function(e) {
		e.preventDefault();
		$('.tab').removeClass('active-tab');
		$(this).addClass('active-tab');
		$('.tab-content').removeClass('active-tab-content');
		var current_tab = '#' + $(this).attr('tab');
		$(current_tab).addClass('active-tab-content');
	});

});