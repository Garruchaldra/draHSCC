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
			console.log(data);
			if (data.response === 'success') {
				window.location.reload(true);
			}
		}, 'json');
	});
	

	$("#datalist").tablesorter({
		headers: { 
            0: { sorter: false },7: { sorter: false }
        } 
	}); 

});