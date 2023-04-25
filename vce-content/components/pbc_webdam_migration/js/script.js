
// function onformerror(formsubmitted,data) {
// 	if (data.response === 'success') {
// 		$(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
	
// 	}
// 	$(formsubmitted).prepend('<div class="form-message form-error">' + data.message + '</div>');

// }

// function onformsuccess(formsubmitted,data) {

// 	// console.log(data);
// 	$(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
	
// 	// setTimeout( function() {
// 	// window.location.reload(1);
// 	// }, 1000);
	
// }


    // ref: http://stackoverflow.com/a/1293163/2343
    // This will parse a delimited string into an array of
    // arrays. The default delimiter is the comma, but this
    // can be overriden in the second argument.
    function CSVToArray( strData, strDelimiter ){
        // Check to see if the delimiter is defined. If not,
        // then default to comma.
        strDelimiter = (strDelimiter || ",");

        // Create a regular expression to parse the CSV values.
        var objPattern = new RegExp(
            (
                // Delimiters.
                "(\\" + strDelimiter + "|\\r?\\n|\\r|^)" +

                // Quoted fields.
                "(?:\"([^\"]*(?:\"\"[^\"]*)*)\"|" +

                // Standard fields.
                "([^\"\\" + strDelimiter + "\\r\\n]*))"
            ),
            "gi"
            );


        // Create an array to hold our data. Give the array
        // a default empty first row.
        var arrData = [[]];

        // Create an array to hold our individual pattern
        // matching groups.
        var arrMatches = null;


        // Keep looping over the regular expression matches
        // until we can no longer find a match.
        while (arrMatches = objPattern.exec( strData )){

            // Get the delimiter that was found.
            var strMatchedDelimiter = arrMatches[ 1 ];

            // Check to see if the given delimiter has a length
            // (is not the start of string) and if it matches
            // field delimiter. If id does not, then we know
            // that this delimiter is a row delimiter.
            if (
                strMatchedDelimiter.length &&
                strMatchedDelimiter !== strDelimiter
                ){

                // Since we have reached a new row of data,
                // add an empty row to our data array.
                arrData.push( [] );

            }

            var strMatchedValue;

            // Now that we have our delimiter out of the way,
            // let's check to see which kind of value we
            // captured (quoted or unquoted).
            if (arrMatches[ 2 ]){

                // We found a quoted value. When we capture
                // this value, unescape any double quotes.
                strMatchedValue = arrMatches[ 2 ].replace(
                    new RegExp( "\"\"", "g" ),
                    "\""
                    );

            } else {

                // We found a non-quoted value.
                strMatchedValue = arrMatches[ 3 ];

            }


            // Now that we have our value string, let's add
            // it to the data array.
            arrData[ arrData.length - 1 ].push( strMatchedValue );
        }

        // Return the parsed data.
        return( arrData );
    }


