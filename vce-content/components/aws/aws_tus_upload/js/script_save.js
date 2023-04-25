$(document).ready(function() {

	var uploader = uploader || { };
	var active = false;

	uploader.binder = function(eachUploader) {
	
		thisUploader = $(eachUploader);

		var uploadform = thisUploader.find('.fileupload');
		
		var typelist = [];
		thisUploader.find('.media-types').each(function() {
			var eachmime = {};
			eachmime.mimetype = $(this).attr('mimetype');
			eachmime.mimename = $(this).attr('mimename');
			typelist.push(eachmime);
		});
		bar = JSON.stringify(typelist);
		thisUploader.find('.mediatypes').val(JSON.stringify(typelist));

		var accept = [];
		thisUploader.find('.file-extensions').each(function() {
			var mime = $(this).attr('extensions');
			accept.push(mime);
		});
		uploadform.attr('accept',accept.join(','));
		
		var inputs = {};

		uploadform.on('change', function(e) {
		
			dossier = thisUploader.find('.dossier').val();
			mediatypes = thisUploader.find('.mediatypes').val();
			
			var uploadpoint = $(this).attr('path');
			var eachChunkSize = parseInt($(this).attr('chunk_size'));
			var restartTimeout = parseInt($(this).attr('restart_time'));
	
			// Get the selected file from the input element
			var file = e.target.files[0];
			
			var counter = 0;
			var chunkCounter = 0;
			var checker = {};

			// Create a new tus upload
			var upload = new tus.Upload(file, {
			
				/*
				endpoint: null,
				uploadUrl: null,
				metadata: {},
				fingerprint: null,
				uploadSize: null,
				onProgress: null,
				onChunkComplete: null,
				onSuccess: null,
				onError: null,
				_onUploadUrlAvailable: null,
				overridePatchMethod: false,
				headers: {},
				addRequestId: false,
				onBeforeRequest: null,
				onAfterResponse: null,
				onShouldRetry: null,
				chunkSize: Infinity,
				retryDelays: [0, 1000, 3000, 5000],
				parallelUploads: 1,
				storeFingerprintForResuming: true,
				removeFingerprintOnSuccess: false,ß
				uploadLengthDeferred: false,
				uploadDataDuringCreation: false,
				urlStorage: null,
				fileReader: null,
				httpStack: null
				*/
			
				// Endpoint is the upload creation URL from your tus server
				endpoint: uploadpoint,
				chunkSize: eachChunkSize,
				retryDelays: [0, 1000, 3000, 5000],
				// Retry delays will enable tus-js-client to automatically retry on errors
				// Attach additional meta data about the file for the server
				metadata: {
					filename: file.name,
					filetype: file.type,
					dossier: dossier,
					mediatypes: mediatypes
				},
				// Callback for errors which cannot be fixed using retries
				onError: function(error) {
					console.log("Failed because: " + error)
				},
				// Callback for reporting upload progress
				onProgress: function(bytesUploaded, bytesTotal) {
					var percentage = parseInt((bytesUploaded / bytesTotal * 100));
					// .toFixed(2)
					$('.uploader-container .percentage').html(percentage + '%<br>');
					//console.log(bytesUploaded, bytesTotal, percentage + "%")
					$(eachUploader).find('.progressbar').progressbar("value",percentage);
					$(eachUploader).find('.progress-label').text(percentage + "%");
				},
				onChunkComplete: function(chunkSize, bytesAccepted, bytesTotal) {
					// console.log('onChunkComplete ' + bytesTotal);
					// console.log(upload);
					chunkCounter++;
					$(eachUploader).find('.progress-chunks').html('<sup>' + chunkCounter + '</sup>/<sub>' + Math.ceil(file.size / upload.options.chunkSize) + ' parts</sub>').show();

				},
				onShouldRetry: function(err, retryAttempt, options) {
					return true;
				},
				onBeforeRequest: function(req) {
					counter++;
					//var current = counter;
					//request.push(current);
					//console.log('sent' + counter);
					//console.log(request.pop());
					name = 'check_' + counter;
					checker.name = setTimeout(
					function () {
						console.log('counter is ' + counter);
						console.log('error happened?');
						upload.start();
					}
					, restartTimeout);
					console.log('sent ' + counter);
					},
				onAfterResponse: function(req, res) {
					//response.push(counter)
					//var current = counter;
					name = 'check_' + counter;
					clearTimeout(checker.name);
					console.log('received ' + counter);
				},
				// Callback for once the upload is completed
				onSuccess: function() {
				
					console.log("Download %s from %s", upload.file.name, upload.url)
					
					postdata = inputs;
					postdata['filename'] = file.name;
					postdata['filetype'] = file.type;
					$.post(upload.url, postdata, function(data) {
						if (data.status === 'success') {
							console.log('status updated');
							thisUploader.find('.progressbar-container').hide()
							thisUploader.find('.progressbar-message').addClass('success').text(data.message).show();

							postdata.media_type = data.media_type;
							postdata.path = data.path;
							
							action = postdata.action;
							
							delete postdata.filetype;
							delete postdata.tilename;
							delete postdata.mediatypes;
							delete postdata.action;
							
							// fire off the call to input
							$.post(action, postdata, function(data) {
								console.log(data);
								if (data.response === "success") {
									window.location.reload(true);
								}
							}, "json");	
						}
					}, "json");		
				}
			})
			

			thisUploader.find('.upload-browse').hide();
			
			fileName = upload.file.name.replace(/\..*$/, '').replace(/[_-]/g, ' ');
			
			thisUploader.find('.resource-name').val(fileName).focus();
			thisUploader.find('.upload-form').show();
			
			thisUploader.find('.start-upload').on('click', function(e) {
			
				submittable = true;
				thisUploader.find('.upload-form input, .upload-form textarea, .upload-form select').each(function() {
					if ($(this).attr('tag') == 'required') {
						if ($(this).val() == '') {
							$(this).closest('label').addClass('highlight-alert');
							$(this).closest('.input-label-style').addClass('highlight-alert');
							submittable = false;
						}
						if ($(this).attr('type') === "checkbox" && !$(this).prop('checked')) {
							$(this).closest('label').addClass('highlight-alert');
							$(this).closest('.input-label-style').addClass('highlight-alert');
							submittable = false;
						}
						if ($(this).find('option:selected').val() == "" && $(this).attr('tag') == 'required') {
							$(this).closest('label').addClass('highlight-alert');
							$(this).closest('.input-label-style').addClass('highlight-alert');
							submittable = false;
						}
					}
					if ($(this).attr('name') && !$(this).hasClass('ignore-input')) {
						if ($(this).is(':checkbox')) {
							if ($(this).is(':checked')) {
								inputs[$(this).attr('name')] = $(this).val();
							}
						} else {
							inputs[$(this).attr('name')] = $(this).val();
						}
					}
				});
			
				if (submittable) {
			
					upload.start();
				
					thisUploader.find('.upload-form').hide();
					thisUploader.find('.progressbar-container').show();
					thisUploader.find('.progressbar').progressbar({
					value: false
					});
				
				}
			
			});
			
			thisUploader.find('.cancel-button').on('click', function(e) {
				e.preventDefault();
				upload.abort(true).then(function () {
					window.location.reload(true);
					// Upload has been aborted and terminated
				});
			});
	
		});
	
	}	
	
	$('.uploader-container').each(function() {
		uploader.binder($(this));
	});

});