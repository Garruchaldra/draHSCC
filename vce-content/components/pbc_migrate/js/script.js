function onformerror(formsubmitted,data) {

	if (data.form == "create") {
		$(formsubmitted).prepend('<div class="form-message form-error">' + data.message + '</div>');

		var str = data.message;

			if (str.includes("Too many API requests"))  {
					$(formsubmitted).prepend('<label id="minutes">00</label>:<label id="seconds">00</label>');

			        var minutesLabel = document.getElementById("minutes");
					var secondsLabel = document.getElementById("seconds");
					var totalSeconds = 0;
					setInterval(setTime, 1000);

					function setTime()
					{
						++totalSeconds;
						if (totalSeconds == 1200) {
								totalSeconds = 0;
								reloadPage();

						}
						secondsLabel.innerHTML = pad(totalSeconds%60);
						minutesLabel.innerHTML = pad(parseInt(totalSeconds/60));
					}

					function pad(val)
					{
						var valString = val + "";
						if(valString.length < 2)
						{
							return "0" + valString;
						}
						else
						{
							return valString;
						}
					}
			}
	}
	
function reloadPage() 
		{
// 			setTimeout(function(){

			location.reload();
			
// 		}, 2000);

   		 }
    
    
    
    
    

	if (data.form == "masquerade") {
		$(formsubmitted).prepend('<div class="form-message form-error">' + data.message + '</div>');
	}

	if (data.form == "delete") {
		$(formsubmitted).parent().prepend('<div class="form-message form-error">' + data.message + '</div>');
	}

}



function selectElementContents(el) {
	var body = document.body, range, sel;
	if (document.createRange && window.getSelection) {
		range = document.createRange();
		sel = window.getSelection();
		sel.removeAllRanges();
		try {
			range.selectNodeContents(el);
			sel.addRange(range);
		} catch (e) {
			range.selectNode(el);
			sel.addRange(range);
		}
	} else if (body.createTextRange) {
		range = body.createTextRange();
		range.moveToElementText(el);
		range.select();
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
		}, 1000);

	}
	
	if (data.form == "show_data") {

		$(formsubmitted).prepend('<div id="show-data" class="form-message form-success">' + data.message + '</div>');
		selectElementContents( document.getElementById('show-data') )
	
		setTimeout(function(){
	   		window.location.reload(1);
		}, 200);

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