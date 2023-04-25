

/**
 * onformerror displays the error message above the form submitted. 
 * This accounts for create and delete errors
 */
function onformerror(formsubmitted,data) {

	if (data.form == "create") {
		$(formsubmitted).prepend('<div class="form-message form-error">' + data.message + '</div>');
	}

	if (data.form == "delete") {
		$(formsubmitted).parent().prepend('<div class="form-message form-error">' + data.message + '</div>');
	}

} //function onformerror




var data_report_status = null;

/**
 * onformsuccess has a list of many conditions related to the form name
 * In this component, there many forms and handlers
 * 
 */
function onformsuccess(formsubmitted,data) {


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

	} // if (data.form === "analytics_report") {


		// this is the output from component_data_report
		if (data.form === "report") {
			var progressbar = $( "#progressbar-component-data" );
			// if there is nothing left to display
			if (data_report_status === 'done') {
				$('#data-table-head').empty();
				$('#data-table-body').empty();
				$('#total-table-head').empty();
				$('#total-table-body').empty();
			
				data_report_status = null;
				// return;
			}
	
			// 
			if (data.table_section == 'head') {
				// console.log('allemann');
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
				$('#total-table-body').append(data.total_in_timespan);
				$('#the-graph:hidden').empty();
				$('#the-graph:hidden').append(data.the_graph);
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
	
		} // if (data.form === "report") {








	if (data.form == "delete") {

		window.location.reload(1);

	}

}  //function onformsuccess







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


var this_result = 'outside all';
 
/**
 * all the functions for actions with the DOM
 */
$(document).ready(function() {




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


		$('#start-date-main').datepicker("setDate", summary_start_date_val );
		$('#end-date-main').datepicker("setDate", summary_end_date_val );
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

		$('#view-graph-button').on('click', function() {
			// selectElementContents(document.getElementById("graph-modal"));
			$('#graph-modal').fadeIn('slow', function(){
				// $('#graph-modal').delay(7000).fadeOut(); 
			 });
		});

		$('.close').on('click', function() {
			// selectElementContents(document.getElementById("graph-modal"));
			$('#graph-modal').fadeOut('slow', function(){
				// $('#graph-modal').delay(7000).fadeOut(); 
			 });
		});

		$('.got-it').on('click', function() {
			// selectElementContents(document.getElementById("graph-modal"));
			$('#graph-modal').fadeOut('slow', function(){
				// $('#graph-modal').delay(7000).fadeOut(); 
			 });
		});
	

		$('.report-subject').on('change', function() {
			var list_of_values = $(this). val();
			$('#subjects_to_show_list').val('{"list_of_values":"' + list_of_values.join("^") + '"}');
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
	
	//   }

	if ($('.datepicker').length) {
		$('.datepicker').datepicker();
	}

	
	
	$('#data-download-button').on('click touchend', function(e) {
		// alert();
		$('#report-results-accordion-container').show();
		$('#summary-table').hide();
		$('#report-results-accordion-container').closest('.accordion-container').click();
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

		  }
		}
		
	});
	
	
	

        
        
        





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



});  // $(document).ready(function() {