function onformerror(formsubmitted,data) {

	if (data.form == "create") {
		$(formsubmitted).prepend('<div class="form-message form-error">' + data.message + '</div>');
	}

	if (data.form == "masquerade") {
		$(formsubmitted).prepend('<div class="form-message form-error">' + data.message + '</div>');
	}

	if (data.form == "delete") {
		$(formsubmitted).parent().prepend('<div class="form-message form-error">' + data.message + '</div>');
	}

	if (data.form == "user_selection") {
			$(formsubmitted).parent().prepend('<div class="form-message form-error">' + data.message + '</div>');
	}
	if (data.form == "view_notes") {
		$(formsubmitted).prepend('<div class="form-message form-error">' + data.message + '</div>');
	}

	if (data.form == "auto_add") {
		
// console.log($(formsubmitted));
		// var clicked_button = $(formsubmitted).closest('.registering-new-org-button');
		// $(formsubmitted).closest('.registering-new-org-button').append('<div class="form-message form-error">' + data.message + '</div>');
		$(formsubmitted).parent().append('<div class="form-message form-error">' + data.message + '</div>');
		setTimeout(function(){
			$('.form-error').hide();
		 }, 3000);

	}

}

function onformsuccess(formsubmitted,data) {

	console.log(formsubmitted);

	if (data.form === "edit") {
		window.location.reload(true);
	}

	if (data.form == "create") {

		$(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
	
		setTimeout(function(){
	   	window.location.reload(true);
		}, 2000);

	}

	if (data.form == "auto_add") {

		$(formsubmitted).parent().append('<div class="form-message form-success">' + data.message + '</div>');
		setTimeout(function(){
			$('.form-success').hide();
		 }, 3000);
	
		setTimeout(function(){
	   		window.location.reload(true);
		}, 2000);

	}

	if (data.form == "masquerade") {

		window.location.href = data.action;	   	

	}

	if (data.form == "view_notes") {
// console.log(data);
		$('#notes-modal').removeClass('hide');  
		$('#notes-title').html('Notes for ' + data.email); 
		$('#notes-textarea').val(data.user_notes); 
		$('#this-user-id').val(data.this_user_id);

		

	}

	if (data.form == "save_notes") {
		// console.log(data);

				$(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
				setTimeout(function(){
					$('.form-success').hide();
					$('#notes-modal').addClass('hide');
				 }, 3000);
				
		
	}

	if (data.form == "delete") {

		window.location.reload(true);

	}

	if (data.form == "user_selection") {
		$(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
		setTimeout(function(){
			window.location.reload(true);
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

	$('#select-all-visible-users').prop("checked", false);
	$('#select-all-visible-users').change(function(e) {
		if ($(this).prop("checked")) {
			$('.batch-process').prop("checked", true);
			$('#process-selected-users').show("slow");
		} else {
			$('.batch-process').prop("checked", false);
			$('#process-selected-users').hide("slow");
		}
	});

	$('.batch-process').change(function(e) {
		if ($(this).prop("checked")) {
			$('#process-selected-users').show("slow");
		}
	});

	$('#process-selected-users').on('click', function(e) {
		e.preventDefault();
		// alert('clicked');
		// console.log($(this).attr('action'));
		$( ".process-user" ).each(function(e) {
			var this_form_id = $(this).attr('id');
			console.log(": " + $( this ).attr('id') );
			var process_this = $('#' + this_form_id + '-process-user').is(":checked");
		if (process_this == true) {
			// console.log(JSON.stringify($('#' + this_form_id + '-dossier').val()));

			postdata = [];
			postdata.push({name: 'dossier', value: JSON.stringify($('#' + this_form_id + '-dossier').val())});
			postdata.push({name: 'role_id', value: $('#' + this_form_id + '-role_id').val()});
			postdata.push({name: 'first_name', value: $('#' + this_form_id + '-first-name').val()});
			postdata.push({name: 'last_name', value: $('#' + this_form_id + '-last-name').val()});
			postdata.push({name: 'organization', value: $('.' + this_form_id + '_org').val()});
			postdata.push({name: 'group', value: $('.' + this_form_id + '_group').val()});
			$.post($(this).attr('action'), postdata, function(data) {
				// console.log(data);
				
			}, 'json');
		}
		  });
		
		  setTimeout( function() {
			window.location.reload(1);
			}, 1000);
	});






	// $('#registering-new-org').on('click', function(e) {
	// 	e.preventDefault();
	// 	alert('clicked');


	// });


	
	
	$('.filter-form-submit').on('click', function(e) {

		var dossier = $(this).attr('dossier');
		var action = $(this).attr('action');
		var pagination = $(this).attr('pagination');
		
		var postdata = [];
		postdata.push(
			{name: 'dossier', value: dossier},
			{name: 'inputtypes', value: $(this).attr('inputtypes')},
			{name: 'pagination_current', value: pagination}
		);
		
		$('.filter-form').each(function(key, value) {
			var selectedName = 'filter_by_' + $(value).attr('name');
			var selectedValue = $(value).val();
				if (selectedValue !== "") {
				postdata.push(
					{name: selectedName, value: selectedValue}
				);
			}
		});

		$.post(action, postdata, function(data) {
			// console.log(data);
			if (data.response === 'success') {
				window.location.reload(true);
			}
		}, 'json');
	});

	// Turn table rows to cards on small screens
	$('.table-style').tabletocard({
		ignore: [4, 5],
		responsive: 905,
		columnsForTitle: [4, 5],
		cardHeight: 130
	});


	$('.cancel-notes, .close-notes-modal').click(function() {
		$('#notes-modal').addClass('hide');
	});

		// When the user clicks anywhere outside of the modal, close it
		var notesModal = document.getElementById('notes-modal');

		$(window).click(function(event) {
			if (event.target === notesModal) {
				$('#notes-modal').addClass('hide');
			}
		});

});
