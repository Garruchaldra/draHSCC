$(document).ready(function() {

	// .textarea-wysiwyg
	if ($('.textarea-wysiwyg').length) {
		tinymce.init({
		selector: '.textarea-wysiwyg',
		menubar: false,
		forced_root_block: '',
		entity_encoding: 'raw',
		remove_linebreaks : true,
		plugins: [
			'advlist autolink lists link charmap code paste table'
		],
		toolbar_items_size: 'small',
		toolbar: 'undo redo | styleselect | bold italic p | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent table | link charmap | code',
		paste_convert_word_fake_lists: false,
		setup: function (editor) {
       		editor.on('change', function () {
				tinymce.triggerSave();
        		});
   			 }
		});
	}
	
/*
	// https://alex-d.github.io/Trumbowyg/
	if ($('.textarea-wysiwyg').length) {
		$('.textarea-wysiwyg').trumbowyg({
    		btns: [
			['viewHTML'],
			'btnGrp-design',
			['link'],
			'btnGrp-justify',
			'btnGrp-lists',
			['removeformat'],
			['fullscreen']
   			]
		})
		.on('tbwfocus', function(){ 
			$(this).closest('label').removeClass('highlight-alert').addClass('highlight');
		})
		.on('tbwblur', function(){
			$(this).closest('label').removeClass('highlight');
		});

	}
*/	

/** WTF are btnGrp-* buttons? There are just arrays of strings :)
*  jQuery.trumbowyg.btnsGrps = {
*      design:   ['bold', 'italic', 'underline', 'strikethrough'],
*      semantic: ['strong', 'em'],
*      justify:  ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
*      lists:    ['unorderedList', 'orderedList']
*  };
*/

	$('.comments-icon').mouseover(function() {
		$(this).find('.instructor-comments').show();
	}).mouseout(function() {
		$(this).find('.instructor-comments').hide();
	});
	
	$('.pbcstep-link-button').on('click', function(e) {
		var url = $(this).attr('url');
		var pbcstep = $(this).attr('pbcstep');
		$('#link-url').val(url);
		$('#link-container').find('.label-message').html('pbcstep Link: ' + pbcstep);
		$('#link-container').fadeIn();
			$('#link-url').on('click', function(e) {
				$(this).select();
			});
	});
	
	$('#close-link-container').on('click', function(e) {
		$('#link-url').val('');
		$('#link-container').find('.label-message').html('');
		$('#link-container').fadeOut();
	});
	




    $(".popup").hide();
    $(".openpop").click(function (e) {
        e.preventDefault();
        $("iframe").attr("src", $(this).attr('href'));
        $(".links").fadeOut('slow');
        $(".popup").fadeIn('slow');
    });

    $(".close").click(function () {
        $(this).parent().fadeOut("slow");
        $(".links").fadeIn("slow");
    });



	$("#users").tablesorter({
		headers: { 
            0: { sorter: false }, 1: { sorter: false }, 2: { sorter: false }
        } 
	}); 


	$('#generate-password').on('click', function() {
		
		var length = 8,
        charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
        retVal = "";
    	for (var i = 0, n = charset.length; i < length; ++i) {
			retVal += charset.charAt(Math.floor(Math.random() * n));
		}
		
		$('input[name=password]').val(retVal);
	
	});
		
	//From:  https://github.com/dimsemenov/Magnific-Popup
//Video popup-fade
		$('.popup-youtube, .popup-vimeo, .popup-gmaps').magnificPopup({
			disableOn: 400,
			type: 'iframe',
			mainClass: 'mfp-fade',
			removalDelay: 160,
			preloader: false,

			fixedContentPos: false
		});


	// PROGRESS INFO BOXES
	// Make progress info boxes not keyboard accessible when content is hidden
	$('.progress-info-box-container')
		.find('*')
		.not('.progress-info-box-expand-btn')
		.attr('tabindex', -1)
		.css('outline', 'none');
	// Expand/collapse
	$('.progress-info-box-expand-btn').click(function() {
		$('.progress-info-box-container, .progress-info-box').toggleClass('progress-info-box-expanded');
		$('.progress-info-box-expand-btn > .arrow').toggleClass('arrow-open');

		// Make keyboard accessible
		if ($('.progress-info-box-expand-btn > .arrow').hasClass('arrow-open')) {
			$('.progress-info-box-container .progress-info-box__link, .progress-info-box-container #edit-btn')
				.attr('tabindex', 0);
		} else {
			// Make inaccessible
			$('.progress-info-box-container')
				.find('*')
				.not('.progress-info-box-expand-btn')
				.attr('tabindex', -1);
		}
	});

	// Set the width of the progress info box "more" button
	var infoboxWidth = 0;

	$('.progress-info-box').each(function() {
		infoboxWidth += $(this).outerWidth();
	});

	var containerWidth = $('.progress-section .inner').width();
	var infoboxWidthPercentage = ((infoboxWidth / containerWidth) * 100).toFixed(2) + '%';

	$('.progress-info-box-expand-btn').css('width', infoboxWidthPercentage);


	// Resources & Comments- expand/collapse
	$('.resources_comments_accordion_btn').on('click', function() {
		var linkLocation = $(this).attr('link_location');
		window.location = linkLocation;
	});
	$('.edit-toggle').on('click', function() {
		$('.edit-container').not('.ignore-admin-toggle').toggle();
		$('.edit-toggle').toggleClass('admin-toggle-active');
	});
});
