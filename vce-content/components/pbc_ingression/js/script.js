function onformsuccess(formsubmitted,data) {

	// console.log(formsubmitted);

	if (data.procedure == "choose_form" || data.procedure == "process_ingression_request" || data.procedure == "process_org_ingression_request" ) {

		$(formsubmitted).append('<div class="form-message form-success">' + data.message + '</div>');
		
	
		setTimeout(function(){
			window.location.href = data.url
		}, 2000);

	}



}

function onformerror(formsubmitted,data) {

	if (data.form == "process_ingression_request" || data.procedure == "process_org_ingression_request") {
		$(formsubmitted).append('<div class="form-message form-error">' + data.message + '</div>');
	}

	// setTimeout(function(){
	// 	window.location.href = data.url
	// }, 2000);

}



$(document).ready(function() {

	// $('form > input:hidden').attr("disabled",true);
		// if a new organization had previously been added, remove the organization and group fields
		if ($('input[name=ind_new_organization]', '#pbc_ingression_form').val() != '') {
			$('#organization').val(0);
			$('#group').val(0);

		}

	//a new, not-registered org, has been entered in org form
	$('.org-new-organization').change(function() {
		var new_name = $(this).val();
		// change corresponding field 
		$('.ind-new-organization').val(new_name);
		// we are entering a new, not-registered org, so we take away the existing org and group choice 
		$('#organization').val(0);
		$('#group').val(0);
   });

   //a new, not-registered org, has been entered in ind form
   $('.ind-new-organization').change(function() {
		var new_name = $(this).val();
		var org_found = false;
		//search for org
		$('#organization option').each(function( index ) {
			var orgname = $( this ).text();
			
			if (new_name.toLowerCase() == orgname.toLowerCase()) {
				console.log($( this ).val());
				console.log(orgname);
				$(this).prop("selected", true).change();
				$('.org-new-organization').val('');
				$('.ind-new-organization').val('');
				if (new_name == '') {
					$('.ind-new-organization').closest('.input-label-style').prepend('<div class="form-message form-success">Please select an organization above or enter a new organization here.</div>');
				} else {
					$('.ind-new-organization').closest('.input-label-style').prepend('<div class="form-message form-success">This organization already exists and has been entered above.</div>');
				}
				setTimeout(function(){
					$('.form-success').hide('slow')
				}, 4000);
				org_found = true;
			}
		});

		if (org_found != true) {
			// change corresponding field 
			$('.org-new-organization').val(new_name);
			// we are entering a new, not-registered org, so we take away the existing org and group choice 
			$('#organization').val(0);
			$('#group').val(0);
		}
	});










	   //a new, not-registered org, has been entered in ind form
	   $('.org-new-organization').change(function() {
		var new_name = $(this).val();
		var org_found = false;
		//search for org
		$('#organization option').each(function( index ) {
			var orgname = $( this ).text();
			if (new_name.toLowerCase() == orgname.toLowerCase()) {
				// console.log($( this ).val());
				// console.log(orgname);
				
				$('.org-new-organization').val('');
				$('.ind-new-organization').val('');
				$('.org-new-organization').closest('.input-label-style').prepend('<div class="form-message form-success">This organization already exists and has been entered above.</div>');
				$(this).prop("selected", true);
				setTimeout(function(){
					$('.form-success').hide('slow');
					$('#organization').change();
				}, 4000);
				
				org_found = true;
			}
		});

		if (org_found != true) {
			// change corresponding field 
			$('.org-new-organization').val(new_name);
			// we are entering a new, not-registered org, so we take away the existing org and group choice 
			$('#organization').val(0);
			$('#group').val(0);
		}
	});

	//sync org and ind cities 
	$('.ind-city').change(function() {
		var new_name = $(this).val();
		// change corresponding field 
		$('.org-city').val(new_name);
	});

	//sync org and ind cities 
	$('.org-city').change(function() {
		var new_name = $(this).val();
		// change corresponding field 
		$('.ind-city').val(new_name);
	});

	// 
	// console.log($('.ind-state').val());
	// if ($('.ind-state').val() == 0) {
	// 	// $('.new-org-registration-accordion').show();
	// 	$('.ind-state').val() = '';
	// }
	//sync org and ind states 
	$('.ind-state').change(function() {
		var new_name = $(this).val();
		// change corresponding field 
		$('.org-state').val(new_name);
		// console.log($('.ind-state').val());
	});

	//sync org and ind states 
	$('.org-state').change(function() {
		var new_name = $(this).val();
		// change corresponding field 
		$('.ind-state').val(new_name);
	});

	//sync org and ind regions
	$('.ind-region').change(function() {
		var new_name = $(this).val();
		// change corresponding field 
		$('.org-region').val(new_name);
	});

	//sync org and ind regions
	$('.org-region').change(function() {
		var new_name = $(this).val();
		// change corresponding field 
		$('.ind-region').val(new_name);
	});

		//check that email and email2 match
		$('.email2').change(function() {
			var email = $('.email').val();
			var email2 = $('.email2').val();
			if (email != email2) {
				$(this).val('');
				$(this).closest('.input-label-style').prepend('<div class="form-message form-success">This field needs to match the first email.</div>');
				setTimeout(function(){
					$('.form-success').hide('slow')
				}, 4000);
			}
		});

		$('.email2').closest('.input-label-style').hide();

		$('.email').change(function() {
			var email = $('.email').val();
			$('.email2').closest('.input-label-style').show('slow');
			$(this).closest('.input-label-style').prepend('<div class="form-message form-success">You cannot change the email of your current user, but your change request will be recorded.</div>');
			setTimeout(function(){
				$('.form-success').hide('slow')
			}, 7000);
			if (!isEmail(email)) {
				// $(this).val('');
				$(this).closest('.input-label-style').prepend('<div class="form-message form-success">This is not a valid email.</div>');
				setTimeout(function(){
					$('.form-success').hide('slow')
				}, 4000);
			}
		});




		function isEmail(email) {
			var regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
			return regex.test(email);
		  }

		//program-size
		$('.integer-input').change(function() {
			var integer_input = parseInt($(this).val());
			console.log(integer_input);
			if (!Number.isInteger(integer_input)) {
				$(this).val('');
				$(this).closest('.input-label-style').prepend('<div class="form-message form-success">This value must be a number.</div>');
				setTimeout(function(){
					$('.form-success').hide('slow')
				}, 4000);
			} else {
				$(this).val(integer_input);
			}
		});

	// open org form if org-registration-question is yes on load
	if ($('input[name=org_registration_question]:checked', '#pbc_ingression_form').val() == 1) {
		$('.new-org-registration-accordion').show();
	}

	// open org form if yes to registering new org
	$('.org-registration-question').on('click', function() {
		var org_registration_form = $('.new-org-registration-accordion');
		if($(this).val() == 1) {
			org_registration_form.show('slow');
		} else if ($(this).val() == 0) {
			org_registration_form.hide('slow');
		}
	});
	
	// if an existing org is chosen, then the user can't register a new one.
	$('#organization').change(function() {
		$('.org-new-organization').val('');
		$('.ind-new-organization').val('');
		// with radio buttons, can't set value to 0, have to find find the 0 value,
		//check it and click it to trigger the on('click') function
		$('.org-registration-question').each(function( index ) {
			if ($( this ).val() == 0) {
				$(this).prop("checked", true).click(); 
				// console.log( index + "radiob: " + $( this ).val() );
			}
		  });
	});




	// hide coachees in caseload if not the a coach
	if ($('input[name=coach_type]:checked', '#pbc_ingression_form').val() == 'I am a coach full time.' || $('input[name=coach_type]:checked', '#pbc_ingression_form').val() == 'I serve in multiple roles.') {
		// $('.coachee-number1').show();
	} else {
		$('.coachee-number1').hide();
	}

	//show  coachees in caseload if coach
	// hide question and org registration if false
	$('.coach-type-question').on('click', function() {
		var coachee_number_question1 = $('.coachee-number1');
		if($(this).val() == 'I am a coach full time.' || $(this).val() == 'I serve in multiple roles.') {
			coachee_number_question1.show('slow');
		} else if ($(this).val() == 'N/A (I am not a coach.)') {
			coachee_number_question1.hide('slow');
			$('.coachee-number').val('');
		}
	});



	// hide new coaching method field if option not selected
	if ($('input[name=coaching-model-question]:checked', '#pbc_ingression_form').val() == 'coaching_model_other') {
		// $('.coaching-model-new').show();
	} else {
		$('.other-coaching-model-field').hide();
	}

	//show  new coaching method field if option selected
	// hide new coaching method field if option not selected
	$('.coaching-model-question').on('click', function() {
		var other_coaching_model_field = $('.other-coaching-model-field');
		if($(this).val() == 'coaching_model_other') {
			other_coaching_model_field.show('slow');
		} else if ($(this).val() != 'coaching_model_other') {
			other_coaching_model_field.hide('slow');
			$('.coaching-model-new').val('');
		}
	});


		// hide org registration question if not the projected org admin
		if ($('input[name=admin_question]:checked', '#pbc_ingression_form').val() != 1) {
			$('.org-registration-question').closest('.input-label-style').hide();
		}

		// show org registration question if the projected org admin
		// hide question and org registration if false
		$('.admin-question').on('click', function() {
			var org_registration_question = $('.org-registration-question').closest('.input-label-style');
			if($(this).val() == 1) {
				org_registration_question.show('slow');
			} else if ($(this).val() == 0) {
				org_registration_question.hide('slow');
				$('.org-registration-question').prop('checked', false);
				$('.new-org-registration-accordion').hide('slow');
			}
		});

	// allow closest_role "other" text field only if "other" radio button is checked
	if ($('input[name=closest_role]:checked', '#pbc_ingression_form').val() != 'Other') {
		$('.new-closest-role').closest('.input-label-style').hide();
	}
	$('.closest-role').on('click', function() {
		var new_closest_role_question = $('.new-closest-role').closest('.input-label-style');
		if($(this).val() == 'Other') {
			new_closest_role_question.show('slow');
		} else if ($(this).val() != 'Other') {
			new_closest_role_question.hide('slow');
		}
	});


	// take out the option "New Users" organization from the dropdown if the user is in it
	// $("#organization option[value='1804']").remove();
	$('#organization option:contains(New Users)').remove();
	$('#group option:contains(New Users Default)').remove();

});
