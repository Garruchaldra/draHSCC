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

}

function onformsuccess(formsubmitted,data) {

	console.log(formsubmitted);

	if (data.form === "edit") {
		window.location.reload(1);
	}

	if (data.form == "create") {

		$(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
	
		setTimeout(function(){
	   	window.location.reload(1);
		}, 1000);

	}

	if (data.form == "masquerade") {

		window.location.href = data.action;	   	

	}

	if (data.form == "delete") {

		window.location.reload(1);

	}

}

$(document).ready(function() {

	if ($('.datepicker').length) {
		$('.datepicker').datepicker();
	}
	
	
	
		// edit/add toggle
	$('.click-tab').on('click touchend', function(e) {
		e.preventDefault();
		$('.tab').removeClass('active-tab');
		$(this).addClass('active-tab');
		$('.tab-content').removeClass('active-tab-content');
		var current_tab = '#' + $(this).attr('tab');
		$(current_tab).addClass('active-tab-content');
	});
	

	
	$('.hide-button').on('click touchend', function(e) {
		e.preventDefault();
	});

        
        
        

	$('#search-input').keyup(function(e) {
		e.preventDefault();
		input = $('#search-input');
		    filter = input.value.toUpperCase();
    ul = document.getElementById("myUL");
    li = ul.getElementsByTagName('li');

    // Loop through all list items, and hide those who don't match the search query
    for (i = 0; i < li.length; i++) {
        a = li[i].getElementsByTagName("a")[0];
        if (a.innerHTML.toUpperCase().indexOf(filter) > -1) {
            li[i].style.display = "";
        } else {
            li[i].style.display = "none";
        }
	}
		
	});
	







	$("#users").tablesorter({
		headers: { 
            0: { sorter: false }, 1: { sorter: false }, 2: { sorter: false }
        } 
	}); 


	$('#generate-password').on('click', function() {
		
		var length = 8,
        charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
        retVal = "";
    	for (var i = 0, n = charset.length; i < length; ++i) {
			retVal += charset.charAt(Math.floor(Math.random() * n));
		}
		
		$('input[name=password]').val(retVal);
	
	});
	
	$('.filter-form-submit').on('click', function(e) {

		var dossier = $(this).attr('dossier');
		var action = $(this).attr('action');
		var pagination = $(this).attr('pagination');
		
		var postdata = [];
		postdata.push(
			{name: 'dossier', value: dossier},
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
				window.location.reload(1);
			}
		}, 'json');
	});
	
	
	$('.pagination-form').on('submit', function(e) {
		e.preventDefault();
		var postdata = $(this).serialize();
		$.post($(this).attr('action'), postdata, function(data) {
			console.log(data);
			if (data.response === 'success') {
				window.location.reload(1);
			}
		}, 'json');
	});
	
});