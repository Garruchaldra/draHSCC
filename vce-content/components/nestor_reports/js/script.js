
// pertains to any form
function onformerror(formsubmitted,data) {

	if (data.form == "create") {
		$(formsubmitted).prepend('<div class="form-message form-error">' + data.message + '</div>');
	}

	if (data.form == "delete") {
		$(formsubmitted).parent().prepend('<div class="form-message form-error">' + data.message + '</div>');
	}

}




var data_report_status = null;


// pertains to any form
function onformsuccess(formsubmitted,data) {

	// console.log(data);

	// pertains to any form
	if (data.form === "edit") {
		window.location.reload(1);
	}
	// pertains to any editing the summary form
	if (data.form === "edit-summary") {
		// window.location.reload(1);
		$('.summary-date-form').submit();
	}


	// this is the output from analytics_data_report
	if (data.form === "analytics_report") {
		var progressbar = $( "#progressbar-analytics-data" );
		// if there is nothing left to display
		if (data_report_status === 'done') {
			$('#analytics-table-head').empty();
			$('#analytics-table-body').empty();
		
			data_report_status = null;
			// return;
		}

		// 
		if (data.table_section == 'head') {
			// console.log(data.message);
			$('#analytics-table-head').append(data.message);
			// $('#data-table-head').append('<tr><th>hi</th></tr>');
			// console.log('head ' + data.current_page);
			$('input[name=current_page]').val(data.current_page);
			$('input[name=head_has_been_sent]').val(data.head_has_been_sent);
			progress(0, progressbar);
			formsubmitted.submit();
		}
		if (data.table_section == 'body') {
			$('#analytics-table-body').append(data.message);
			// console.log('body ' + data.current_page);
			$('input[name=current_page]').val(data.current_page);
			$('input[name=head_has_been_sent]').val(data.head_has_been_sent);
			var progress_percent = parseInt((data.current_page / data.number_of_pages) * 100);
			progress(progress_percent, progressbar);
			formsubmitted.submit();
		}

		if (data.table_section == 'done') {
			$('input[name=current_page]').val(0);
			$('input[name=head_has_been_sent]').val(0);
			var progress_percent = parseInt(100);
			progress(progress_percent, progressbar);
			setTimeout( remove_progressbar, 3000 );
			data_report_status = 'done';
			console.log('finished loading table');
			// alert('done ' + data.current_page);
		}

	}


		// this is the output from component_data_report
		if (data.form === "report") {
			var progressbar = $( "#progressbar-component-data" );
			// if there is nothing left to display
			if (data_report_status === 'done') {
				$('#data-table-head').empty();
				$('#data-table-body').empty();
			
				data_report_status = null;
				// return;
			}
	
			// 
			if (data.table_section == 'head') {
				// console.log(data.message);
				$('#data-table-head').append(data.message);
				// $('#data-table-head').append('<tr><th>hi</th></tr>');
				// console.log('head ' + data.current_page);
				$('input[name=current_page]').val(data.current_page);
				$('input[name=head_has_been_sent]').val(data.head_has_been_sent);
				progress(0, progressbar);
				formsubmitted.submit();
			}
			if (data.table_section == 'body') {
				$('#data-table-body').append(data.message);
				// console.log('body ' + data.current_page);
				$('input[name=current_page]').val(data.current_page);
				$('input[name=head_has_been_sent]').val(data.head_has_been_sent);
				var progress_percent = parseInt((data.current_page / data.number_of_pages) * 100);
				// console.log(progress_percent);
				// console.log('progress_percent');
				progress(progress_percent, progressbar);
				formsubmitted.submit();
			}
	
			if (data.table_section == 'done') {
				$('input[name=current_page]').val(0);
				$('input[name=head_has_been_sent]').val(0);
				var progress_percent = parseInt(100);
				progress(progress_percent, progressbar);
				
				setTimeout( remove_progressbar, 3000 );
				data_report_status = 'done';
				console.log('finished loading table');
				// alert('done ' + data.current_page);
			}
	
		}

		// pertains to saving presets from any form
		if (data.form === "save-preset") {
			$(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
			// window.location.reload(1500);
		}

		if (data.form === "save-advanced-options") {
			$(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
			window.location.reload(1500);
		}


		// this pertains to recalling a preset
		if (data.form === "preset") {
			console.log('preset');
			console.log(data.message);

			var thisjson = data.message;

			if (data.parent_datalist_name == 'personal_analytics_presets') {
				// iterate through preset
				$.each(JSON.parse(thisjson), function(key, value) {
					// console.log(key + value);
					if (key == 'report_subject') {
						$('.analytics-subject').val(value);
					}
					if (key == 'perspective') {
						$('.analytics-perspective').val(value);
					}

					if (key == 'columns_to_show_list') {
						// console.log('yep');
						$(".analytics-columns-to-show option:selected").prop("selected", false);
						$.each(value, function(key2, value2) {
							var split = value2.split("|");
							$.each(split, function(key3, value3) {
								$(".analytics-columns-to-show option[value='" + value3 + "']").prop("selected", true);
								// console.log(value3);
							});
							var list_of_values = $('.analytics-columns-to-show'). val();
							$('#analytics_columns_to_show_list').val('{"list_of_values":"' + list_of_values.join("|") + '"}');
						});
						// console.log('end');
					}

					if (key == 'analytics_start_date') {
						$('#start-date-analytics-data').val(value);
					}
					if (key == 'analytics_end_date') {
						$('#end-date-analytics-data').val(value);
					}


				});
			}

			if (data.parent_datalist_name == 'personal_reports_presets') {
				// turn on show all keys
				// $('#show-all-keys-checkbox').prop( 'checked', true );
				$('#show-all-keys-checkbox').click();
				// iterate through preset
				$.each(JSON.parse(thisjson), function(key, value) {
					// console.log(key + value);
					if (key == 'report_subject') {
						$('.report-subject').val(value);
					}
					if (key == 'perspective') {
						$('.perspective').val(value);
					}

					if (key == 'columns_to_show_list') {
						// console.log('yep');
						$(".columns-to-show option:selected").prop("selected", false);
						$.each(value, function(key2, value2) {
							var split = value2.split("|");
							$.each(split, function(key3, value3) {
								$(".columns-to-show option[value='" + value3 + "']").prop("selected", true);
								// console.log(value3);
							});
							var list_of_values = $('.columns-to-show'). val();
							$('#columns_to_show_list').val('{"list_of_values":"' + list_of_values.join("|") + '"}');
						});
						// console.log('end');
					}

					if (key == 'component_start_date') {
						$('#start-date-component-data').val(value);
					}
					if (key == 'component_end_date') {
						$('#end-date-component-data').val(value);
					}


				});
			}
			$('#data-download-button').click();
		}


	// summary responses
	if (data.form === "summary") {

		// $('#message').hide().html("You clicked on a checkbox.").fadeIn('slow');
		// console.log(data.message);
		$('#summary-content').hide().html(data.message).fadeIn(2000);

		// if (data.summary_method == 'get_total_number_of_comments') {
		// 	// console.log(data.message);
		// 	formsubmitted.find('.loading-div').html(data.message);
		// }
		// if (data.summary_method == 'get_number_of_commenting_users') {
		// 	// console.log(data.message);
		// 	formsubmitted.find('.loading-div').html(data.message);
		// }
		// if (data.summary_method == 'get_total_number_of_uploaded_videos') {
		// 	// console.log(data.message);
		// 	formsubmitted.find('.loading-div').html(data.message);
		// }
		// if (data.summary_method == 'get_total_number_of_distinct_user_logins') {
		// 	// console.log(data.message);
		// 	formsubmitted.find('.loading-div').html(data.message);
		// }
		// if (data.summary_method == 'get_total_number_of_total_user_logins') {
		// 	// console.log(data.message);
		// 	formsubmitted.find('.loading-div').html(data.message);
		// }
		


	}



	if (data.form == "delete") {

		window.location.reload(1);

	}

}


function get_summaries() {

	var parent_form = $('.summary-start-date').closest('form');
	parent_form.submit();

 }


function progress(progress_percent, progressbar) {
		
	progressbar.progressbar( "value", progress_percent);
	if ( progress_percent < 99 ) {
		progressbar.show();
	}
	
 }


 function remove_progressbar() {
	var progressbar = $( "#progressbar-component-data" );
	progressbar.hide();
	var progressbar = $( "#progressbar-analytics-data" );
	progressbar.hide();

 }



$(document).ready(function() {

// var thisaccordion = $('.accordion-content').accordion();
// thisaccordion.on({"afterexpand": function(e){
// 		var highest = $('.longest-name').outerHeight();
// 		console.log(highest);
// 		$('.preset-button').outerHeight(highest);
// 	}
// });


	

	// $(document).on('submit', '.asynchronous-form', function(e) {
	// 	e.preventDefault();

	// 	var formsubmitted = $( this ).serializeArray();
	// 	console.log( formsubmitted );
	// 	formsubmitted.forEach(function(item, index, array) {
	// 		// $('#columns-to-show-data option:contains(' + item + ')').prop({selected: true});
	// 		if ( item.name == 'columns_to_show') {
	// 			var hidden_input = '<input type="hidden" name="' + item.name + index + '" value="' + item.value + '" >';
	// 			console.log(hidden_input);
	// 		}
	// 	  })
	// 	console.log(formsubmitted);

	// });





		
		// function update_users_$dl() {
		// var selected = $('#group-members-$dl li');
		// var ids = new Array();
		// $.each(selected, function (index, value) {
		// var thisId = $(value).attr('user_id');
		// ids.push(thisId);
		// });
		// //create json object which includes datalist identifier, dl id and selected user ids in one hidden input
		// $('#selected-users-$dl').val('{"dl":"$dl", "dl_id":"$dl_id","dl_name":"$dl_name", "user_ids":"' + ids.join('|') + '"}');
		// };
		
		
		// $('.remove-current-members-$dl').on('click', function() {
		// var parentLi = $(this).parent();
		// $(this).remove();
		// parentLi.removeClass('invited-members');
		// parentLi.remove();
		// parentLi.appendTo($('#default-members-$dl'))
		// update_users_$dl();
		// });
		
		// //create json object from selected users when page has loaded
		// update_users_$dl();
		
	
		
		
		// $('.cancel-button').on('click', function() {
		// window.location.reload(1);
		// });
		
		








		var progressbar = $( "#progressbar-component-data" );
		progressLabel = $( ".progress-label" );

		$( "#progressbar-component-data" ).progressbar({
			value: false,
			change: function() {
			   progressLabel.text( 
				  progressbar.progressbar( "value" ) + "%" );
			},
			complete: function() {
			   progressLabel.text( "Loading Completed!" );
			}
		 });

		 var progressbar = $( "#progressbar-analytics-data" );
		 progressLabel = $( ".progress-analytics-label" );
 
		 $( "#progressbar-analytics-data" ).progressbar({
			 value: false,
			 change: function() {
				progressLabel.text( 
				   progressbar.progressbar( "value" ) + "%" );
			 },
			 complete: function() {
				progressLabel.text( "Loading Completed!" );
			 }
		  });




		// Preset some VALUES
		var summary_start_date_val = new Date() 
		summary_start_date_val.setDate( date.getDate() );
		summary_start_date_val.setFullYear( date.getFullYear() - 1 );	
		var summary_end_date_val = new Date() 
		summary_end_date_val.setDate( date.getDate() );


		$('#start-date-summary').datepicker("setDate", summary_start_date_val );
		$('#end-date-summary').datepicker("setDate", summary_end_date_val );
		// $('.summary-date-form').submit();
		
		var cat_val = 'year created';
		var multi_column_val = ['year created', 'month created', 'day created', 'start_date', 'comments'];
		var start_date_val = summary_start_date_val;
		$('#data-category-component-data option:contains(' + cat_val + ')').prop({selected: true});
		$('#start-date-component-data').datepicker("setDate", start_date_val );
		multi_column_val.forEach(function(item, index, array) {
			$('#columns-to-show-data option:contains(' + item + ')').prop({selected: true});
			// console.log(item, index)
		  })



		var list_of_values = $('.columns-to-show').val();
		if(typeof(list_of_values) != "undefined" && list_of_values !== null) {
			$('#columns_to_show_list').val('{"list_of_values":"' + list_of_values.join("|") + '"}');
			get_summaries();
		}

		// END TEST VALUES
		// alert($('.columns-to-show').val());




		$('.analytics-columns-to-show').on('change', function() {
			var list_of_values = $(this). val();
			$('#analytics_columns_to_show_list').val('{"list_of_values":"' + list_of_values.join("|") + '"}');
		});

		$('.columns-to-show').on('change', function() {
			var list_of_values = $(this). val();
			$('#columns_to_show_list').val('{"list_of_values":"' + list_of_values.join("|") + '"}');
		});

		$('#datapoints').on('change', function() {
			var list_of_values = $(this). val();
			$('#datapoints_list').val('{"list_of_values":"' + list_of_values.join("|") + '"}');
			$(this).closest('form').submit();

		});
		// $('.datapoint_checkbox').on('change', function() {
		// 	var list_of_values = $(this). val();
		// 	$('#datapoints_list').val('{"list_of_values":"' + list_of_values.join("|") + '"}');
		// 	$(this).closest('form').submit();
		// });



		  $('#data-category-component-data').on('change', function() {
			var new_val = $(this). val();
			$('#columns-to-show-data option:contains(' + new_val + ')').prop({selected: true});
			var list_of_values = $('.columns-to-show'). val();
			$('#columns_to_show_list').val('{"list_of_values":"' + list_of_values.join("|") + '"}');
			console.log($('#columns_to_show_list').val());
		  });

		  $('#data-category-analytics-data').on('change', function() {
			var new_val = $(this). val();
			$('#analytics-columns-to-show-data option:contains(' + new_val + ')').prop({selected: true});
			var list_of_values = $('.columns-to-show'). val();
			$('#analytics_columns_to_show_list').val('{"list_of_values":"' + list_of_values.join("|") + '"}');
			console.log($('#analytics-columns_to_show_list').val());
		  });

		// setTimeout( progress, 3000 );

		//summary section
		// $('.refresh-summary').on('change', function() {

		// 	$(this).closest('form').submit();
		// });

		// $('#save-summary-config').on('click touchend', function(e) {

		// 	$(this).closest('form').submit();
		// });

		// $('.pagination-form').on('submit', function(e) {
		// 	e.preventDefault();

		// });


	// $(document).on('submit', '.background-form', function(e) {
	// 	e.preventDefault();

	// 	var formsubmitted = $( this ).serializeArray();
	// 	console.log( formsubmitted );
	// 	// formsubmitted.forEach(function(item, index, array) {
	// 	// 	// $('#columns-to-show-data option:contains(' + item + ')').prop({selected: true});
	// 	// 	if ( item.name == 'columns_to_show') {
	// 	// 		var hidden_input = '<input type="hidden" name="' + item.name + index + '" value="' + item.value + '" >';
	// 	// 		console.log(hidden_input);
	// 	// 	}
	// 	//   })
	// 	// console.log(formsubmitted);

	// });


		// this allows a remote button to trigger submit the data form which is in the Config accordion
		$('.trigger-data-download-button').on('click touchend', function(e) {
			$('.data-download-button').click();
		});

		// $('.preset-button').on('click touchend', function(e) {
		// 	$('.data-download-button').click();
		// });

		$('#last-report-button').on('click touchend', function(e) {
			// $('.data-download-button').click();
		});


		$('#download-summary-button').on('click', function() {
			selectElementContents(document.getElementById("summary-table"));
			$('#summary-message').fadeIn('slow', function(){
				$('#summary-message').delay(7000).fadeOut(); 
			 });
		});
		$(document).on('click', '.print-summary-button', function(e) {
		// $('.print-summary-button').on('click', function() {
			console.log('target');
			var target = $(this).attr('target');
			console.log(target);
			createPDF(target);
		});

		$('#download-report-button').on('click', function() {
			selectElementContents(document.getElementById("data-table"));
			$('#data-message').fadeIn('slow', function(){
				$('#data-message').delay(7000).fadeOut(); 
			 });
		});
	
	
		function selectElementContents(el) {
			var body = document.body, range, sel;
			if (document.createRange && window.getSelection) {
				range = document.createRange();
				sel = window.getSelection();
				sel.removeAllRanges();
				try {
					range.selectNodeContents(el);
					sel.addRange(range);
				} catch (e) {
					range.selectNode(el);
					sel.addRange(range);
				}
				document.execCommand("copy");
			} else if (body.createTextRange) {
				range = body.createTextRange();
				range.moveToElementText(el);
				range.select();
				range.execCommand("Copy");
			}
		}



	
	// download button doesn't appear until page has loaded
	if((typeof download_submit_button !== 'undefined')) {
		$('.download-button').replaceWith(download_submit_button);
	}
	// $('#turn-on').on('click touchend', function(e) {
	// 	uploader.binder($(this));
	// });

	// populate loading cells
	// function(e) {
	// 	if ($(this).hasClass('disabled') !== true) {
	// 	  $(this).attr("aria-expanded", ($(this).attr("aria-expanded") != "true"));
	// 	  if ($(this).closest('.accordion-container').hasClass('accordion-open')) {
	// 		// $(this).closest('.accordion-container').addClass('accordion-closed');
	// 		$(this).closest('.accordion-container').find('.accordion-content').first().slideUp('slow', function() {
	// 		  $(this).closest('.accordion-container').removeClass('accordion-open').addClass('accordion-closed');
	// 		});
	// 	  } else {
	// 		$(this).closest('.accordion-container').addClass('accordion-open');
	// 		$(this).closest('.accordion-container').find('.accordion-content').first().slideDown('slow', function() {
	// 		  $(this).closest('.accordion-container').removeClass('accordion-closed');
	// 		});
	// 	  }
	// 	}
	// 	e.preventDefault();
	//   }

	if ($('.datepicker').length) {
		$('.datepicker').datepicker();
	}

	$('#save-as-reports-preset').on('click touchend', function(e) {
		// console.log('save-as-preset');
		$('.report-subject-save').val($('.report-subject').val());
		$('.perspective-save').val($('.perspective').val());
		$('.columns_to_show_list-save').val($('#columns_to_show_list').val());
		$('.start-date-component-data-save').val($('#start-date-component-data').val());
		$('.end-date-component-data-save').val($('#end-date-component-data').val());
	});

	$('#save-as-analytics-preset').on('click touchend', function(e) {
		console.log('save-as-analytics-preset');
		$('.analytics-subject-save').val($('.analytics-subject').val());
		$('.analytics-perspective-save').val($('.analytics-perspective').val());
		$('.analytics-columns_to_show_list-save').val($('#analytics_columns_to_show_list').val());
		$('.start-date-analytics-data-save').val($('#start-date-analytics-data').val());
		$('.end-date-analytics-data-save').val($('#end-date-analytics-data').val());
	});
	
	$('#data-download-button').on('click touchend', function(e) {
		// alert();
		$('#report-results-accordion-container').show();
		$('#report-results-accordion-container').closest('.accordion-container').click();
	});

	$('#analytics-data-download-button').on('click touchend', function(e) {
		// alert();
		$('#analytics-report-results-accordion-container').show();
		$('#analytics-report-results-accordion-container').closest('.accordion-container').click();
	});
	// edit/add toggle
	$('.accordion-container').on('click touchend', function(e) {
		if ($(this).hasClass('disabled') !== true) {
		  $(this).attr("aria-expanded", ($(this).attr("aria-expanded") != "true"));
		  if ($(this).closest('.accordion-container').hasClass('accordion-open')) {
			// if open, do this while closing
			// alert('closing');
			// $(this).closest('.accordion-container').addClass('accordion-closed');
			// $(this).closest('.accordion-container').find('.accordion-content').first().slideUp('slow', function() {
			//   $(this).closest('.accordion-container').removeClass('accordion-open').addClass('accordion-closed');
			// });
		  } else {
			// if closed, do this while opening:
			if ($(this).hasClass('coaching-companion-summary')) {
				// alert(loading_gif);
				$('.loading-div').html(loading_gif);

				
			}
			// $(this).closest('.accordion-container').addClass('accordion-open');
			// $(this).closest('.accordion-container').find('.accordion-content').first().slideDown('slow', function() {
			//   $(this).closest('.accordion-container').removeClass('accordion-closed');
			// });
		  }
		}
		// e.preventDefault();
		
	});
	
	
	
		// edit/add toggle
	// $('.click-tab').on('click touchend', function(e) {
	// 	e.preventDefault();
	// 	$('.tab').removeClass('active-tab');
	// 	$(this).addClass('active-tab');
	// 	$('.tab-content').removeClass('active-tab-content');
	// 	var current_tab = '#' + $(this).attr('tab');
	// 	$(current_tab).addClass('active-tab-content');
	// });
	

	
	// $('.hide-button').on('click touchend', function(e) {
	// 	e.preventDefault();
	// });

        
        
        

	// $('#search-input').keyup(function(e) {
	// 	e.preventDefault();
	// 	input = $('#search-input');
	// 	    filter = input.value.toUpperCase();
    // ul = document.getElementById("myUL");
    // li = ul.getElementsByTagName('li');

    // // Loop through all list items, and hide those who don't match the search query
    // for (i = 0; i < li.length; i++) {
    //     a = li[i].getElementsByTagName("a")[0];
    //     if (a.innerHTML.toUpperCase().indexOf(filter) > -1) {
    //         li[i].style.display = "";
    //     } else {
    //         li[i].style.display = "none";
    //     }
	// }

		
	// });
	







	$("#users").tablesorter({
		headers: { 
            0: { sorter: false }, 1: { sorter: false }, 2: { sorter: false }
        } 
	}); 


	// $('#generate-password').on('click', function() {
		
	// 	var length = 8,
    //     charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
    //     retVal = "";
    // 	for (var i = 0, n = charset.length; i < length; ++i) {
	// 		retVal += charset.charAt(Math.floor(Math.random() * n));
	// 	}
		
	// 	$('input[name=password]').val(retVal);
	
	// });
	
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
				// window.location.reload(1);
			}
		}, 'json');
	});


	// Reactions to config checkboxes
	var show_all_keys_toggle = false;
	$('#show-all-keys-checkbox').on('click touchend', function(e) {
		// e.preventDefault();

		if (show_all_keys_toggle) {
			show_all_keys_toggle = false;
			$(".key-toggle option").remove();
			var hidden_primary_columns = $('#primary-columns-to-show-data').html();
			$('.key-toggle').append(hidden_primary_columns);
			// $('#columns-to-show-data').remove('<option value="media_type">this option</option>');
		} else {
			show_all_keys_toggle = true;
			var hidden_extra_columns = $('#extra-columns-to-show-data').html();
			$('.key-toggle').append(hidden_extra_columns);
		}
	});
	

	
	// $('.hide-button').on('click touchend', function(e) {
	// 	e.preventDefault();
	// });

	$('.preset-delete').on('click touchend', function(e) {
		e.stopPropagation();
		var preset_name = $(this).attr('preset-name');
		// var this_button = $(this);
		if (confirm("Are you sure you want to delete " + preset_name + "?")) {
			var postdata = [];
			postdata.push(
				{name: 'dossier', value: $(this).attr('dossier')}
			);
			$.post($(this).attr('action'), postdata, function(data) {
				if (data.response === 'success') {
					// this_button.prepend('<div class="form-message form-success">' + data.message + '</div>');
					// setTimeout (3000);
					window.location.reload(1);
				}
			}, 'json');
			
		} 
		
	});


	function createPDF(target) {
        var sTable = document.getElementById(target).innerHTML;

        var style = "<style>";
        style = style + "table {width: 100%;font: 17px Calibri;}";
        style = style + "table, th, td {border: solid 1px #DDD; border-collapse: collapse;";
        style = style + "padding: 2px 3px;text-align: center;}";
        style = style + "</style>";

        // CREATE A WINDOW OBJECT.
        var win = window.open('', '', 'height=700,width=700');

        win.document.write('<html><head>');
        win.document.write('<title>Profile</title>');   // <title> FOR PDF HEADER.
        win.document.write(style);          // ADD STYLE INSIDE THE HEAD TAG.
        win.document.write('</head>');
        win.document.write('<body>');
        win.document.write(sTable);         // THE TABLE CONTENTS INSIDE THE BODY TAG.
        win.document.write('</body></html>');

        win.document.close(); 	// CLOSE THE CURRENT WINDOW.

        win.print();    // PRINT THE CONTENTS.
    }



});