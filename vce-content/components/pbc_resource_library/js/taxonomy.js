$(document).ready(function() {

                

	$(function() {
		var level = {};
		$('.resource-library-taxonomy').each(function(index) {
			var currentLevel = $(this).attr('level');
			var classLevel = '.cat-level-' + currentLevel;
			if (!level[currentLevel]) {
				$('.cat-level-' + currentLevel).sortable({
					connectWith: '.resource-library-taxonomy',
					change: function( event, ui ) {
						$('.sub-category-addition').fadeOut();
						$('.category-delete').fadeOut();
						$('#update-categories input[type=submit]').addClass('highlighted');
					}
				}).disableSelection();
			}
			level[currentLevel] = true;
		});
	});


	$('#update-categories').on('submit', function(e) {

		e.preventDefault();

		var sortOrder = stucturize($('.cat-level-1'), 1, 1);
	
		function stucturize(ulClass, ulLevel, currentsort) {
			var sort = {};
			$(ulClass).children('li').each(function() {
				var info = {};
				info['sequence'] = currentsort;
				// info['category_name'] = $(this).text();
			
				var nextLevel = (ulLevel + 1);
				var nextUl = $(this).find('.cat-level-' + nextLevel);
				info['children'] = stucturize(nextUl,nextLevel, 1);
			
				var current = $(this).attr('item');
				sort[current] = info;
			
				currentsort++;
			
			});
			return sort;
		}
	
		var inputtypes = [];
		inputtypes.push({name: 'sort', type : 'json'});
	
		var postdata = $(this).serializeArray();

		postdata.push(
			{name: 'sort', value: JSON.stringify(sortOrder)},
			{name: 'inputtypes', value: JSON.stringify(inputtypes)}
		);
		$.post($(this).attr('action'), postdata, function(data) {
			console.log(data);
			if (data.response === 'success') {
				window.location.reload(1);
			}
		}, 'json');

	});


	$('.category-delete').on('click touchend', function(e) {
		e.stopPropagation();
		if (confirm("Are you sure you want to delete?")) {
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


	$('.sub-category-addition').on('click touchend', function(e) {
		$(this).closest('li').find('.add-sub-category').first().toggle();
	});
	
	
	$('.sub-category-edit').on('click touchend', function(e) {
		$(this).siblings('.category-name').toggle();
		$(this).siblings('.edit-category').toggle();
	});
	
	
	$('.edit-category').on('submit', function(e) {
		e.preventDefault();
		var categoryName = $(this).siblings('.category-name');
		var editForm = $(this);
		var updateName = $(this).find('input[name=category_name]').val();

		var postdata = $(this).serializeArray();
		$.post($(this).attr('action'), postdata, function(data) {
			if (data.response === 'success') {
				categoryName.text(updateName);
				categoryName.toggle();
				editForm.toggle();
			}
		}, 'json');
	});

	$(document).on('click touchend','.cancel-button', function(e) {
		window.location.reload(1);
	});

});