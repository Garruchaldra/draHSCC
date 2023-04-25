var videoPlayer = videoPlayer || { };

$(document).ready(function() {

	var vimeovideo = vimeovideo || { };
	
	vimeovideo.binder = function(player) {
	
		var playerid = $('#' + player);

		var controller = new Vimeo.Player(playerid);

		var vidbox = $(playerid).closest('.vidbox');
		var vidboxContent = $(vidbox).find('.vidbox-content');
		
		$(vidbox).bind('contextmenu', function(e) {
            e.preventDefault();
        });
		
		var videoclick = false;
		$(document).on('click', '.vidbox-click-control-vimeo', function(e) {
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
		
		controller.getDuration().then(function(duration) {
 			videoPlayer[player].metadataLoaded();
		});

		controller.on('play', function(e) {
			console.log(player + ' video has started playing');
			videoPlayer[player].hideVidbox();
		});
		
		controller.on('ended', function() {
			console.log(player + ' video has finished playing');
		});
		

		controller.on('timeupdate', function(e) {
			var micro = e.seconds * 1000;
			videoPlayer[player].timeStampListener(micro);
			videoPlayer[player].timeStamp = micro;
			videoPlayer[player].percentageComplete = e.percent;
			videoPlayer[player].duration = function() {
				return (e.duration * 1000);
			};
		});

		// videoPlayer object
		videoPlayer[player] = {
			metadataLoaded: function() {
				// this is a placeholder function and can be reassigned in any
				// annotation type that needs to wait for all video data to be available
			},
			duration: function() {
				return $('#' + player).attr('duration');
			},
			pauseVideoPlayer: function() {
				controller.pause();
			},
			startVideoPlayer: function() {
				controller.play();
			},
			buffering: false,
			shuttleVideoPlayer: function(timestamp) {
				controller.setCurrentTime((timestamp / 1000));
			},
			timeStampListener: function(timestamp) {
			},
			timeStamp: 0,
			bufferVideo: function () {
			},
 			percentageComplete: 0,
			getVideoPlayerTimestamp: function() {
				return this.timeStamp;
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
				return msToTime(this.timeStamp)
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

		$('.vidbox-content-close').on('click', function(e) {
			var player = $(this).closest('.vidbox').attr('player');
			videoPlayer[player].hideVidbox();
			videoPlayer[player].startVideoPlayer();
		});
		
		var rotateby = 90;
		$('#rotate-' + player).on('click', function(e) {
		var rotateto = 'rotate(' + rotateby + 'deg)';
		var thisplayer = $(playerid);
		var thisClickControl = $(thisplayer).siblings('.vidbox-click-control-vimeo');
		thisplayer.css('transform', rotateto);
		thisClickControl.css('transform', rotateto);
		var vc = thisplayer.closest('.vidbox');
		thisplayer.css('height', parseInt(vc.css('width')));
		rotateby += 90;
		});
	};
	
	// find videos
	$('.player').each(function(index) {
		var player = $(this).attr('id');
		vimeovideo.binder(player);
	});

	// refresh button
	$('.reload-button').click(function () {
		window.location.reload();
	});
});
