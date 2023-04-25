$(document).ready(function() {
	// run if the sidebar exists
	if ($('#sidebar__container').length) {
		// figure out if user's browser is Internet Explorer 6-11
		var isIE = /* @cc_on!@ */false || !!document.documentMode;

		// If the user isn't on IE (which doesn't support MutationObserver), push the footer down if the sidebar is longer than the main content
		if (isIE === false) {
			MutationObserver = window.MutationObserver || window.WebKitMutationObserver;
			var tabsContainerHeight;
			var instructionsContainerHeight;

			var observer = new MutationObserver(function(mutations, observer) {
				// get container heights when a mutation occurs and set margin-bottom accordingly
				// this keeps the instructions from overlapping the footer
				tabsContainerHeight = $('.tabs__container').height();
				instructionsContainerHeight = $('.instructions__container').height() + 50;

				if (tabsContainerHeight < instructionsContainerHeight) {
					$('#main').css('margin-bottom', instructionsContainerHeight - tabsContainerHeight + 'px');
				}
			});

			// define what element should be observed by the observer
			var targetNode = document.getElementById('sidebar__container');

			observer.observe(targetNode, {
				subtree: true,
				attributes: true
			});
		}
	}

	// check local storage for whether to show or hide instructions
	var showInstructionsSelection = localStorage.getItem('showInstructions');

	if (showInstructionsSelection === 'false') {
		$('.instructions__label-hide, .sidebar__content-box').addClass('hide');
		$('.instructions__label-show').removeClass('hide');
		// expand width of tab content container
		$('.tabs__content-wrapper').addClass('tabs__content-wrapper-expanded');
	} else {
		$('.instructions__label-hide, .sidebar__content-box').removeClass('hide');
		$('.instructions__label-show').addClass('hide');
		// retract width of tab content container
		$('.tabs__content-wrapper').removeClass('tabs__content-wrapper-expanded');
	}

	$('.instructions__label-container').click(function() {
		toggleInstructions();
	});

	$('.instructions__label-container').keypress(function() {
		toggleInstructions();
	});

	function toggleInstructions() {
		$('.instructions__label, .sidebar__content-box').toggleClass('hide');
		// toggle width of tab content container
		$('.tabs__content-wrapper').toggleClass('tabs__content-wrapper-expanded');

		if ($('.sidebar__content-box').hasClass('hide')) {
			showInstructionsSelection = 'false';
		} else {
			showInstructionsSelection = 'true';
			var tabsContainerHeight = $('.tabs__container').height();
			var instructionsContainerHeight = $('.instructions__container').height() + 50;

			if (tabsContainerHeight < instructionsContainerHeight) {
				$('#main').css('margin-bottom', instructionsContainerHeight - tabsContainerHeight + 'px');
			}
		}

		// save show/hide selection to local storage
		localStorage.setItem('showInstructions', showInstructionsSelection);
	}
});
