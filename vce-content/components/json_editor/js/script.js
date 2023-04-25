function onformsuccess(formsubmitted,data) {

		if (data.form === "save-json-obj") {
			$(formsubmitted).prepend('<div id="save-message" class="form-message form-success">' + data.message + '</div>');
			$('.form-success').fadeOut(4000);
			// window.location.reload(1500);
		}

		if (data.form === "select-json-obj") {
			$(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
			$('.form-success').fadeOut(2000);
			window.location.reload(2000);
		}

		if (data.form === "select-backup-obj") {
			$(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
			$('#selected_json_obj_placeholder').text(data.selected_backup_obj);
			$('.form-success').fadeOut(2000);
			$('#update-current').click();
			// editor.set(data.selected_backup_obj);

		}
	}


$(document).ready(function() {

	// const container = document.getElementById('jsoneditor')
	
	// const options = {
	//   mode: 'tree',
	//   modes: ['code', 'form', 'text', 'tree', 'view', 'preview'], // allowed modes
	//   onModeChange: function (newMode, oldMode) {
	// 	console.log('Mode switched from', oldMode, 'to', newMode)
	//   }
	// }
  
  //   const json = {
  // 	"array": [1, 2, 3],
  // 	"boolean": true,
  // 	"null": null,
  // 	"number": 123,
  // 	"object": {"a": "b", "c": "d"},
  // 	"string": "Hello World"
  //   }
// console.log($('#json-obj-pre-edit').val());
// 	const json = $('#json-obj-pre-edit').val();
  
	// const editor = new JSONEditor(container, options, json)


	// $('.pagination-input').on('change', function(e) {
	// 	edited_json = document.getElementById("json-obj-post-edit").setAttribute("value", edited_json);
	// 	document.getElementById("json-obj-post-edit").setAttribute("value", edited_json);
	// });

	// $('#save-current').on('click', function(e) {
	// 	testjson = {
  	// "array": [1, 2, 3],
  	// "boolean": true,
  	// "null": null,
  	// "number": 123,
  	// "object": {"a": "b", "c": "d"},
  	// "string": "testjson World"
    // };
	// 	editor.set(json);
	// 	testdata = 'asdf';
	// 	alert(testdata);
	// });

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
	
	$('.clickbar-top-close').click(function(e) {
		$(this).closest('.clickbar-container').find('.clickbar-title').click();
	});
	
// 	$('.all-clickbar-top-close').click(function(e) {
// 	
// 		$('.clickbar-title').not('.disabled').each(function(key, value) {
// 		
// 			var test = $(value).closest('.clickbar-container').first('.clickbar-content');
// 			
// 			console.log(test);
// 			
// 			var foobar = $(test).find('.clickbar-container');
// 			
// 			console.log(foobar);
// 			
// 			if (foobar.length) {
// 		 	
// 				$(value).not('.disabled').addClass('clickbar-closed');
// 				$(value).closest('.clickbar-container').find('.clickbar-content').slideUp();
// 			}
// 		
// 		
// 		});
// 	});
	
	
	var selectedUsers = function() {
		var selected = $('#users-selected option');
		var ids = new Array();
		$.each(selected, function (index, value) {
			var thisId = $(value).val();
			ids.push(thisId);
		});
		$('#selected-users').val(ids.join('|'));
	};
	

		// tool tips
	$('.input-tooltip-icon').mouseover(function() {
					$(this).children('.general-tooltip').show();
			}).mouseout(function() {
					$(this).children('.general-tooltip').hide();
	});


});