function onformsuccess(formsubmitted,data) {

	// console.log(formsubmitted);

	if (data.procedure == "delete") {

		$(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
	
		setTimeout(function(){
			window.location.href = data.url
		}, 2000);

	}

	if (data.procedure == "get_resource") {

		$(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
	
		setTimeout(function(){
			window.location.href = data.url
		}, 2000);

	}

	if (data.procedure == "update") {

		$(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
	
		setTimeout(function(){
			window.location.href = data.url
		}, 2000);

	}

	if (data.procedure == "create" && data.action == "reload") {

		// $(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
	
		setTimeout(function(){
			window.location.href = data.url
		}, 200);

	}

	if (data.form == "edit") {

		$(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
	
		setTimeout(function(){
			window.location.href = data.action
		}, 2000);

	}



}



$(document).ready(function() {

	$('.pagination-button, .sort-icon').on('click', function(e) {
		e.preventDefault();
		postdata = [];
		postdata.push({name: 'dossier', value: JSON.stringify($(this).attr('dossier'))});
		postdata.push({name: 'inputtypes', value: $(this).attr('inputtypes')});
		postdata.push({name: 'pagination_current', value: $(this).attr('pagination')});
		postdata.push({name: 'sort_by', value: $(this).attr('sort')});
		postdata.push({name: 'sort_direction', value: $(this).attr('direction')});
		$.post($(this).attr('action'), postdata, function(data) {
			// console.log(data);
			window.location.reload(true);	
		}, 'json');
	});
	
	$('.pagination-input').on('change', function(e) {
		e.preventDefault();
		var pagination = $(this).val();
		if (!isNaN(parseFloat(pagination)) && isFinite(pagination)) {
			postdata = [];
			postdata.push({name: 'dossier', value: JSON.stringify($(this).attr('dossier'))});
			postdata.push({name: 'inputtypes', value: $(this).attr('inputtypes')});
			postdata.push({name: 'pagination_current', value: pagination});
			postdata.push({name: 'sort_by', value: $(this).attr('sort')});
			postdata.push({name: 'sort_direction', value: $(this).attr('direction')});
			$.post($(this).attr('action'), postdata, function(data) {
				// console.log(data);
				window.location.reload(true);	
			}, 'json');
		}
	});


	$('#create-title').change(function() {
	 	var name = $(this).val();
	 	var url = name.replace(/[\W\s]+/gi,"-").toLowerCase();
	 	$('#create-url').val($('#create-url').attr('parent_url') + url);
	});

	// create add/remove menu
	$(document).on('click', '.plus-minus-icon', function(e) {
		e.preventDefault();
		if($('.my-library-menu').length === 0) {
			var check = $(this).closest('form').find('.add-button-info');
			console.log(check);
			var component_title = $(this).closest('form').find('.add-button-info').attr('resource_requester_title');
			$(this).siblings('.menu-container').append(
				$('<div class="my-library-menu shadow">')
					.append(
						$('<button class="button__primary">')
							.text('Add to: ' + component_title)
					)
				)
			// if the menu is already a child of this element, toggle hide
		} else if ($(this).siblings('.menu-container').children().length > 0) {
				$(this).blur();
				$('.my-library-menu').toggleClass('hide');
		} else {
			$('.my-library-menu').removeClass('hide');
			$(this).siblings('.menu-container').append($('.my-library-menu'));
		}
	});


		// Resources & Comments- expand/collapse
		$('.resources_comments_accordion_btn').on('click', function() {
			var linkLocation = $(this).attr('link_location');
			window.location = linkLocation;
		});




					//add resource from resource library
	//first, call method to add resource_caller to page, then redirect to the resource library
	$('.add-requester').click(function()  {

		var postdata = [];
		postdata.push(
				{name: 'dossier', value: $(this).attr('dossier')}
		);
                alert(postdata);
		$.post($(this).attr('action'), postdata, function(data) {
				if (data.response === "success") {
						window.location.href = data.url;
				}
		}, "json");

	});




});
