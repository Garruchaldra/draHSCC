$(document).ready(function() {

	// admin-toggle

	if ($('.add-container').length) {
		if ($('.add-toggle').length) {
			$('.add-toggle').css('visibility', 'visible');
			$('.add-container').not('.ignore-admin-toggle').hide();
		}
	}
	
	if ($('.ignore-admin-toggle').length) {
		$('.add-toggle').toggleClass('admin-toggle-active');
		$('.add-toggle').addClass('disabled');
	}

	$('.add-toggle').on('click touchend', function(e) {
		$('.add-container').toggle();
		$('.add-toggle').toggleClass('admin-toggle-active');
	});

	if ($('.edit-container').length) {
		if ($('.edit-toggle').length) {
			$('.edit-toggle').css('visibility', 'visible');
			$('.edit-container').not('.ignore-admin-toggle').hide();
		}
	}

	$('.edit-toggle').on('click touchend', function(e) {
		$('.edit-container').not('.ignore-admin-toggle').toggle();
		$('.edit-toggle').toggleClass('admin-toggle-active');
	});

	$('.close-message').on('click touchend', function(e) {
		$(this).parent('.form-message').remove();
	});

});