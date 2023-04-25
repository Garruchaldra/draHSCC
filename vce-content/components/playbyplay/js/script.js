$(document).ready(function() {


	$('#color-picker').iris({
		hide: true,
		palettes: true,
		target: $('#picker-box'),
		change: function(event, ui) {
			$('#color-picker').css('color', ui.color.toString()).css('background', ui.color.toString());
		}
	});

	$('#create-pbp-cancel').on('click touchend', function(e) {
		$('#color-picker').iris('toggle');
	});

	$('.play-clickbar').on('click touchend', function(e) {
	
		var player = $(this).closest('.media-item').find('.vidbox').attr('player');

		if ($(this).hasClass('clickbar-closed')) {
			if (typeof videoPlayer === 'object') {	
				var player = $(this).closest('.media-item').find('.vidbox').attr('player');
				videoPlayer[player].startVideoPlayer();
			}
		} else {
			if (typeof videoPlayer === 'object') {
				var player = $(this).closest('.media-item').find('.vidbox').attr('player');
				videoPlayer[player].pauseVideoPlayer();
			}
		}
	});


	$(document).on('click touchend','.play-timestamp', function(e) {
		var posiition = $('.vidbox').position();
 		$("html, body").animate({ scrollTop: posiition.top }, "slow");
		var timestamp = $(this).attr('timestamp');
		if (typeof videoPlayer === 'object') {
			var player = $(this).closest('.media-item').find('.vidbox').attr('player');
			videoPlayer[player].shuttleVideoPlayer(timestamp);
		}
	});

	$(document).on('click touchend','.play-each', function(e) {

		var play = $(this);
		var asyncontid = $(this).attr('asyncontid');
		var parent_container = $('#' + $(this).attr('pbp'));

		var postdata = [];
		postdata.push(
			{name: 'dossier', value: $(this).attr('dossier')}
		);
	
		if (typeof videoPlayer === 'object') {
			var player = $(this).closest('.media-item').find('.vidbox').attr('player');
			var timestamp = videoPlayer[player].getVideoPlayerTimestamp();
			var nicetimestamp = videoPlayer[player].getVideoPlayerNiceTime();
			if (timestamp > 0) {
				postdata.push(
					{name: 'timestamp', value: timestamp}
				);
			}
		}
	
		$.post($(this).attr('action'), postdata, function(data) {

			var layout_container = $(parent_container).parent();

			$(parent_container).children('.clickbar-title').toggleClass('clickbar-closed');
			$(parent_container).children('.clickbar-content').slideUp();

			if (typeof videoPlayer !== 'object') {
				$('.play-timestamp').remove();
			}
	
			var asynchronous_content = $('#' + asyncontid).html();
		
			asynchronous_content = asynchronous_content.replace("{background}", $(play).css('backgroundColor'));

			asynchronous_content = asynchronous_content.replace("{interaction}", $(play).attr('interaction'));

			if (typeof videoPlayer === 'object') {

				asynchronous_content = asynchronous_content.replace("{timestamp}", timestamp);
				asynchronous_content = asynchronous_content.replace("{nice-timestamp}", nicetimestamp);

			}

			$(layout_container).append($.parseHTML(asynchronous_content));
		
			if (typeof videoPlayer === 'object') {
				$(play).closest('.vidbox-content').fadeOut('slow');
				videoPlayer[player].startVideoPlayer();
			}
	
		}, 'json');

	});


	$('.play-edit').on('click touchend', function(e) {
		e.stopPropagation();
		var postdata = [];
		postdata.push(
			{name: 'dossier', value: $(this).attr('dossier')}
		);
		$.post($(this).attr('action'), postdata, function(data) {
			if (data.response === 'success') {
				window.location.reload(1);
			}
		}, 'json');
	});


	$('.play-delete').on('click touchend', function(e) {
		e.stopPropagation();
		if (confirm("Are you sure you want to delete?")) {
			var interaction = $(this).closest('.play-each');
			var postdata = [];
			postdata.push(
				{name: 'dossier', value: $(this).attr('dossier')}
			);
			$.post($(this).attr('action'), postdata, function(data) {
				if (data.response === 'success') {
					window.location.reload(1);
				}
			}, 'json');
		} 
	});


	$(document).on('click touchend','.play-reload', function(e) {
		window.location.reload(1);
	});

});