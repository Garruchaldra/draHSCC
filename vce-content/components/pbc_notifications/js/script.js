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


// function onformsuccess(formsubmitted,data) {
// 	console.log("formsuccess");
// 	console.log(formsubmitted);
// 	console.log(data.form);

// 	if (data.form === "edit") {
// 		window.location.reload(1);
// 	}

// 	if (data.form == "create") {

// 		$(formsubmitted).prepend('<div class="form-message form-success">' + data.message + '</div>');
	
// 		setTimeout(function(){
// 	   	window.location.reload(1);
// 		}, 2000);

// 	}

// 	if (data.form == "masquerade") {

// 		window.location.href = data.action;	   	

// 	}

// 	if (data.form == "delete") {

// 		window.location.reload(1);

// 	}


// 	if (data.form == "update") {

// 		window.location.reload(1);

// 	}

// }

$(document).ready(function() {


	// $("#users").tablesorter({
	// 	headers: { 
    //         0: { sorter: false }, 1: { sorter: false }, 2: { sorter: false }
    //     } 
	// }); 




});