$(document).ready(function() {
	$('.resource-library').append('<div class="lds-spinner category-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>');

	$('.resources-list').tablesorter();

	$('.search-form').on('submit', function(e) {
		e.preventDefault();

		var submittable = true;

		var typetest = $(this).find('input[type=text]');
		typetest.each(function(index) {
			if ($(this).val() == '' && $(this).attr('tag') == 'required') {
				$(this).closest('label').addClass('highlight-alert');
				submittable = false;
			}
		});

		if (submittable) {

			var formsubmitted = $(this);

			$(formsubmitted).find('.clickbar-container').remove();

			var postdata = $(this).serializeArray();

			$.post($(this).attr('action'), postdata, function(data) {
				if (data.response) {
					var results = displayResources(formsubmitted, data.message);

					if (results.count > 0) {
						$(formsubmitted).append(results.content);
						remove_unwanted_placeholders();
					} else {
						var $content = '<div>No Search Results Found</div>';
						$(formsubmitted).append($content);
					}
				}
			}, 'json');
		}
	});

	$(document).on('click', '.level-title', function(e) {
		$('.level-title').removeClass('selected-category');
		$('.level-title').attr('aria-expanded', 'false');
		$(this).removeAttr('resources');

		// toggle accordion arrows
		$(this).parent().siblings('.resource-library-category').find('.arrow').removeClass('arrow-open');

		// remove background color
		$(this).parent().siblings('.resource-library-category-expanded').find('.level-title').removeClass('level-title-expanded');

		// collapse descendants of sibling accordions
		$(this).parent().siblings('.resource-library-category-expanded').find('.resource-library-category-expanded').children('.resource-library-category').slideUp();

		// collapse sibling accordions
		$(this).parent().siblings('.resource-library-category-expanded').removeClass('resource-library-category-expanded').children('.level-title').siblings('.resource-library-category').slideUp();

		if ($(this).hasClass('selected-category')) {
			$('.resource-results').empty();
		}

		$(this).toggleClass('selected-category level-title-expanded');
		$(this).parent().toggleClass('resource-library-category-expanded');
		$(this).children('.arrow').toggleClass('arrow-open');
		$(this).parent().children('.resource-library-category').slideToggle();
		$(this).attr('aria-expanded', 'true');

		if (!$(this).attr('resources')) {

			var formsubmitted = $(this);
			var formInfo = $(this).closest('.resource-library');

			postdata =  [];
			postdata.push(
				{name: 'dossier', value: $(formInfo).attr('dossier')},
				{name: 'inputtypes', value: $(formInfo).attr('inputtypes')},
				{name: 'item_id', value: $(this).attr('item_id')}
			);

			// adds spinner while waiting for resources to load
			$('.resource-results')
				.empty()
				.append('<div class="lds-spinner results-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>');

			$.post($(formInfo).attr('action'), postdata, function(data) {
				// console.log('form');
				$('.resource-results').empty();
				if (data.response) {
					var results = displayResources(formsubmitted, data.message);
					$(formsubmitted).attr('resources', true);
					if (results.count > 0) {
						// this puts the result in
						// $(formsubmitted).after(results.content);
						$('.resource-results').html(results.content);
						remove_unwanted_placeholders();

						// keeps footer at the bottom in Internet Explorer 10 and below
						if (/MSIE \d/.test(navigator.userAgent)) {
							var marginHeight = $('.resource-container').height();

							$('#main').css('margin-bottom', marginHeight);
						}
					} else {
						$('.resource-results').empty();
					}
					// can be used as an else case to open the level where resources are found
					// $(formsubmitted).siblings('.resource-library-category').children('.level-title').click();
				}
			}, 'json');
		}

		
	});
	
	
	function displayResources(location, data) {

		var asynchronousContent = $('#asynchronous-content').children().clone();
		
		var displayResources = $(asynchronousContent).find('.display-resources').prop("outerHTML");
		
		var buildContent;
		var counter = 0;
	// console.log(location);
	// console.log(data);
	// This is where the '{' + metakey + '}' elements are filled out. The way this logic works, each metakey is only used once.
		$.each(jQuery.parseJSON(data),function(id, metadata) {
		
			counter++;
			
			var eachResource = displayResources;
						
			$.each(metadata, function(metakey, metavalue) {
				eachResource = eachResource.replace('{' + metakey + '}', metavalue);
				// console.log(metavalue);		
			});
			eachResource = eachResource.replace('{description}', ' ');
			buildContent += eachResource;
			
						
		});


		var newContent = $.parseHTML(buildContent);
		
		$(asynchronousContent).find('.display-resources').replaceWith(newContent);
		// $(asynchronousContent).addClass(location.attr('results'));
		
		// counter = (counter == 0) ? 'No' : counter;
		// var results = (counter == 1) ? 'Result' : 'Results';
		// $(asynchronousContent).find('.clickbar-title span').html(counter + ' Search ' + results + ' Found');
		
		var results = {};
		results.content = asynchronousContent;
		results.count = counter;
		

		return results;
		
		// $(location).append(asynchronousContent);
	
	}

// function remove_unwanted() {
// 	$(".description").each(function() {
// 		var descr = $( this ).html();
// 		console.log(descr);
// 		if (descr == "{description}") {
// 			console.log('yep');
// 			$( this ).text("defualt");
// 		}
// 	  });

// }

function remove_unwanted_placeholders() {
	// $(".description").text(function() {
	// 	console.log($(this).text() + "outside");
	// 	if($(this).text() == "{description}") {
	// 		console.log($(this).text() + "inside");
	// 		return $(this).text().replace("{description}", " aaa");
	// 	}
	// 	return $(this).text();
	// });


	// 	$(".description").each(function(i, obj) {
	// 		var self = obj;
	// 		// console.log(self);
	// 	var descr = $( this ).text();
	// 	// console.log(descr);
	// 	if (descr == "{description}") {
	// 		// console.log('yep' + descr);
	// 		$( this ).eq(i).text("default");
	// 	}
	//   });
}

	$(document).on('click', '.above-level-1', function() {
		if(window.innerWidth <= 767) {
		$('.resource-library').toggleClass('resource-library-expanded');
		$('.above-level-1 > .arrow').toggleClass('arrow-open');
		$('.above-level-1').toggleClass('above-level-1-expanded');

		if($('.resource-library').hasClass('resource-library-expanded')){
			$('.resource-results').addClass('resource-results-expanded');
		} else {
			$('.resource-results').removeClass('resource-results-expanded');
		}
		}
	})

	// get title id to use for aria tag on result content
	$(document).on('click', '.level-title', function() {
		var titleId = $(this).attr('id');
		$('.resource-results')
			.attr('id', titleId + "-content")
			.attr('aria-labelledby', titleId)
		
		$(this).toggleClass('.')
	});
});