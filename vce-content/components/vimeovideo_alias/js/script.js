$(document).ready(function() {

	$('.video-thumbnail').on('click',function(e) {

		var video = $(this).closest('.video-thumbnail-border');

		if (video.hasClass('video-selected')) {
			$('#vimeovideo-alias').find("input[name='dossier']").val('');
			$(this).removeAttr('selected')
			$('.video-thumbnail-border').removeClass('video-selected');
		} else {
			$('#vimeovideo-alias').find("input[name='dossier']").val($(this).attr('dossier'));
			$(this).attr('selected',true);
			$('.video-thumbnail-border').removeClass('video-selected');
			video.addClass('video-selected');
		}

	});





	$('#vimeovideo-alias').on('submit', function(e) {

		e.preventDefault();
	
		if ($(this).find("input[name='dossier']").val() === "") {
			alert('Select The video you would like to submit');
		}
		
		var postdata = [];
		postdata.push(
			{name: 'dossier', value: $(this).find("input[name='dossier']").val()},
		);
		
		
		$.post($(this).attr('action'), postdata, function(data) {
			if (data.response === 'success') {
				window.location.reload(true);	
			} else {
				console.log(data);
			}
		}, "json")
		.fail(function(response) {
			console.log('Error: Response was not a json object');
			$(formsubmitted).prepend('<div class="form-message form-error">' + response.responseText + '</div>');
		});

	});
	
	
	

});