function onformsuccess(formsubmitted,data) {

	console.log(formsubmitted);
    // console.log(data);
    console.log(data.message);
    // $(formsubmitted).prepend('<div class="form-message form-error">' + data.message + '</div>');
	if (data.form === "reset" && data.response_type === "error") {
        $(formsubmitted).prepend('<div class="form-message form-error">' + data.message + '</div>');

    }
    if (data.form === "reset" && data.response_type === "success") {
        $(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
        setTimeout(function(){
            window.location.reload(1);
         }, 2000);

	}


	if (data.form === "change_id" && data.response_type == "masquerade") {

        $(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
	
		setTimeout(function(){
	   	window.location.reload(1);
		}, 2000);

    }
    
    if (data.form === "change_id" && data.response_type === "no_masquerade") {

        $(formsubmitted).prepend('<div class="form-message form-error">' + data.message + '</div>');

	}

	// if (data.procedure == "reset") {
    //     alert(data.message);
    //     $(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');

	// 	window.location.reload(1);

    // }
    
    // if (data.procedure == "error") {
    //     alert(data.message);
    //     $(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');

	// 	// window.location.reload(1);

	// }

}




$(document).ready(function() {
	



});