$(document).ready(function() {


// clicking this button touches off a series of ASYNC calls which add webdam videos to the site
// Using a form on the page, one ID number at a time is inserted into the form, along with the list of taxonomy.
// The form is then triggered, and the result triggers the process again. 
// Duplicates are avoided by the save_video method
// The same list can be used repeatedly, there is no danger of overwriting or duplicates
// Taxonomy must be already existant, otherwise the assigned taxonomy is default
// Webdam asset_id's are saved along with the vimeo component. This is used to check existance
// 


	$('#parse-csv-button').click(function(e) {
	
		var textareaContent = $("#parse-csv-textarea").val();


		var parseResult = CSVToArray(textareaContent);
		// console.log(parseResult);

		// for each row in the CSV, submit the info to input which calls pbc_webdam_migration::save_video
		// the class and method called are in the dossier which is on the page.
		// parseResult.forEach(uploadVideo2);

		parseResult.every((value, index) => {  

			// console.log(value[0]);
			// console.log(value[1]);
			// console.log(`${element} === ${array[index]} is equals to ${element === array[index]}`);  
  
			// return true; 
						// get submission form for create VimeoVideo from Webdam_id
						var parent_form = $('.asset-id').closest('form');

						// add dossier, asset_id and taxonomy
			
						var thisDossier = $('#dossier-for-add-video').val();
			
						var postdata = [];			
						postdata.push({name: 'dossier', value: thisDossier});
						postdata.push({name: 'asset_id', value: value[0]});
						postdata.push({name: 'taxonomy', value: value[1]});


						$.ajax({
							type: 'POST',
							url: parent_form.attr('action'),
							data: postdata,
							dataType: 'json',
							async:false
						})
						.done(function(data) {
							if (data.response === 'success') {
								// do nothing, just go on to the next asset_id and repeat process
								$('#parse-csv-result').append('<div class="form-message form-success">' + data.message + ': ' + data.component_id + '</div>');
							} else {
								$('#parse-csv-result').append('<div class="form-message form-error">' + data.message + '</div>');
							}
							// return false;
						})
						.always(function(jqXHROrData, textStatus, jqXHROrErrorThrown){
							// console.log(jqXHROrData);
							// console.log(jqXHROrErrorThrown);
							// $('#parse-csv-result').append('<div class="form-message form-success">' + jqXHROrData + '</div>');
							// $('#parse-csv-result').append('<div class="form-message form-success">' + textStatus + '</div>');
							// $('#parse-csv-result').append('<div class="form-message form-success">' + jqXHROrErrorThrown + '</div>');

						});



			// console.log(index);
			return true; 
		});

	});
	


	$('#parse-csv-delete-button').click(function(e) {
	
		var textareaContent = $("#parse-csv-textarea").val();


		var parseResult = CSVToArray(textareaContent);
		// console.log(parseResult);

		// for each row in the CSV, submit the info to input which calls pbc_webdam_migration::save_video
		// the class and method called are in the dossier which is on the page.
		// parseResult.forEach(uploadVideo2);

		parseResult.every((value, index) => {  

			// console.log(value[0]);
			// console.log(value[1]);
			// console.log(`${element} === ${array[index]} is equals to ${element === array[index]}`);  
  
			// return true; 
						// get submission form for create VimeoVideo from Webdam_id
						var parent_form = $('.asset-id').closest('form');

						// add dossier, asset_id and taxonomy
			
						var thisDossier = $('#dossier-for-delete-video').val();
			
						var postdata = [];			
						postdata.push({name: 'dossier', value: thisDossier});
						postdata.push({name: 'asset_id', value: value[0]});


						$.ajax({
							type: 'POST',
							url: parent_form.attr('action'),
							data: postdata,
							dataType: 'json',
							async:false
						})
						.done(function(data) {
							if (data.response === 'success') {
								console.log(data);
								// do nothing, just go on to the next asset_id and repeat process
								$('#parse-csv-result').append('<div class="form-message form-success">' + data.message + ': ' + value[0] + '</div>');
							} else {
								$('#parse-csv-result').append('<div class="form-message form-error">' + data.message + '</div>');
							}
							// return false;
						})
						.always(function(jqXHROrData, textStatus, jqXHROrErrorThrown){
							// console.log(jqXHROrData);
							// console.log(jqXHROrErrorThrown);
							// $('#parse-csv-result').append('<div class="form-message form-success">' + jqXHROrData + '</div>');
							// $('#parse-csv-result').append('<div class="form-message form-success">' + textStatus + '</div>');
							// $('#parse-csv-result').append('<div class="form-message form-success">' + jqXHROrErrorThrown + '</div>');

						});


			// console.log(index);
			return true; 
		});

	});



	$('#unlink-uploads-button').click(function(e) {
	


			// console.log(value[0]);
			// console.log(value[1]);
			// console.log(`${element} === ${array[index]} is equals to ${element === array[index]}`);  
  
			// return true; 
						// get submission form for create VimeoVideo from Webdam_id
						var parent_form = $('.asset-id').closest('form');

						// add dossier, asset_id and taxonomy
			
						var thisDossier = $('#dossier-for-unlink-uploads').val();
			
						var postdata = [];			
						postdata.push({name: 'dossier', value: thisDossier});


						$.ajax({
							type: 'POST',
							url: parent_form.attr('action'),
							data: postdata,
							dataType: 'json',
							async:false
						})
						.done(function(data) {
							if (data.response === 'success') {
								console.log(data);
								// do nothing, just go on to the next asset_id and repeat process
								$('#parse-csv-result').append('<div class="form-message form-success">' + data.message + ': ' + value[0] + '</div>');
							} else {
								$('#parse-csv-result').append('<div class="form-message form-error">' + data.message + '</div>');
							}
							// return false;
						})
						.always(function(jqXHROrData, textStatus, jqXHROrErrorThrown){
							// console.log(jqXHROrData);
							// console.log(jqXHROrErrorThrown);
							// $('#parse-csv-result').append('<div class="form-message form-success">' + jqXHROrData + '</div>');
							// $('#parse-csv-result').append('<div class="form-message form-success">' + textStatus + '</div>');
							// $('#parse-csv-result').append('<div class="form-message form-success">' + jqXHROrErrorThrown + '</div>');

						});



			return true; 


	});


	$('#repair-description-button').click(function(e) {
	
		var textareaContent = $("#parse-csv-textarea").val();


		var parseResult = CSVToArray(textareaContent);
		// console.log(parseResult);

		// for each row in the CSV, submit the info to input which calls pbc_webdam_migration::save_video
		// the class and method called are in the dossier which is on the page.
		// parseResult.forEach(uploadVideo2);

		parseResult.every((value, index) => {  

			// console.log(value[0]);
			// console.log(value[1]);
			// console.log(`${element} === ${array[index]} is equals to ${element === array[index]}`);  
  
			// return true; 
						// get submission form for create VimeoVideo from Webdam_id
						var parent_form = $('.asset-id').closest('form');

						// add dossier, asset_id and taxonomy
			
						var thisDossier = $('#dossier-for-repair-description').val();
			
						var postdata = [];			
						postdata.push({name: 'dossier', value: thisDossier});
						postdata.push({name: 'asset_id', value: value[0]});
						//postdata.push({name: 'taxonomy', value: value[1]});


						$.ajax({
							type: 'POST',
							url: parent_form.attr('action'),
							data: postdata,
							dataType: 'json',
							async:false
						})
						.done(function(data) {
							if (data.response === 'success') {
								// do nothing, just go on to the next asset_id and repeat process
								$('#parse-csv-result').append('<div class="form-message form-success">' + data.message + ': ' + data.component_id + '</div>');
							} else {
								$('#parse-csv-result').append('<div class="form-message form-error">' + data.message + '</div>');
							}
							// return false;
						})
						.always(function(jqXHROrData, textStatus, jqXHROrErrorThrown){
							// console.log(jqXHROrData);
							// console.log(jqXHROrErrorThrown);
							// $('#parse-csv-result').append('<div class="form-message form-success">' + jqXHROrData + '</div>');
							// $('#parse-csv-result').append('<div class="form-message form-success">' + textStatus + '</div>');
							// $('#parse-csv-result').append('<div class="form-message form-success">' + jqXHROrErrorThrown + '</div>');

						});



			// console.log(index);
			return true; 
		});

	});


	$('#import-external-links-button').click(function(e) {
	
		var textareaContent = $("#parse-csv-textarea").val();

		var parseResult = CSVToArray(textareaContent);
		console.log(parseResult);

		// for each row in the CSV, submit the info to input which calls pbc_webdam_migration::save_video
		// the class and method called are in the dossier which is on the page.
		// parseResult.forEach(uploadVideo2);

		parseResult.every((value, index) => {  

			// console.log(value[0]);
			// console.log(value[1]);
			// console.log(`${element} === ${array[index]} is equals to ${element === array[index]}`);  
  
			// return true; 
						// get submission form for create VimeoVideo from Webdam_id
						var parent_form = $('.asset-id').closest('form');

						// add dossier, asset_id and taxonomy
			
						var thisDossier = $('#dossier-for-import-external-link').val();
			
						var postdata = [];			
						postdata.push({name: 'dossier', value: thisDossier});
						postdata.push({name: 'import_url', value: value[0]});
						postdata.push({name: 'taxonomy', value: value[1]});
						postdata.push({name: 'name', value: value[2]});
						postdata.push({name: 'description', value: value[3]});
						
// console.log(postdata);

						$.ajax({
							type: 'POST',
							url: parent_form.attr('action'),
							data: postdata,
							dataType: 'json',
							async:false
						})
						.done(function(data) {
							if (data.response === 'success') {
								// do nothing, just go on to the next asset_id and repeat process
								// console.log('success');
								$('#parse-csv-result').append('<div class="form-message form-success">' + data.message + ': ' + data.component_id + '</div>');
							} else {
								$('#parse-csv-result').append('<div class="form-message form-error">' + data.message + '</div>');
							}
							// return false;
						})
						.always(function(jqXHROrData, textStatus, jqXHROrErrorThrown){
							// console.log(jqXHROrData);
							// console.log(jqXHROrErrorThrown);
							// $('#parse-csv-result').append('<div class="form-message form-success">' + jqXHROrData + '</div>');
							// $('#parse-csv-result').append('<div class="form-message form-success">' + textStatus + '</div>');
							// $('#parse-csv-result').append('<div class="form-message form-success">' + jqXHROrErrorThrown + '</div>');

						});



			// console.log(index);
			return true; 
		});

	});



});