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

	// multiple select
	function selectIngredient(select)
	{
	  var $ul = $(select).prev('ul');
   
	  if ($ul.find('input[value=' + $(select).val() + ']').length == 0)
		$ul.append('<li onclick="$(this).remove();">' +
		  '<input type="hidden" name="ingredients[]" value="' + 
		  $(select).val() + '" /> ' +
		  $(select).find('option[selected]').text() + '</li>');
	}
	
	
	
	
// 	$(function() {
// 			var selected = $('#group-members li');
// 			var ids = new Array();
// 			$.each(selected, function (index, value) {
// 				var thisId = $(value).attr('user_id');
// 				ids.push(thisId);
// 			});
// 			$('#selected-users').val(ids.join('|'));

// 	});
	
	
		
		
		
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
				var removeButton = $('<span class="remove-current-members" title="remove">x</span>').click(function() {
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
			$('#selected-users').val('observers~' + ids.join('|'));
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
	
	$(function() {
		$('#default-members2').sortable({
			connectWith: ".connected-sortable",
			cursor: "move"
		}).disableSelection();
		$('#group-members2').sortable({
			receive: function( event, ui ) {
				update_users2();
			},
			update:function(ev, ui) {
				var widget = $(this);
				var removeButton = $('<span class="remove-current-members2" title="remove">x</span>').click(function() {
					var parentLi = $(this).parent();
					$(this).remove();
					parentLi.appendTo($('#default-members2'))
					$('#default-members2 li').sort(asc_sort).appendTo($('#default-members2'));
					update_users2();
				});
				$(ui.item).prepend(removeButton);
			}
		}).disableSelection();

		function asc_sort2(a, b){
			return ($(b).text().toUpperCase()) < ($(a).text().toUpperCase());    
		}

		function update_users2() {
			var selected = $('#group-members2 li');
			var ids = new Array();
			$.each(selected, function (index, value) {
				var thisId = $(value).attr('user_id');
				ids.push(thisId);
			});
			$('#selected-users2').val('observed~' + ids.join('|'));
		}
		
		
		$('.remove-current-members2').on('click', function() {
			var parentLi = $(this).parent();
			$(this).remove();
			parentLi.removeClass('invited-members2');
			parentLi.remove();
			parentLi.appendTo($('#default-members2'))
			update_users2();
		});

	});
	
	
	
	$('.cancel-button').on('click', function() {
		window.location.reload(1);
	});
});