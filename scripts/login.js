$(document).ready(function() {
	$('#login-form').submit(function() {
		var pass = $('#LoginForm_password').val();
		var key = $('#LoginForm_key').val();
		$('#LoginForm_key').remove();
		$('#LoginForm_password_enc').val( hex_md5( hex_md5(pass) + key) );
		$('#LoginForm_password').val( new Array( $('#LoginForm_password').val().length + 1).join('0') );
		return true;
	});
	$("#LoginForm_password").keypress(function(event) {
		event = event || window.event;
		key = event.charCode || event.keyCode;
		var ch = String.fromCharCode(key);
		if ((/^[A-Z]$/.test(ch) && !event.shiftKey) || (/^[a-z]$/.test(ch) && event.shiftKey)) {
			$('#caps').show();
		} else {
			$('#caps').hide();
		}
	});
});