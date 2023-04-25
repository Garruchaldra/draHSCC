var videoPlayer = videoPlayer || { };

$(document).ready(function() {

	var htmlvideo = htmlvideo || { };
	
	htmlvideo.binder = function(player) {
	
		var playerid = $('#' + player);
		
		var controller = $(playerid).get(0);

		var vidbox = $(playerid).closest('.vidbox');
		var vidboxContent = $(vidbox).find('.vidbox-content');
		
        $(vidbox).bind('contextmenu', function(e) {
            e.preventDefault();
        });
		
		var videoclick = false;
		$(vidbox).on('click', '.vidbox-click-control', function(e) {

			function findremotes(vidbox, counter) {
				var location = $(vidbox).find('.remote-container');
				if (location.length !== 0) {
					var vidboxiframe = $(vidbox).find('.player');
					var vidboxWidth = vidboxiframe.width();
					var vidboxHeight = vidboxiframe.height();
					var vidboxContent = $(vidbox).find('.vidbox-content');
					$(vidboxContent).css('width', (vidboxWidth - 80) + 'px').css('height', (vidboxHeight - 120) + 'px');
					return location;
				}
				counter++;
				if (counter < 6) {
					return findremotes($(vidbox).parent(), counter);
				}
			}
			
			var remotes = findremotes(vidbox,1);
			if ($(remotes).length) {
				if (!videoclick) {
					var remote = "";
					$(remotes).each(function(index) {
						if (!$(this).parents('.remote-ignore').length) {
							remote += $(this).html();
						}
					});
					videoPlayer[player].addVidboxContent(remote);
					videoclick = true;
				}
				videoPlayer[player].showVidbox();
				videoPlayer[player].pauseVideoPlayer();
			}
			
		});
		
		// https://developer.mozilla.org/en-US/docs/Web/Guide/Events/Media_events

		$(playerid).on('playing', function () {
			console.log(player + ' video has been started');
			$(vidbox).find('.vidbox-click-control').show();
		});
	
		// video has finished playing		
		$(playerid).on('ended', function () {
			console.log(player + ' video has finished playing');
		});
		
			
		// videoPlayer object
		videoPlayer[player] = {
			pauseVideoPlayer: function() {
				controller.pause();
			},
			startVideoPlayer: function() {
				controller.play();
			},
			shuttleVideoPlayer: function(timestamp) {
				controller.currentTime = (timestamp / 1000);
				this.percentageComplete();
			},
			timeStampListener: function(timestamp) {
			},
			timeStamp: 0,
			duration: function() {
				return controller.duration * 1000;  // convert seconds to timestamp
			},
			bufferVideo: function () {
				controller.load();
			},
 			percentageComplete: function() {
 				var getPercentage = Math.round((controller.currentTime / controller.duration) * 100);
 				return getPercentage;
			},
			getVideoPlayerTimestamp: function() {
				var currentMicroTime = Math.round(controller.currentTime * 1000);
				return currentMicroTime;
			},
			getVideoPlayerNiceTime: function() {
				function msToTime(s) {
	 				function addZ(n) {
    					return (n < 10 ? '0' : '') + n;
 					}
 					var ms = s % 1000;
 					s = (s - ms) / 1000;
 					var secs = s % 60;
 					s = (s - secs) / 60;
 					var mins = s % 60;
					var hrs = (s - mins) / 60;
  					return hrs + ':' + addZ(mins) + ':' + addZ(secs);
				}
				var currentMicroTime = Math.round(controller.currentTime * 1000);
				return msToTime(currentMicroTime);
			},
			showVidbox: function() {
				$(vidboxContent).fadeIn('slow');
			},			
			hideVidbox: function() {
				$(vidboxContent).fadeOut('slow');
			},
			addVidboxContent: function(content) {
				$(vidboxContent).children('.vidbox-content-area').html($.parseHTML(content));
			}
		}
		
		controller.ontimeupdate = function () {
			timeStamp = Math.round(controller.currentTime * 1000);
			videoPlayer[player].timeStampListener(timeStamp);
			videoPlayer[player].timeStamp = timeStamp;
		};

		setTimeout(function() {
			if (typeof videoPlayer[player].metadataLoaded === "function") {
				videoPlayer[player].metadataLoaded();
			}
		}, 1000);

		$('.vidbox-content-close').on('click', function(e) {
			var player = $(this).closest('.vidbox').attr('player');
			videoPlayer[player].hideVidbox();
			videoPlayer[player].startVideoPlayer();
			e.stopPropagation();
		});
		
	};
	
	// find videos
	$('.player').each(function(index) {
		var player = $(this).attr('id');
		htmlvideo.binder(player);
	});

});