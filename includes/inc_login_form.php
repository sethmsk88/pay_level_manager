<style type="text/css">
	.loginForm {
		border-radius: 4px;
		padding: 10px 10px 8px 10px;
		background-color: #EFEFEF;
		line-height: 3em;
		display: block;
		z-index:1001;
		box-shadow: 0px 0px 3px #888888;
		width: 225px;
	}

	#login-container input[type=text],
	#login-container input[type=password]{
		margin-bottom: 10px;
	}

	#login-container input[type=submit],
	#login-container button{
		width: 100%;
	}

	.login-msg:focus{
		outline: none;
	}
</style>

<!-- Login Form (absolutely positioned) -->
<div
	id="login-container"
	class="loginForm">

	<form
		name="login-form"
		id="login-form"
		role="form"
		method="post"
		action="">

		<input
			type="text"
			name="email"
			id="email"
			class="form-control"
			placeholder="FAMU Email">

		<input
			type="password"
			name="password"
			id="password"
			class="form-control"
			placeholder="Password">

		<input
			type="submit"
			id="login-submit-btn"
			class="btn btn-md btn-primary"
			value="Login">

		<button
			id="loggingIn-btn"
			class="btn btn-md btn-primary login-msg"
			style="display:none;">
			Logging in...
		</button>

		<button
			id="login-failure-btn"
			class="btn btn-md btn-danger login-msg"
			style="display:none;">
			Login Failure
		</button>
	</form>
</div>
