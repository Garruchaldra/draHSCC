$(document).ready(function() {

	$(document).on('change', '.keyword-checkbox', function(e) {
	
		var currentLabel = $(this).parent('label.keyword-title');

		$(currentLabel).toggleClass('current');

		keywordFilter();

	});
	
	
	function keywordFilter() {

		$('.webdam-item-container').hide();
	
		var checkCounter = 0;
		var classList = "";
	
		$('.keyword-checkbox').each(function(key, value) {

			if (value.checked) {
				checkCounter++;
				classList += '.' + $(this).attr('keyword');
			}

		});
	
		var mediaCount = $(classList).length;
	
		if (checkCounter > 0) {
			$('.filter-count').html(' | ' + mediaCount + ' KEYWORD RESULTS');
		} else {
			$('.filter-count').html('');
		}
		
		$(classList).parents('.webdam-item-container').show();
	
		if (checkCounter === 0) {
			$('.webdam-item-container').show();
		}

	}

	
	$(document).on('click', '.keyword-container', function(e) {
		// change background color of filter button if it has an item checked
		if ($(this).siblings('.keyword-list').children('.current').length !== 0 || $(this).siblings('.keyword-list').children('.sub-explore').children('.current').length !== 0) {
			$(this).addClass('filter-selected');
		} else {
			$(this).removeClass('filter-selected');
		}
console.log("click");
		var thisList = $(this).closest('.keyword-block').find('.keyword-list');
	
		$(thisList).toggle();
		$(this).find('.keyword-count').toggleClass('container-open');
	
	});

	$(document).on('click', '.close-filter', function() {
		// change background color of filter button if it has an item checked
		if ($(this).closest('.keyword-list').children('.current').length !== 0 || $(this).closest('.keyword-list').children('.sub-explore').children('.current').length !== 0) {
			$(this).closest('.keyword-block').find('.keyword-container').addClass('filter-selected');
		} else {
			$(this).closest('.keyword-block').find('.keyword-container').removeClass('filter-selected');
		}

		$(this).closest('.keyword-list').toggle();
		$(this).closest('.keyword-block').find('.keyword-count').removeClass('container-open');
	});

	// main drop down categories
	// $(document).on('click', '.explore-title', function(e) {
	
	// 	if (!$(this).hasClass('container-open') && !currentExploreKeyword) {
		
	// 		$('.explore-title').removeClass('container-open');
	// 		$('.explore-list-block').removeClass('explore-list-open');

	// 		$(this).addClass('container-open');
	// 		$(this).next('.explore-list-block').addClass('explore-list-open');
		
	// 	}
	
	// });


	$(document).on('click keydown', '.webdam-item', function(e) {
		if (e.type === 'click' || e.which === 13) {
			var details = '#view-' + $(this).attr('code');
		
			if (!descriptionChecker[details]) {

				var thisMediaItem = mediaItemDescriptionTemplate;
		
				thisMediaItem = thisMediaItem.replace(/{classes}/g, 'webdam-item');
				thisMediaItem = thisMediaItem.replace(/{code}/g, $(this).attr('code'));
				thisMediaItem = thisMediaItem.replace(/{title}/g, $(this).attr('name'));
				thisMediaItem = thisMediaItem.replace(/{description}/g, $(this).attr('description'));
				thisMediaItem = thisMediaItem.replace(/{filesize}/g, $(this).attr('filesize') + ' MB');
				thisMediaItem = thisMediaItem.replace(/{product}/g, $(this).attr('product'));
				thisMediaItem = thisMediaItem.replace(/{keyword_list}/g, $(this).attr('keyword_list'));
				thisMediaItem = thisMediaItem.replace(/{download}/g, $(this).attr('download'));
				thisMediaItem = thisMediaItem.replace(/{video}/g, '<source src="' + $(this).attr('video') + '">');

			
				if ((navigator.userAgent.indexOf('Chrome') == -1) && (navigator.userAgent.indexOf("Safari") > -1)) {
				
				}
				
				var newMediaItem = $.parseHTML(thisMediaItem);
				
				$('.webdam-popover-content').html(newMediaItem);
				
				$('.webdam-popover').fadeIn();
		
				$(this).parent('.media-item-container').after(newMediaItem);

				descriptionChecker[details] = true;
			
			}
		}
	});

	$(document).on('click', '.close', function(e) {

		var key = '#' + $(this).closest('.webdam-item-details').attr('id');

		delete descriptionChecker[key];
		
		$('.webdam-popover').fadeOut();

		$(this).closest('.webdam-item-details').remove();

	});

	
	$(document).on('click', '.embed-link', function(e) {

		formsubmitted = $(this);

		var postdata = [];
		
		postdata.push(
			{name: 'dossier', value: $(this).attr('dossier')},
			{name: 'inputtypes', value: '[]'},
			{name: 'media_type', value: $(this).attr('type')},	
			{name: 'title', value: $(this).attr('title')},			
			{name: 'code', value: $(this).attr('code')}
		);
		
		$.post($(this).attr('action'), postdata, function(data) {
			if (data.response === "success") {
				window.location.reload(1);
			}
		}, "json")
		.fail(function(response) {
			console.log('Error: Response was not a json object');
			$(formsubmitted).prepend('<div class="form-message form-error">' + response.responseText + '</div>');
		});
	});

	// setting this to false will hide keywords as search filtering
	var showKeywords = false;
	
	if (!showKeywords) {
		$('.search-keywords').hide();
	}
	
	var searchCount = 0;
	var totalCount = 0;
	var folderChecker = [];
	var searchActive = false;
	var searchKeywords = [];
	var keywordCount = 0;
	var descriptionChecker = [];
	var exploreChecker = [];
	var currentExploreKeyword = null;
	var request = null;
	
	var mediaItemTemplate = $('.webdam-item-template').html();
	var mediaItemDescriptionTemplate = $('.webdam-item-description-template').html();
	
	$('.search-icon').on('click', function(e) {
		if ($('.search').val() == "") {
			return;
		}
		if (searchActive === false) {
			$(this).siblings('.search').change();
		}
	});

	$('.search').on('change', function(e) {
	
		resetBrowse();
	
		clearResults();
		
		var query = {};
		query.query = $(this).val();
		query.dossier = $(this).attr('dossier');
		query.inputtypes = '[]';
		
		var action = $(this).attr('action');
		
		startSearch(query, action);
		
	});
	
	/* click on the main browse categories */
	$(document).on('click', '.explore-keywords', function(e) {
		
		// prevent a re-click
		if ($(this).hasClass('current-explore')) {
			return;
		}
		
		currentExploreKeyword = $(this);
		
		// $(this).closest('.explore-title-list').siblings().slideUp();
		
		// $(this).find('.explore-keywords-cancel').show();

		$('.filter-by-block').show();
		$('.keyword-block > .sub-explore').show();
		$('.keyword-container').removeClass('filter-selected');

		clearResults();
		$('.search').val('');
		$('.keyword-list.type-filter').empty();
	
		// $('.sub-explore').slideUp();

		var clone = $('.' + $(this).attr('code')).clone();

		$('.keyword-list.type-filter').prepend(
			clone.append($('<button class="close-filter">').text('done'))
		);

		// $('.' + $(this).attr('code')).slideDown();
	
		var query = {};
		query.query = $(this).attr('value');
		query.dossier = $(this).attr('dossier');
		query.inputtypes = '[]';
	
		if (!exploreChecker[query]) {
		
			clearResults();
	
			$(this).addClass('current-explore');
	
			var action = $(this).attr('action');
			
			searchKeywords[query] = true;
			
			exploreChecker[query] = true;
			
			startSearch(query, action);
		}

	});
	
	/* click on the main browse categories */
	// $(document).on('click', '.explore-keywords-cancel', function(e) {
	
	// 	e.stopPropagation();
		
	// 	$(this).hide();
	// 	currentExploreKeyword = null;
	// 	var exploreParent = $(this).closest('.explore-keywords');
	// 	$('.' + $(exploreParent).attr('code')).slideUp();
		
	// 	$(exploreParent).removeClass('current-explore');
		
	// 	$(this).closest('.explore-title-list').siblings().slideDown();
		
	// 	clearResults();
		
	// });
	
	
	function startSearch(query, action) {
	
		$('.webdam-progressbar').progressbar({
			value: false
		});
	
		searchActive = true;
		var searchKeywords = [];
		
		if (showKeywords && !$('.height-adjusted').length) {
			$('.search-keywords').find('.keyword-list').height($('.webdam-container').height() - $('.webdam-left-content').height()).addClass('height-adjusted');
		}
		
		if (!query) {
			alert('enter something to search for');
			return;
		}
		
		$('.loading-icon').show();
		$('.loading-cancel').show();
		
		sendPost(action,query);
	
	}
	
	function sendPost(action, postfields) {
	
		var postdata = [];

		$.each(postfields, function(key, value) {

			postdata.push(
				{name: key, value: value}
			);
		
		});
		
		request = $.post(action, postdata, function(data) {
			// console.log(data);
			if (data.status === 'success') {
					processObject(data, action, postfields);
			}
		}, "json");
	
	}
	
	function processObject(data, action, postfields) {
	
		$('.results').removeClass('splash');

		if (!folderChecker[data.results_folder]) {
			totalCount += data.total_count;
			folderChecker[data.results_folder] = true;
		}


		searchCount += data.count;
		
		var progress = (searchCount / totalCount) * 100;
		
		$('.webdam-progressbar').progressbar('value',progress);
		
		$('.total-results').html(searchCount + ' of ' + totalCount);
		
		$.each(data.media, function(key, value) {
		
			var thisMediaItem = mediaItemTemplate;

			
			if (!value.classes) {
				value.classes = 'webdam-item';
			}

			thisMediaItem = thisMediaItem.replace(/{classes}/g, value.classes);
			thisMediaItem = thisMediaItem.replace(/{code}/g, value.code);
			thisMediaItem = thisMediaItem.replace(/{video}/g, value.video);
			thisMediaItem = thisMediaItem.replace(/{thumbnail}/g, value.thumbnail);
			thisMediaItem = thisMediaItem.replace(/{title}/g, value.title);
			thisMediaItem = thisMediaItem.replace(/{description}/g, value.description);
			//thisMediaItem = thisMediaItem.replace(/{filesize}/g, value.filesize);
			//thisMediaItem = thisMediaItem.replace(/{product}/g, value.product);
			//thisMediaItem = thisMediaItem.replace(/{keyword_list}/g, value.keyword_list);
			thisMediaItem = thisMediaItem.replace(/{download}/g, value.download);			

			var newMediaItem = $.parseHTML(thisMediaItem);
		
			$('.results').append(newMediaItem);
			
		});
		
		if (showKeywords) {
		
			$.each(data.keywords, function(code, title) {
		
				if (!searchKeywords[code]) {
		
					var keyword = '<label class="keyword-title">' + title + ' <input class="keyword-checkbox" type="checkbox" keyword="' + code + '" name="' + code + '" value="' + title + '"></label>';

					var newKeyword = $.parseHTML(keyword);
			
					$('.keyword-list').append(keyword);
				
					searchKeywords[code] = true;
				
					keywordCount++;
			
				}
			
			});
		
		
			$('.search-keywords').find('.keyword-count').html(keywordCount);
		
		}
		
		keywordFilter();
		
		if (data.folder_id != 0) {
			postfields.total_count = data.total_count;
			if (data.results_folder === data.folder_id) {
				postfields.current_limit = data.current_limit;
				console.log(data.folder_count);
			} else {
				$('.loading-icon-text').html('Searching ' + data.folder_position + ' of ' + data.folder_count + ' folders');
				console.log('different folder');
				postfields.current_limit = 0;
			}
			postfields.folder_id = data.folder_id;
			sendPost(action,postfields);
		} else {
			console.log('done');
			$('.webdam-progressbar').progressbar('destroy');
			$('.loading-icon').hide();
			$('.loading-cancel').hide();
			searchActive = false;
			if (totalCount === 0) {
				setTimeout(alert('Nothing Found'), 500);
			}
		}
	
	}
	
	
	/* click on the main browse categories */
	$(document).on('click', '.loading-cancel', function(e) {
	
		// abort any active ajax calls
		if (request) {
			request.abort();
			$('.webdam-progressbar').progressbar().progressbar('destroy');
			$('.loading-icon').hide();
			$('.loading-cancel').hide();
		}
		
	});
	
	/* clear */
	$('.clear-results').on('click', function(e) {
		$('.search').val('');
		resetBrowse();
		clearResults();
	});
	
	
	function resetBrowse() {
		
		var exploreParent = $('.current-explore');
		$(exploreParent).find('.explore-keywords-cancel').hide();
		$('.' + $(exploreParent).attr('code')).slideUp();
		$(exploreParent).closest('.explore-title-list').siblings().slideDown();
	
	}
	
	
	/* function to clear */
	function clearResults() {
	
		folderChecker = [];
		searchCount = 0;
		totalCount = 0;
		keywordCount = 0;
		$('.webdam-item-container').remove();
		$('.keyword-title').not('.display').remove();
		$('.webdam-item-details').remove();
		$('.loading-icon-text').empty();
		var descriptionChecker = [];
		$('.keyword-count').empty();
		$('.total-results').html('0');
		searchKeywords = [];
		$('.explore-keywords').removeClass('current-explore');
		exploreChecker = [];
		// $('.search').val('');
		$('.loading-cancel').hide();
		
		$('.keyword-checkbox').prop('checked', false);
		$('.keyword-title').removeClass('current');
		
		// abort any active ajax calls
		if (request) {
			request.abort();
			$('.webdam-progressbar').progressbar().progressbar( "destroy" )
			$('.loading-icon').hide();
		}
		
	}

	// mobile menu
	$('.explore-list').click(function () {
		$(this).find('.explore-list-block.explore-list-open').toggleClass('show');
		$(this).find('.explore-block-title').toggleClass('open');
	});
});