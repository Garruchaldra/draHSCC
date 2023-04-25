function onformerror(formsubmitted,data) {

	if (data.form == "create") {
		$(formsubmitted).prepend('<div class="form-message form-error">' + data.message + '</div>');
	}

	if (data.form == "masquerade") {
		$(formsubmitted).prepend('<div class="form-message form-error">' + data.message + '</div>');
	}

	if (data.form == "delete") {
		$(formsubmitted).parent().prepend('<div class="form-message form-error">' + data.message + '</div>');
	}

}

function onformsuccess(formsubmitted,data) {

	console.log(formsubmitted);

	if (data.form === "edit") {
		window.location.reload(1);
	}

	if (data.form == "create") {

		$(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
	
		setTimeout(function(){
	   	window.location.reload(1);
		}, 2000);

	}

	if (data.form == "masquerade") {

		window.location.href = data.action;	   	

	}

	if (data.form == "delete") {

		window.location.reload(1);

	}

}

$(document).ready(function() {


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

});