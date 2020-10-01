<?php
namespace MBUP;

class DuplicatedFields {
	private $fields = [
		'user_login',
		'user_email',
		'user_pass',
		'user_pass2',
		'user_nicename',
		'user_url',
		'display_name',
	];

	public function __construct() {
		add_filter( 'rwmb_outer_html', [$this, 'remove_field'], 10, 2 );
	}

	public function remove_field( $html, $field ) {
		if ( ! is_admin() ) {
			return $html;
		}
		$screen = get_current_screen();
		if ( ! in_array( $screen->id, ['profile', 'user-edit', 'profile-network', 'user-edit-network'], true ) ) {
			return $html;
		}
		return in_array( $field['id'], $this->fields, true ) ? '' : $html;
	}
}
