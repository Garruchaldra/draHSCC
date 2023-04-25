$(document).ready(function() {

	$("#default-group").tablesorter({
		headers: { 
			0: { sorter: false }
		} 
	});
	
// 	$('.table-to-card').tabletocard({
// 		ignore			: [0],
// 		responsive 		: 900,
// 		cardWidth 		: 283,
// 		cardHeight		: 300
// 	});

	$('.remove-member, .remove-group').on('submit', function(e) {
		e.preventDefault();
		var formsubmitted = $(this);
		
		if (confirm("Are you sure you want to delete?")) {

			postdata = $(this).serializeArray();
			$.post($(this).attr('action'), postdata, function(data) {
				if (data.response) {
					console.log(data);
					if (data.response === "error") {
						$(formsubmitted).closest('.clickbar-container').prepend('<div class="form-message form-error">' + data.message + '</div>');
					}
					if (data.response === "success") {
						window.location.reload(1);
					}
				}
			}, 'json');
		}

	});


	$('.edit-group').on('submit', function(e) {
		e.preventDefault();
		var formsubmitted = $(this);
		
		postdata = $(this).serializeArray();
		$.post($(this).attr('action'), postdata, function(data) {
			if (data.response) {
				console.log(data);
				if (data.response === "error") {
					$(formsubmitted).closest('.clickbar-container').prepend('<div class="form-message form-error">' + data.message + '</div>');
				}
				if (data.response === "success") {
					window.location.reload(1);
				}
			}
		}, 'json');
		
	});
	
	$(function() {
		$('#default-members').sortable({
			connectWith: ".connected-sortable",
			cursor: "move"
		}).disableSelection();
		$('#group-members').sortable({
			receive: function( event, ui ) {
				update_users();
			},
			update:function(ev, ui) {
				var widget = $(this);
				var removeButton = $('<span class="remove-members" title="remove">x</span>').click(function() {
					var parentLi = $(this).parent();
					$(this).remove();
					parentLi.appendTo($('#default-members'))
					$('#default-members li').sort(asc_sort).appendTo($('#default-members'));
					update_users();
				});
				$(ui.item).prepend(removeButton);
			}
		}).disableSelection();

		function asc_sort(a, b){
			return ($(b).text().toUpperCase()) < ($(a).text().toUpperCase());    
		}

		function update_users() {
			var selected = $('#group-members li');
			var ids = new Array();
			$.each(selected, function (index, value) {
				var thisId = $(value).attr('user_id');
				ids.push(thisId);
			});
			$('#selected-users').val(ids.join('|'));
		}
		
		$('.remove-current-members').on('click', function() {
			var parentLi = $(this).parent();
			$(this).remove();
			parentLi.removeClass('invited-members');
			parentLi.remove();
			parentLi.appendTo($('#default-members'))
			update_users();
		});

	});


	$('.clickbar-trigger').one('mouseover',function() {
		$('#large-arrow').delay(2000).fadeOut('slow');
    });

});