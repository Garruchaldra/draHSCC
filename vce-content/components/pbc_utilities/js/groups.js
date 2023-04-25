$(document).ready(function() {
    // $('select[name=organization]').change(function() {

    // 	var menu = $(this);
    // 	if (menu.attr('action')) {
    // 		var action = menu.attr('action');
    // 	} else {
	// 		var action = menu.closest('form').attr('action');
	// 	}
	// 	selected = menu.find('option:selected').val();
	// 	postdata = [];
	// 	postdata.push(
	// 		{name: 'dossier', value: menu.attr('dossier')},
	// 		{name: 'org_list', value: menu.attr('org_list')},
	// 		{name: 'native_org', value: menu.attr('native_org')},
	// 		{name: 'datalist_id', value: menu.attr('datalist_id')},
	// 		{name: 'item_id', value: selected}
	// 	);
    // 	if (selected) {
    	
    // 		$.post(action, postdata, function(data) {
    // 		console.log(data);
    // 		$('select[name=group] option').remove();
    // 		$.each(data, function(item) {
    // 			if (menu.attr('restrict_groups') && menu.attr('native_org') != selected) {
    // 				if (additional_groups.includes(data[item].item_id) == true) {
	// 					$('select[name=group]').append($("<option></option>").attr("value",data[item].item_id).text(data[item].name));
	// 				}
	// 			} else {
	// 				$('select[name=group]').append($("<option></option>").attr("value",data[item].item_id).text(data[item].name));
	// 			}
	// 		});
	// 		$('select[name=group]').append($("<option></option>").attr("value",'').text(''));
	// 		}, "json");
    // 	} else {
    // 		$('select[name=group] option').remove();
    // 	}
	// })
	

	$('select[name=organization]').change(function() {
// console.log('changed');
    	var menu = $(this);
    	if (menu.attr('action')) {
			var action = menu.attr('action');
    	} else {
			var action = menu.closest('form').attr('action');
		}

		if (menu.attr('group_class')) {
			var group_class = menu.attr('group_class');
    	} 
// console.log(group_class);
		selected = menu.find('option:selected').val();
		
		postdata = [];
		postdata.push(
			{name: 'dossier', value: menu.attr('dossier')},
			{name: 'role_hierarchy', value: menu.attr('role_hierarchy')},
			{name: 'datalist_id', value: menu.attr('datalist_id')},
			// {name: 'org_group_role_list', value: org_group_role_list},
			{name: 'item_id', value: selected}
		);
    	if (selected) {
			// console.log(selected);
			// alert(JSON.stringify(org_group_role_list));
		
			// contact the groups() method in 
    		$.post(action, postdata, function(data) {
				console.log(data);
			if (typeof group_class !== 'undefined') {
				$('.' + group_class + ' option').remove();
			} else {
				$('select[name=group] option').remove();
			}
			//cycle through all groups which belong to the chosen org
    		$.each(data, function(item) {
				// console.log(data[item].name);
					//cycle through all groups the user belongs to (native and floating)
					if(typeof org_group_role_list !== 'undefined'){
						// console.log('if');
						$.each(org_group_role_list, function(i, val) {
							// console.log('val: ' + val);
							// break the group/role value into component parts
							$.each(val, function(i2, val2) {
								// console.log('yep: ' + i2 + ' '+ val2 + ' item_id: ' + data[item].item_id);
								//if lower than group admin
								if (val2 == 1 ) {
									$('select[name=group]').append($("<option></option>").attr("value",data[item].item_id).text(data[item].name));
									// console.log('one');
								} else if (val2 == 4 ) {
									$('select[name=group]').append($("<option></option>").attr("value",data[item].item_id).text(data[item].name));
									// console.log('four');
								} else if (val2 == 5 ) {
									$('select[name=group]').append($("<option></option>").attr("value",data[item].item_id).text(data[item].name));
									// console.log('five');
								} else if (i2 == data[item].item_id && val2 == 6 ) {
									$('select[name=group]').append($("<option></option>").attr("value",data[item].item_id).text(data[item].name));
									// console.log('six');
									return false;
								}else if (val2 == 2 ) {
									$('select[name=group]').append($("<option></option>").attr("value",data[item].item_id).text(data[item].name));
									// console.log('two');
									return false;
								}else if (i2 == data[item].item_id && val2 == 3 ) {
									$('select[name=group]').append($("<option></option>").attr("value",data[item].item_id).text(data[item].name));
									// console.log('three');
									return false;
								}
							});	
						});	
					} else {
						// console.log('else');
						var admin_group_id = $('.admin_group_id').attr('admin_group_id');
						if (typeof admin_group_id !== 'undefined' && admin_group_id !== false) {
							if (data[item].item_id == admin_group_id) {
								$('select[name=group]').append($("<option></option>").attr("value",data[item].item_id).text(data[item].name));
							}
						} else if (typeof group_class !== 'undefined') {
							$('.' + group_class).append($("<option></option>").attr("value",data[item].item_id).text(data[item].name));
						} else {
							$('select[name=group]').append($("<option></option>").attr("value",data[item].item_id).text(data[item].name));
						}

					}
						// console.log('data: ' + menu.attr('org_list'));
				});
				

			$('select[name=group]').append($("<option></option>").attr("value",'').text(''));

			$('select[name=group] option').each(function(index,element){
				if(element.selected){
						group = element.value;
						// console.log('group: ' + group);
					}
				});
				// console.log(org_group_role_list);
				if(typeof org_group_role_list !== 'undefined'){
					
					$('select[name=organization] option').each(function(index,element){
						if(element.selected){
							// console.log(pagesiteroles);
								org = element.value;
								// console.log('group: ' + group);
								// console.log('org: ' + org);
								// console.log('org_g_r_l: ' + JSON.stringify(org_group_role_list));
								role_id = org_group_role_list[org][group];
								// console.log("role_id:"+role_id);
								role_name = pagesiteroles[role_id]['role_name'];
								
								// console.log(element.value);
								// console.log(element.text);
								// console.log(org_group_role_list[org][group]);
							}
						});
						$('input[name="role_id"]').val(role_id);
						$("#role").html(role_name);
					}
			}, "json");
    	} else {
    		$('select[name=group] option').remove();
    	}
    })
});