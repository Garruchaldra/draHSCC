$(document).ready(function() {
    $('.users_select').select2({placeholder: "Click here to select people."});
           

    // create json object from selected users when page has loaded
    $('.multiple-select-users').each(function(i, obj) {
        getSelectedOptions(obj);
    });
        
	 
	
	// arguments: reference to select list, callback function (optional)
	function getSelectedOptions(sel) {
        // console.log(sel.id);
        var element_id = sel.id;
        var phpvars = $('#multiple-select-data-' + element_id).data();

        // phpvars.foo 

        $.each(phpvars, function( key, value ) {
            eval( key + " = " + "'" + value + "'");
        });

        //  foo = 

        var opts = [], opt;
		
		// loop through options in select list
		for (var i=0, len=sel.options.length; i<len; i++) {
			opt = sel.options[i];
			
			// check if selected
			if ( opt.selected ) {
				// add to array of option elements to return from this function
				opts.push(opt.value);
			}
		}
		$('#selected-users-' + element_id).val('{"dl":"' + element_id + '", "dl_id":"' + dl_id + '","dl_name":"' + dl_name + '", "user_ids":"' + opts.join('|') + '"}');
		
	}
	

	// anonymous function onchange for select list with id demoSel
	
	$('.multiple-select-users').on('change', function(e) {

        getSelectedOptions(this);
        // console.log(this);

	});

	
	
});
