$(document).ready(function() {

	var uploader = uploader || { };

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
		
			thisUploader = $(eachUploader);

			dossier = thisUploader.find('.dossier').val();
			mediatypes = thisUploader.find('.mediatypes').val();
			
			// Get the selected file from the input element
			var file = e.target.files[0];
			
			thisUploader.find('.upload-browse').hide();
			
			fileName = file.name.replace(/\..*$/, '').replace(/[_-]/g, ' ');
			
			media_type = null;
			typelist.forEach(theType => {
				if (file.type) {
					if (file.type.match(theType.mimetype)) {
						media_type = theType.mimename;
					} else if (file.type === theType.mimetype) {
						media_type = theType.mimename;
					}
				} else {
					extention = file.name.split('.').pop();
					if (extention === theType.mimetype) {
						media_type = theType.mimename;
					}
				}
			});
			
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

					if (media_type == null) {
						submittable = false;
						thisUploader.find('.upload-form').hide();
						thisUploader.find('.progressbar-container').hide()
						thisUploader.find('.progressbar-message').addClass('error').text('Incorect Media Type.  Upload Canceled.').show();
					}
				});
			
				if (submittable) {
			
					identity_pool_id = thisUploader.find('.identity_pool_id').val();
					key = thisUploader.find('.key').val();
					secret = thisUploader.find('.secret').val();
					bucket = thisUploader.find('.bucket').val();
					region = thisUploader.find('.region').val();
					version = thisUploader.find('.version').val();
					action = thisUploader.find('.action').val();
					created_by = thisUploader.find('.created_by').val();
					begin_log_activity = thisUploader.find('.begin_log_activity').val();
					log_activity = thisUploader.find('.log_activity').val();

					AWS.config.update({
						region: region,
						credentials: new AWS.CognitoIdentityCredentials({
							IdentityPoolId: identity_pool_id
						  })
					});

           	 		filekey = created_by + '_' + Date.now()  + '.' + file.name.split('.').pop();

					postdata = inputs;
					postdata.filename = file.name;
					postdata.filetype = file.type;
					postdata.media_type = media_type;
					postdata.path = filekey;
						
					$.post(begin_log_activity, postdata, function(data) {}, "json");	

					var upload = new AWS.S3.ManagedUpload({
						params: {
							Bucket: bucket,
							Key: filekey,
							Body: file
						}
					});

					upload.on('httpUploadProgress', (event) => {
						var percentage = parseInt((event.loaded * 100 / event.total));
						$('.uploader-container .percentage').html(percentage + '%<br>');
						$(eachUploader).find('.progressbar').progressbar("value",event.loaded * 100 / event.total);
						$(eachUploader).find('.progress-label').text(percentage + "%");

						postdata.chunk = event.part;
						postdata.total = event.total;
						postdata.loaded = event.loaded;
						postdata.status = 'uploading';
						postdata.path = filekey;

						// disable these posts for now.  It was slowing down too much
						//$.post(log_activity, postdata, function(data) {}, "json");	

					});

					thisUploader.find('.cancel-upload').on('click', function(e) {

						thisUploader.find('.progressbar-container').hide()
						thisUploader.find('.progressbar-message').addClass('error').text('Upload Cancelled.').show();

						e.preventDefault();
						upload.abort();

						postdata['status'] = 'cancelled';
						postdata.path = filekey;
						$.post(log_activity, postdata, function(data) {
							window.location.reload(true);
						}, "json");

					});

					var promise = upload.promise();
			
					promise.then(
						function(data) {
							thisUploader.find('.progressbar-container').hide()
							thisUploader.find('.progressbar-message').addClass('success').text('Upload Succeeded!').show();

							postdata.filename = file.name;
							postdata.filetype = file.type;
							postdata.media_type = media_type;
							postdata.status = 'success';
							postdata.path = filekey;
							
							$.post(log_activity, postdata, function(data) {}, "json");	

							// Pass on to input
							//successpostdata = {};
							//successpostdata.dossier = postdata.dossier;
							//successpostdata.type = postdata.type;
							//successpostdata.media_type = postdata.media_type;
							//successpostdata.title = postdata.title;
							//successpostdata.path = postdata.path;
							//successpostdata.job_id = postdata.job_id;
							//successpostdata.name = postdata.name;
							//successpostdata.created_by = postdata.created_by;
							
							// Pass on to input and cleanup
							successpostdata = postdata;					
							delete successpostdata.action;
							delete successpostdata.begin_log_activity;
							delete successpostdata.bucket;
							delete successpostdata.chunk;
							delete successpostdata.filename;
							delete successpostdata.filetype;
							delete successpostdata.identity_pool_id;
							delete successpostdata.inputtypes;
							delete successpostdata.loaded;
							delete successpostdata.log_activity;
							delete successpostdata.mediatypes;
							delete successpostdata.parent_id;
							delete successpostdata.region;
							delete successpostdata.status;
							delete successpostdata.total;
							delete successpostdata.version;

							// fire off the call to input
							$.post(action, successpostdata, function(data) {
								console.log(data);
								if (data.response === "success") {
										console.log("success");
										if (data.url) {
												window.location.href = data.url;
										} else {
												window.location.reload(true);           
										}
								}
							}, "json");     

						},
						function(err) {
							postdata['status'] = 'failed';
							postdata.path = filekey;
							$.post(log_activity, postdata, function(data) {}, "json");	
							thisUploader.find('.progressbar-container').hide()
							thisUploader.find('.progressbar-message').addClass('error').text(err.message).show();
						}
					);

					thisUploader.find('.upload-form').hide();
					thisUploader.find('.progressbar-container').show();
					thisUploader.find('.progressbar').progressbar({
						value: false
					});
				}
			
			});
			

	
		});
	
	}	
	
	$('.uploader-container').each(function() {
		uploader.binder($(this));
	});

});