$(document).ready(function() {



	$('.pagination-button-home, .sort-icon').on('click', function(e) {
		e.preventDefault();
		postdata = [];
		postdata.push({name: 'dossier', value: JSON.stringify($(this).attr('dossier'))});
		postdata.push({name: 'inputtypes', value: $(this).attr('inputtypes')});
		postdata.push({name: 'pagination_current', value: $(this).attr('pagination')});
		postdata.push({name: 'sort_by', value: $(this).attr('sort')});
		postdata.push({name: 'sort_direction', value: $(this).attr('direction')});
		postdata.push({name: 'search_value', value: $(this).attr('search_value')});
		postdata.push({name: 'user_search_results', value: $(this).attr('user_search_results')});
		postdata.push({name: 'cycle_search_results', value: $(this).attr('cycle_search_results')});
		// console.log(postdata);
		// return;
		$.post($(this).attr('action'), postdata, function(data) {
			// console.log(data);
			window.location.reload(true);	
		}, 'json');
	});
	
	$('.pagination-input-home').on('change', function(e) {
		e.preventDefault();
		var pagination = $(this).val();
		if (!isNaN(parseFloat(pagination)) && isFinite(pagination)) {
			postdata = [];
			postdata.push({name: 'dossier', value: JSON.stringify($(this).attr('dossier'))});
			postdata.push({name: 'inputtypes', value: $(this).attr('inputtypes')});
			postdata.push({name: 'pagination_current', value: pagination});
			postdata.push({name: 'sort_by', value: $(this).attr('sort')});
			postdata.push({name: 'sort_direction', value: $(this).attr('direction')});
			postdata.push({name: 'search_value', value: $(this).attr('search_value')});
			postdata.push({name: 'user_search_results', value: $(this).attr('user_search_results')});
			postdata.push({name: 'cycle_search_results', value: $(this).attr('cycle_search_results')});
			$.post($(this).attr('action'), postdata, function(data) {
				// console.log(data);
				window.location.reload(true);	
			}, 'json');
		}
	});

	$('.sort-by').on('change', function(e) {
		e.preventDefault();
		// var url = window.location + $(this).attr('query_string');
		// var url = window.location + $(this).val();
		var sort_by = $(this).attr('sort_by');
		var sort_direction = $(this).val();
		// alert(sort_info[1]);
		// window.location.href = "";

			postdata = [];
			postdata.push({name: 'dossier', value: JSON.stringify($(this).attr('dossier'))});
			postdata.push({name: 'inputtypes', value: $(this).attr('inputtypes')});
			postdata.push({name: 'pagination_current', value: $(this).attr('pagination_current')});
			postdata.push({name: 'sort_by', value: sort_by});
			postdata.push({name: 'sort_direction', value: sort_direction});
			postdata.push({name: 'search_value', value: $(this).attr('search_value')});
			postdata.push({name: 'user_search_results', value: $(this).attr('user_search_results')});
			postdata.push({name: 'cycle_search_results', value: $(this).attr('cycle_search_results')});
			$.post($(this).attr('action'), postdata, function(data) {
				// console.log(data);
				window.location.reload(true);	
			}, 'json');
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

});
