( function( $, document, passwordStrength, i18n ) {
	var types = ['very-weak', 'very-weak', 'weak', 'medium', 'strong', 'mismatch'],
		requiredStrength = types.indexOf( i18n.strength ),
		$document = $( document ),
		$result,
		$submitButton;

	function checkPasswordStrength( password, password2 ) {
		if ( '' === password ) {
			$result.hide();
			return;
		}

		// Reset the form & meter.
		$submitButton.prop( 'disabled', true );
		$result.removeClass( 'very-weak weak medium strong mismatch' ).show();

		// Get the password strength.
		var strength = passwordStrength.meter( password, passwordStrength.userInputBlacklist(), password2 );
		if ( 0 > strength || 5 < strength ) {
			return;
		}
		var type = types[strength];

		$result.addClass( type ).html( i18n[type] );
		if ( requiredStrength <= strength && 5 !== strength ) {
			$submitButton.prop( 'disabled', false );
		}
	}

	function triggerCheck() {
		$result = $( '#password-strength' );
		$submitButton = $( '[name="rwmb_profile_submit_register"], [name="rwmb_profile_submit_info"]' );

		var $user_pass = $( '#user_pass' ),
			$user_pass2 = $( '#user_pass2' );

		$document.on( 'keyup', '#user_pass, #user_pass2', function() {
			checkPasswordStrength( $user_pass.val(), $user_pass2.val() );
		} );
	}

	$document.on( 'ready', triggerCheck );
} )( jQuery, document, wp.passwordStrength, MBUP_Password );