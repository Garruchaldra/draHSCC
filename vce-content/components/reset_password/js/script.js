function onformerror(formsubmitted,data) {

	$(formsubmitted).prepend('<div class="form-message form-error">' + data.message + '</div>');

}

function onformsuccess(formsubmitted,data) {

	$(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');

	if (data.action == "reload") {
		setTimeout(function(){
	   	window.location.href = data.url;
		}, 2000);
	}

}

$(document).ready(function() {
	$('.show-password-input').change(function() {
		if ($(this).is(':checked')) {

			$('.password-input').attr('type', 'text');
		} else {

			$('.password-input').attr('type', 'password');
		}
	});
});
