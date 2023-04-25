$(document).ready(function() {
	
	if ($('.datepicker').length) {
		$('.datepicker').datepicker();
	}
	
	// edit/add toggle
	$('.click-tab').on('click touchend', function(e) {
		e.preventDefault();
		$('.tab').removeClass('active-tab');
		$(this).addClass('active-tab');
		$('.tab-content').removeClass('active-tab-content');
		var current_tab = '#' + $(this).attr('tab');
		$(current_tab).addClass('active-tab-content');
	});
	
	
				//add resource from resource library
	//first, call method to add resource_caller to page, then redirect to the resource library
        $('.add-requester').click(function()  {

                var postdata = [];
                postdata.push(
                        {name: 'dossier', value: $(this).attr('dossier')}
                );
//                 alert(postdata);
                $.post($(this).attr('action'), postdata, function(data) {
                        if (data.response === "success") {
                                window.location.href = data.url;
                        }
                }, "json");

        });

});