<?php
/**
 * Conditional utilities
 *
 * @package ExactStudios\Installation
 * @author  Exact Studios <info@exactwp.com>
 * @since   1.0.0
 */

namespace ExactStudios\Framework\Utilities;

defined( 'ABSPATH' ) || exit;

/**
 * Conditional class.
 */
class Conditional {

	/**
	 * Is REST request
	 *
	 * @return bool
	 */
	public static function is_rest() {
		$request = Params::server( 'REQUEST_URI' );
		if ( empty( $request ) ) {
			return false;
		}

		return false !== strpos( $request, trailingslashit( rest_get_url_prefix() ) );
	}

	/**
	 * Checks if the current request is a WP CLI request.
	 *
	 * @return bool
	 */
	public static function is_wp_cli(): bool {
		return defined( 'WP_CLI' ) && WP_CLI;
	}

	/**
	 * Checks if the current request is an AJAX request.
	 * It can be a request to `admin-ajax.php` or to `ajax-handler.php`.
	 *
	 * @return bool
	 */
	public static function doing_ajax(): bool {
		return wp_doing_ajax() || 'XMLHttpRequest' === Params::server( 'HTTP_X_REQUESTED_WITH' );
	}

	/**
	 * Determines whether the current request is an autosave
	 *
	 * @return bool
	 */
	public static function doing_autosave(): bool {
		return defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
	}
}
