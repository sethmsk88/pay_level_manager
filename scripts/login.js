$(document).ready(function() {
	/* Handle login submit action */
	$('#login-form').submit(function(e) {
		e.preventDefault();

		$('#login-submit-btn').hide();
		$('#loggingIn-btn').fadeIn(200);

		$form = $(this);

		formhash($form);

		/* Submit the login form */
		$.ajax({
			type: 'post',
			url: './content/act_process_login.php',
			data: $form.serialize(),
			success: function(response) {

				/* IF LOGIN WAS SUCCESSFUL */
				if (response == 1) {
					location.reload();
				}
				else {
					$('#loggingIn-btn').hide();
					$('#login-failure-btn').fadeIn().focus();
				}
				
				// Clear password inputs in form
				$form.children('input[type="password"]').each(function() {
					$(this).val('');
				});				
			}
		});
	});

	/*
		When a login message loses focus, hide it
		and show login-submit button.
	*/
	$('.login-msg').blur(function() {
		$(this).hide();
		$('#login-submit-btn').fadeIn();
	})
});

/*
	Hash the password before sending to server. This is
	necessary if there is no SSL.
*/
function formhash(form) {

	var hashedPassword = hex_sha512($('#password').val());

	// Check to see if input p exists
	// If it doesn't exist, create it,
	// otherwise, just assign hashedPassword as its value

	/* Append hidden input field to login form */
	$("<input>")
		.attr("type", "hidden")
		.attr("id", "p")
		.attr("name", "p")
		.attr("value", hashedPassword)
		.appendTo(form);

	// Clear the original password
	$('#password').val('');
}


function regformhash(form, uid, email, password, conf) {
	// Make sure each field has a value
	if (uid.value == '' ||
		email.value == '' ||
		password.value == '' ||
		conf.value == '') {

		alert('You must provide all the requested details. Please try again.');
		return false;
	}

	// Check the username
	re = /^\w+$/;
	if (!re.test(form.username.value)) {
		alert("Username must contain only letters, numbers, and underscores. Please try again.");
		form.username.focus();
		return false;
	}

	/*
		Check that the password is sufficiently long (min 6 chars)
		The check is duplicated below, but this is included to give
		more specific guidance to the user
	*/
	if (password.value.length < 6) {
		alert("Passwords must be at least 6 characters long. Please try again.");
		form.password.focus();
		return false;
	}

	/*
		At least one number, one lowercase and one uppercase letter.
		At least 6 characters
	*/
	var re = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}/;
	if (!re.test(password.value)) {
		alert('Passwords must contain at least one number, one lowercase and one uppercase letter. Please try again.');
		return false;
	}

	// Check password and confirmation are the same
	if (password.value != conf.value) {
		alert('Your password and confirmation do not match. Please try again.');
		form.password.focus();
		return false;
	}

	// Create a new element input, this will be our hashed password field
	var p = document.createElement("input");

	// Add new element to form
	form.appendChild(p);
	p.name = "p";
	p.type = "hidden";
	p.value = hex_sha512(password.value);

	// Make sure the plaintext password doesn't get sent
	password.value = "";
	conf.value = "";

	form.submit();
	return true;
}


