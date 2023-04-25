function onformerror(formsubmitted,data) {
	$(formsubmitted).prepend('<div class="form-message form-error">' + data.message + '</div>');
	if (data.action == "clear") {
		$(formsubmitted).trigger('reset');
	}
	if (data.action == "reload") {
		setTimeout(function(){
			window.location.reload();
		}, 3000);
	}

}





function onformsuccess(formsubmitted,data) {
	$(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
	if (data.url) {
		setTimeout(function(){
			window.location.assign(data.url);
		}, 3000);
	} else {
		setTimeout(function(){
			window.location.reload();
		}, 3000);
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
