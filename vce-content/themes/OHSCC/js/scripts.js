$(document).ready(function() {
	// MOBILE MENU
	// make mobile menu not keyboard accessible when menu is closed
	$('.responsive-menu').find('*').attr('tabindex', -1);

	var positionLeft = -1;
	$('.responsive-menu-icon').on('click', function() {
		if (positionLeft < 0) {
			// make mobile menu keyboard accessible when menu is open
			$('.responsive-menu').find('ul li').attr('tabindex', 0);
			$('.responsive-menu').animate({
				left: '0%'
			}, 300);
			positionLeft = 1;
		} else {
			// make mobile menu not keyboard accessible when menu is closed
			$('.responsive-menu').find('*').attr('tabindex', -1);
			$('.responsive-menu').animate({
				left: '-100%'
			}, 300);
			positionLeft = -1;
		}
	});


	// ADMIN-TOGGLE
	if ($('.add-container').length) {
		if ($('.add-toggle').length) {
			$('.add-toggle').toggleClass('show');
			$('.add-container').not('.ignore-admin-toggle').hide();
		}
	}

	if ($('.ignore-admin-toggle').length) {
		$('.add-toggle').toggleClass('admin-toggle-active');
		$('.add-toggle').addClass('disabled');
	}

	$('.add-toggle').on('click', function() {
		$('.add-container').toggle();
		$('.add-toggle').toggleClass('admin-toggle-active');
	});

	if ($('.edit-container').length) {
		if ($('.edit-toggle').length) {
			$('.edit-toggle').css('visibility', 'visible');
			$('.edit-container').not('.ignore-admin-toggle').hide();
		}
	}

	$('.edit-toggle').on('click', function() {
		$('.edit-container').not('.ignore-admin-toggle').toggle();
		$('.edit-toggle').toggleClass('admin-toggle-active');
	});

	$('.close-message').on('click', function() {
		$(this).parent('.form-message').remove();
	});


	// Underline link for current page (desktop size only)
	$('#info-bar ul.menu-main li a[href]').each(function() {
		if (this.href === window.location.href) {
			$(this).addClass('active');
		}
	});


	// Add margin to titlebar on homepage
	$('.pbc-home-wrapper').siblings('.title-bar').addClass('title-bar__home-page');


	// Add focus to cycle
	$('.pbccycle-link, .pbccycle-link-complete').focus(function() {
		$(this).parent('.pbc-cycles__table-item').addClass('pbc-cycles__table-item-active');
	}).blur(function() {
		$('.pbc-cycles__table-item').removeClass('pbc-cycles__table-item-active');
	});


	// add aria-label to multi select forms for screen reader and add highlight class for accessibility
	$(document).on('focus', '.select2-selection__rendered', function() {
		$('.select2-selection__rendered').attr('aria-label', 'participants');
		$(this).closest('.input-label-style').addClass('highlight');
	}).on('blur', '.select2-selection__rendered', function() {
		$('.select2-selection__rendered').attr('aria-label', 'participants');
		$(this).closest('.input-label-style').removeClass('highlight');
	});


	// WELCOME MODAL
	$('.got-it, .close').click(function() {
		$('#welcome-modal').addClass('hide');
	});


	// COMPLETED ITEM MODAL
	var formToSubmit;
	var checkmark;

	function drop(x) {
		$('.confetti-' + x).animate({
			top: '100%',
			left: '+=' + Math.random() * 15 + '%'
		}, Math.random() * 3000 + 3000, function() {
			$(this).remove();
		});
	}

	function create(i) {
		var width = Math.random() * 8;
		var height = width * 0.4;
		var colorIdx = Math.ceil(Math.random() * 3);
		var color = 'red';
		switch (colorIdx) {
		case 1:
			color = 'yellow';
			break;
		case 2:
			color = 'blue';
			break;
		default:
			color = 'red';
		}
		$('<div class="confetti-' + i + ' ' + color + '"></div>').css({
			width: width + 'px',
			height: height + 'px',
			top: -Math.random() * 20 + '%',
			left: Math.random() * 100 + '%',
			opacity: Math.random() + 0.5,
			transform: 'rotate(' + Math.random() * 360 + 'deg)'
		}).appendTo('.modal-content');
		drop(i);
	}

	// confetti animation
	function startConfetti() {
		for (var i = 0; i < 250; i++) {
			create(i);
		}
	}

	$('.completed-checkbox').change(function() {
		checkmark = $(this).children('input');
		formToSubmit = $(this).closest('form');

		if ($(checkmark).is(':checked')) {
			$('#completed-modal').removeClass('hide');
			startConfetti();
		}
	});

	$('.cancel-completed, .close-completed-modal').click(function() {
		$('#completed-modal').addClass('hide');
		$(checkmark).prop('checked', false);
	});

	$('.complete-btn').click(function() {
		$('#completed-modal').addClass('hide');
		formToSubmit.submit();
		$(checkmark).prop('checked', true);
		$('#view-cycles-tab .accordion-container').removeClass('hide');
	});

	// When the user clicks anywhere outside of the modal, close it
	var completedModal = document.getElementById('completed-modal');

	$(window).click(function(event) {
		if (event.target === completedModal) {
			$('#completed-modal').addClass('hide');
			$(checkmark).prop('checked', false);
		}
	});


	// ANIMATED CHECKMARK
	$('#checkmark-svg').on('click', function() {
		var svg = $(this);
		svg.removeClass('run-animation').width();
		svg.addClass('run-animation');
		return false;
	});


	// MODAL FOR UNCHECKING CHECKMARK
	$('.completed-checkbox').change(function() {
		checkmark = $(this).children('input');
		formToSubmit = $(this).closest('form');

		if (!$(checkmark).is(':checked')) {
			$('#unchecked-modal').removeClass('hide');
		}
	});

	$('.cancel-uncheck, .close-unchecked-modal').click(function() {
		$('#unchecked-modal').addClass('hide');
		$(checkmark).prop('checked', true);
	});

	$('.unchecked-btn').click(function() {
		$('#unchecked-modal').addClass('hide');
		formToSubmit.submit();
		$(checkmark).prop('checked', false);
		$('#view-cycles-tab .accordion-container').removeClass('hide');
	});

	// When the user clicks anywhere outside of the modal, close it
	var uncheckedModal = document.getElementById('unchecked-modal');

	$(window).click(function(event) {
		if (event.target === uncheckedModal) {
			$('#unchecked-modal').addClass('hide');
			$(checkmark).prop('checked', true);
		}
	});


	// PBC Steps - IE footer hack
	// Get tab container height
	var tableContentHeight = 0;

	$('#view-steps-tab').children('.pbc-cycles__table-item').each(function() {
		tableContentHeight += $(this).height();
	});

	// keeps footer at the bottom in Internet Explorer 10 and below
	if (/MSIE \d/.test(navigator.userAgent)) {
		var cyclesMarginHeight = tableContentHeight;

		$('#main').css('margin-bottom', cyclesMarginHeight);
	}


	// Manage Users 2 - IE footer hack
	// keeps footer at the bottom in Internet Explorer 10 and below
	if (/MSIE \d/.test(navigator.userAgent)) {
		var marginHeight = $('.table-style').height();

		$('#main').css('margin-bottom', marginHeight);
	}


	// Comments & Resources
	// add <hr> after each media section
	$('.media-item ~ .comments-container').append(
		$('<br><br><hr>')
	);

	$('.resources_comments_accordion_content > .comments-container:last-child').prepend(
		$('<p class="resources_comments_accordion-heading">')
			.text('Comments')
	);



		// MODAL FOR SECURITY STATEMENT

		$('.security-statement').click(function(e) {
			
			 var modalPositioner = $('#admin-bar');
			var message = '<p>The Coaching Companion is accessed through the Early Childhood Learning and Knowledge Center (ECLKC), from the Office of Head Start.<a href="https://eclkc.ohs.acf.hhs.gov/professional-development/head-start-coaching-companion/security-statement" target="_blank"> Read the security statement on the ECLKC</a>.</p>';
			modalPositioner.prepend('<div class="security-statement-modal"><div class="modal-content">' + message + '</div></div>');
			e.preventDefault();
		});

		// When the user clicks anywhere outside of the modal, close it
		// window.onclick = function(event) {
		// 	if (event.target.id != "image_in_modal_div") {
		// 	   $("#modal_div").hide();
		// 	}
		//  }
	
		$(window).click(function(event) {
			// alert($(event.target).attr('class'));
			var clickedClass = $(event.target);
			// alert(clickedClass);
			if ($(event.target).hasClass("modal-content") || $(event.target).hasClass("security-statement")) {
				// do nothing
			} else {
				$('.security-statement-modal').hide();
			}
		});
});
