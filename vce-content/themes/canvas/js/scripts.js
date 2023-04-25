$(document).ready(function() {

	/* coaching model navigation */

	var colapsed = true;

	$('.logo-container').on('click touchend', function(e) {
		e.preventDefault();
		if (colapsed === false) {
			$('.logo-color').hide();
			$('#logo').show();
			targetUrl = $('#' + e.target.id).parent().attr('href');
			// end neg var controls margin
			var containerTop = (parseInt($('.logo-container').css('top'), 10) * - 1);
			var containerMargin = (parseInt($('.logo-container').css('marginBottom'), 10) * - .5);
			$('#coaching-model').animate({width:"50px",top: containerTop , marginBottom: containerMargin}, 500,
			function() {
				$('#logo-small').show();
				$('#logo').hide();
				$('#logo-overlay').hide();
				if (targetUrl) {
					location.href = targetUrl;
				}
			});
			$('.coaching-model-link').unbind('mouseenter mouseleave');
		}
		if (colapsed === true) {
			$('#logo-small').hide();
			$('#logo').show();
			$('#logo-overlay').show();
			// end neg var controls margin
			var containerTop = (parseInt($('.logo-container').css('top'), 10) * -1);
			var containerMargin = (parseInt($('.logo-container').css('marginBottom'), 10) * -2);
			$('#coaching-model').animate({width:"90%",top: containerTop, marginBottom: containerMargin}, 500);
			$('.coaching-model-link').on('mouseenter touchstart', function() {
				var current = $(this).attr('color');
				$('#logo').hide();
				$('#logo-' + current).show();
			});	
			$('.coaching-model-link').on('mouseleave touchmove click', function() {
				$('.logo-color').hide();
				$('#logo').show();	
			});
		}
		colapsed = !colapsed;
	});


	$('#responsive-menu-icon').click(function() {
		$('#responsive-user-menu').slideUp();
		var menuId = $('#responsive-nav-menu');
		if (menuId.css('left') !== '0px') {
			menuId.animate({
			left: '0px'
			}, 500);
		} else {
			menuId.animate({
			left: '-100%'
			}, 500);
		}
	});

	$('#responsive-user-icon').click(function() {
		$('#responsive-nav-menu').animate({
			left: '-100%'
			}, 500);
		$('#responsive-user-menu').slideToggle();
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
		$('.edit-container').not('.ignore-admin-toggle').toggle().find('.clickbar-title').click();
		$('.edit-toggle').toggleClass('admin-toggle-active');
	});

	$('.close-message').on('click touchend', function(e) {
		$(this).parent('.form-message').remove();
	});

});