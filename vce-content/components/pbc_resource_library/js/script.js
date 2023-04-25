// function onformerror(formsubmitted,data) {

// 	if (data.form == "search") {
// 		$(formsubmitted).prepend('<div class="form-message form-error">' + data.message + '</div>');
// 	}

// }

function onformsuccess(formsubmitted,data) {

	// console.log(formsubmitted);
	// console.log(data);

	if (data.procedure == "search" && data.action == "reload") {

		// $(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
	
		setTimeout(function(){
			window.location.href = data.url
		}, 200);

	}
	
	if (data.procedure == "create" && data.action == "reload") {

		// $(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
	
		setTimeout(function(){
			window.location.href = data.url
		}, 200);

	}

	if (data.procedure == "update") {

		$(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
	
		setTimeout(function(){
			window.location.href = data.url
		}, 2000);

	}

	if (data.procedure == "delete") {
		$(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
	
		setTimeout(function(){
			window.location.href = data.url
		}, 2000);

	}

}



$(document).ready(function() {
	// $('.select-taxonomy').select2().on('change', function(e) {
	// 	var selections = new Array();
	// 	var closestSelectTaxonomy = $(this).closest('.clickbar-content').find('.select-taxonomy');

	// 	// var all = $('.select-taxonomy');
	// 	$.each(closestSelectTaxonomy, function (index, value) {
	// 		var data = $(value).select2('data');
	// 		$.each(data, function (index, value) {
	// 			selections.push(value.id);
	// 		});
	// 	});
	// 	var closestTaxonomySelected = $(this).closest('.clickbar-content').find('.taxonomy-selected');
	// 	closestTaxonomySelected.val('|' + selections.join('|') + '|');
	// });

        $('.select-taxonomy').select2().on('change', function(e) {
			
                var selections = new Array();
                var all = $('.select-taxonomy');
                $.each(all, function (index, value) {
                        var data = $(value).select2("data");
                        $.each(data, function (index, value) {
                                selections.push(value.id);
                        });
				});
		
                $('.taxonomy-selected').val('|' + selections.join('|') + '|');
        });


	$(document).on('click', '.clear-button', function(e) {
		e.preventDefault();
		$('.resource-container').show();
		var reload_url = $(this).attr('reload_url');

		var menu = 	$(".clear-button");
		var action = $(this).attr("action");
		console.log(action);
		postdata = [];
		postdata.push(
            {name: 'dossier', value: menu.attr('dossier')},
			{name: 'inputtypes', value: menu.attr('inputtypes')},
			{name: 'reload_url', value: menu.attr('reload_url')}
        );
        
		$.post(action, postdata, function(data) {
			console.log('data: ' + data);	
			window.location.href = reload_url;		
		}, "html");
		
	});

	$(document).on('click', '.manage_taxonomy', function(e) {
		var accordion_title = $(this).find(".accordion-title");
		var accordion_content = accordion_title.attr('aria-controls');
		// alert(accordion_content);
		// $('#' + accordion_content).replaceWith("<div>coming soon</div>");

		// var menu = 	$(".clear-button");
		// var action = $(this).attr("action");
		// console.log(action);
		// postdata = [];
		// postdata.push(
        //     {name: 'dossier', value: menu.attr('dossier')},
		// 	{name: 'inputtypes', value: menu.attr('inputtypes')},
		// 	{name: 'reload_url', value: menu.attr('reload_url')}
        // );
        
		// $.post(action, postdata, function(data) {
		// 	// console.log('data: ' + data);	
		// 	window.location.href = reload_url;		
		// }, "html");
		
	});


	// create add menu
	$(document).on('click', '.plus-minus-icon', function(e) {
		e.preventDefault();
		if ($('.my-library-menu').length === 0) {
			var component_title = $(this).closest('form').find('.add-button-info').attr('resource_requester_title');
			// console.log($(this));
			$(this).siblings('.menu-container').append(
				$('<div class="my-library-menu shadow">')
					.append(
						$('<button class="button__primary">')
							.text('Add to: ' + component_title)
					)
			); 
			// if the menu is already a child of this element, toggle hide
		} else if ($(this).siblings('.menu-container').children().length > 0) {
			$(this).blur();
			$('.my-library-menu').toggleClass('hide');
		} else {
			$('.my-library-menu').removeClass('hide');
			$(this).siblings('.menu-container').append($('.my-library-menu'));
		}
	});


	// append search results table under search bar
	$('.search-results-table').appendTo('#search-resources');

	if ($('.search-results-table').length) {
		$('.resource-container').hide();
	}
});


