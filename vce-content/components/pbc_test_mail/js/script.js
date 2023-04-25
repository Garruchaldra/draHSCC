$(document).ready(function() {
	
	if ($('.datepicker').length) {
		$('.datepicker').datepicker();
	}
	
	// edit/add toggle
	$('.click-tab').on('click touchend', function(e) {
		e.preventDefault();
		// $('.tab').removeClass('active-tab');
		// $(this).addClass('active-tab');
		// $('.tab-content').removeClass('active-tab-content');
		// var current_tab = '#' + $(this).attr('tab');
		// $(current_tab).addClass('active-tab-content');
	});
	

	
	$('.hide-button').on('click touchend', function(e) {
		e.preventDefault();
	//	$('li:contains("Scriabin")').addClass('hide-element');
		// $("li:contains('Scriabin')").addClass('hide-element');
	});
	
	
	
			//add resource from resource library
	//first, call method to add resource_caller to page, then redirect to the resource library
        $('.add-requester').click(function()  {

                var postdata = [];
                postdata.push(
                        {name: 'dossier', value: $(this).attr('dossier')}
                );
//                 alert(postdata);
                $.post($(this).attr('action'), postdata, function(data) {
                        if (data.response === "success") {
                                window.location.href = data.url;
                        }
                }, "json");

        });



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
		
		
// 		$('li:contains("Scriabin")').addClass('hide-element');
		// $("li:contains('Scriabin')").addClass('hide-element');
	});
	
	
	    // Declare variables
  //   var input, filter, ul, li, a, i;
//     input = document.getElementById('myInput');
//     filter = input.value.toUpperCase();
//     ul = document.getElementById("myUL");
//     li = ul.getElementsByTagName('li');
// 
//     // Loop through all list items, and hide those who don't match the search query
//     for (i = 0; i < li.length; i++) {
//         a = li[i].getElementsByTagName("a")[0];
//         if (a.innerHTML.toUpperCase().indexOf(filter) > -1) {
//             li[i].style.display = "";
//         } else {
//             li[i].style.display = "none";
//         }
//     }
// 
// 

});