$(document).ready(function() {

    	var menu = 	$("#slow_content_loader");
        // alert(menu.attr('datalist_id'));
        var action = menu.attr('action');
		postdata = [];
		postdata.push(
            {name: 'dossier', value: menu.attr('dossier')},
            {name: 'this_component_id', value: menu.attr('this_component_id')},
            {name: 'inputtypes', value: menu.attr('inputtypes')}

        );
        
    		$.post(action, postdata, function(data) {
                $('.category-spinner').remove();
                // console.log('data: ' + data);
                $("#slow_content_loader").replaceWith(data);
                                

            }, "html");
            

        var menu2 = $("#slow_content_loader2");
        // alert(menu.attr('datalist_id'));
        var action2 = menu2.attr('action');
		postdata = [];
		postdata.push(
            {name: 'dossier', value: menu2.attr('dossier')},
            {name: 'inputtypes', value: menu2.attr('inputtypes')},
            {name: 'taxonomy', value: menu2.attr('taxonomy_items')}
        );
        


    		$.post(action2, postdata, function(data) {
            //    console.log('data2: ' + data);
                //var parsed_data = $.parseHTML(data);
                $("#slow_content_loader2").replaceWith(data);
			}, "html");

});