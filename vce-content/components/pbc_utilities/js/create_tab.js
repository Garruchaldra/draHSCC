$(document).ready(function() {

	$('.tabs__tab').click(function() {
		// if the tab has an attribute for reloading the page, do so, and if it has a tab_target, append it as a query string

		query_string = new Object();
		  $(this).each(function() {
			  $.each(this.attributes, function() {
				  if (this.name != 'id' && this.name != 'role' && this.name != 'class' && this.name != 'aria-controls' ) {
					// console.log(this.name, this.value);
					query_string[this.name] = this.value;

				  }
			  });
		});

		if ($(this).attr('reload')) {
			// window.location.href = window.location.pathname + '?'  + $.param({ tab_target: tab_target });
			window.location.href = window.location.pathname + '?' + $.param(query_string);
			return;
		}

		$('.tabs__tab').removeClass('tabs__active');
		$(this).addClass('tabs__active');
		showTabContent($(this).attr('id'));
	});


	function showTabContent(buttonId) {
		var contentClass = buttonId;
		$('.tabs__content-wrapper')
			.addClass('hide');
		$('.' + contentClass)
			.removeClass('hide');
	}
});
