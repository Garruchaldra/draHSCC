$(document).ready(function() {



	$('.pagination-button, .sort-icon').on('click', function(e) {
		e.preventDefault();
		postdata = [];
		postdata.push({name: 'dossier', value: JSON.stringify($(this).attr('dossier'))});
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
			postdata.push({name: 'pagination_current', value: pagination});
			postdata.push({name: 'sort_by', value: $(this).attr('sort')});
			postdata.push({name: 'sort_direction', value: $(this).attr('direction')});
			$.post($(this).attr('action'), postdata, function(data) {
				// console.log(data);
				window.location.reload(true);	
			}, 'json');
		}
	});


	$('.pbccycle-datepicker').datepicker();

	$('.users_select').select2();
	
	$('.users_select').on('change', function() {
		var users = $(this).select2("data");
		var ids = new Array();
		$.each(users, function (index, value) {
			$.each(value, function (index, value) {
				if (index == "id") {
					ids.push(value);
				}
			});	
		});
		$(this).parent().find('.user_ids').val(ids.join('|'));
	});
	
	$(".add-sel-item").click(function(b) {
        b.preventDefault();
		var available = $('#users-available option:selected')
		var selected = $('#users-selected');
		$(available).remove().appendTo(selected);
		selectedUsers();
	});
	
	$(".remove-sel-item").click(function(b) {
        b.preventDefault();
		var available = $('#users-selected option:selected')
		var selected = $('#users-available');
		$(available).remove().appendTo(selected);
		selectedUsers();
	});

	
	var selectedUsers = function() {
		var selected = $('#users-selected option');
		var ids = new Array();
		$.each(selected, function (index, value) {
			var thisId = $(value).val();
			ids.push(thisId);
		});
		$('#selected-users').val(ids.join('|'));
	};


	$('.clickbar-top-close').click(function(e) {
		$(this).closest('.clickbar-container').find('.clickbar-title').click();
	});

		// tool tips
	$('.input-tooltip-icon').mouseover(function() {
					$(this).children('.general-tooltip').show();
			}).mouseout(function() {
					$(this).children('.general-tooltip').hide();
	});


});