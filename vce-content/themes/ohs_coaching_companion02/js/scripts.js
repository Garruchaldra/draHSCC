$(document).ready(function() {

	jQuery('.responsive-menu-icon').on('click', function() {
		if (parseInt(jQuery('.responsive-menu').css('left')) < 0) {
			jQuery('.responsive-menu').animate({
				left: "0%"
			}, 300 );
		} else {
			jQuery('.responsive-menu').animate({
			  left: "-100%"
			  }, 300 );
		}
	});

//home link
	$('.site-link').on('click touchend', function(e) {
		var homebase = $('.site-info').attr('id');
		window.location.assign(homebase);	
	});

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
	
	


/* Supratitle definitions*/

		// click-bar (adds supratitle to vce.js to collapse)
	$('.clickbar-title').on('click touchend', function(e) {
		if ($(this).hasClass('disabled') !== true) {
			$(this).parent('.clickbar-container').children('.clickbar-supratitle').slideToggle();
		   	$(this).toggleClass('clickbar-active');
			$(this).parent('.clickbar-container').children('.clickbar-supratitle').toggleClass('clickbar-active');

		}
	});
	
	
	//allows click on supratitle to do the same as clickbar-title
	$('.clickbar-supratitle').on('click touchend', function(e) {
		if ($(this).hasClass('disabled') !== true) {
			$(this).parent('.clickbar-container').children('.clickbar-title').toggleClass('clickbar-closed');
			$(this).parent('.clickbar-container').children('.clickbar-content').slideToggle();
			$(this).slideToggle();
			
			$(this).toggleClass('clickbar-active');
			$(this).parent('.clickbar-container').children('.clickbar-title').toggleClass('clickbar-active');
			
			if ($(this).hasClass('clickbar-closed')) {
				$('.clickbar-group').not($(this).parent('.clickbar-container').children('.clickbar-title')).hide();
			} else {
				$('.clickbar-group').not($(this).parent('.clickbar-container').children('.clickbar-title')).show();
			}
		}
	});

/* Supratitle definitions*/
});