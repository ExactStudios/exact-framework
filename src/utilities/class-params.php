<?php
/**
 * Params class
 *
 * Easy access to inputs from
 *    INPUT_COOKIE
 *    INPUT_GET
 *    INPUT_POST
 *    INPUT_REQUEST
 *    INPUT_ENV
 *    INPUT_SERVER
 *
 * @package ExactStudios\Framework\Utilities
 * @author  Exact Studios <info@exactwp.com>
 * @since   1.0.0
 */

namespace ExactStudios\Framework\Utilities;

defined( 'ABSPATH' ) || exit;

/**
 * Params class
 */
class Params {

	/**
	 * Get field from input.
	 *
	 * @param string $input   Input to get from.
	 * @param string $id      Field id to get.
	 * @param mixed  $default Default value to return if field is not found.
	 * @param int    $filter  The ID of the filter to apply.
	 * @param int    $flag    The ID of the flag to apply.
	 *
	 * @return mixed
	 */
	public static function input( $input, $id, $default = false, $filter = FILTER_DEFAULT, $flag = [] ) {
		return filter_has_var( $input, $id ) ? filter_input( $input, $id, $filter, $flag ) : $default;
	}

	/**
	 * Get field from input as array.
	 *
	 * @param string $input   Input to get from.
	 * @param string $id      Field id to get.
	 * @param mixed  $default Default value to return if field is not found.
	 * @param int    $filter  The ID of the filter to apply.
	 * @param int    $flag    The ID of the flag to apply.
	 *
	 * @return mixed
	 */
	public static function input_array( $input, $id, $default = false, $filter = FILTER_DEFAULT, $flag = null ) {
		return filter_has_var( $input, $id ) ? filter_input( $input, $id, $filter, $flag ?? FILTER_REQUIRE_ARRAY ) : $default;
	}

	/**
	 * Get field from query string.
	 *
	 * @param string $id      Field id to get.
	 * @param mixed  $default Default value to return if field is not found.
	 * @param int    $filter  The ID of the filter to apply.
	 * @param int    $flag    The ID of the flag to apply.
	 *
	 * @return mixed
	 */
	public static function get( $id, $default = false, $filter = FILTER_DEFAULT, $flag = [] ) {
		return self::input( INPUT_GET, $id, $default, $filter, $flag );
	}

	/**
	 * Get field from FORM post.
	 *
	 * @param string $id      Field id to get.
	 * @param mixed  $default Default value to return if field is not found.
	 * @param int    $filter  The ID of the filter to apply.
	 * @param int    $flag    The ID of the flag to apply.
	 *
	 * @return mixed
	 */
	public static function post( $id, $default = false, $filter = FILTER_DEFAULT, $flag = [] ) {
		return self::input( INPUT_POST, $id, $default, $filter, $flag );
	}

	/**
	 * Get field from FORM server.
	 *
	 * @param string $id      Field id to get.
	 * @param mixed  $default Default value to return if field is not found.
	 * @param int    $filter  The ID of the filter to apply.
	 * @param int    $flag    The ID of the flag to apply.
	 *
	 * @return mixed
	 */
	public static function server( $id, $default = false, $filter = FILTER_DEFAULT, $flag = [] ) {
		return self::read_superglobal( $_SERVER, $id, $default, $filter, $flag );
	}

	/**
	 * Get field from FORM cookie.
	 *
	 * @param string $id      Field id to get.
	 * @param mixed  $default Default value to return if field is not found.
	 * @param int    $filter  The ID of the filter to apply.
	 * @param int    $flag    The ID of the flag to apply.
	 *
	 * @return mixed
	 */
	public static function cookie( $id, $default = false, $filter = FILTER_DEFAULT, $flag = [] ) {
		return self::input( INPUT_COOKIE, $id, $default, $filter, $flag );
	}

	/**
	 * Get field from FORM env.
	 *
	 * @param string $id      Field id to get.
	 * @param mixed  $default Default value to return if field is not found.
	 * @param int    $filter  The ID of the filter to apply.
	 * @param int    $flag    The ID of the flag to apply.
	 *
	 * @return mixed
	 */
	public static function env( $id, $default = false, $filter = FILTER_DEFAULT, $flag = [] ) {
		return self::read_superglobal( $_ENV, $id, $default, $filter, $flag );
	}

	/**
	 * Get field from request.
	 *
	 * @param string $id      Field id to get.
	 * @param mixed  $default Default value to return if field is not found.
	 * @param int    $filter  The ID of the filter to apply.
	 * @param int    $flag    The ID of the flag to apply.
	 *
	 * @return mixed
	 */
	public static function request( $id, $default = false, $filter = FILTER_DEFAULT, $flag = [] ) {
		$request_filters = [
			'G' => INPUT_GET,
			'P' => INPUT_POST,
			'C' => INPUT_COOKIE,
		];

		// This directive describes the order in which PHP registers GET, POST and Cookie variables into the _REQUEST array. Registration is done from left to right, newer values override older values.
		$request_order = ini_get( 'request_order' ) ? ini_get( 'request_order' ) : 'GP';
		$request_order = str_split( $request_order );
		$request_order = array_reverse( $request_order );

		foreach ( $request_order as $letter ) {
			if ( ! isset( $request_filters[ $letter ] ) ) {
				continue;
			}

			if ( filter_has_var( $request_filters[ $letter ], $id ) ) {
				return filter_input( $request_filters[ $letter ], $id, $filter, $flag );
			}
		}

		return $default;
	}

	/**
	 * Read one superglobal value and sanitize it.
	 *
	 * @param array  $source  Superglobal.
	 * @param string $id      Field id.
	 * @param mixed  $default Default when missing.
	 * @param int    $filter  Filter id.
	 * @param mixed  $flag    Filter flag.
	 *
	 * @return mixed
	 */
	private static function read_superglobal( array $source, $id, $default, $filter, $flag ) {
		if ( ! array_key_exists( $id, $source ) ) {
			return $default;
		}

		$value = $source[ $id ];
		if ( ! is_scalar( $value ) ) {
			return $default;
		}

		if ( FILTER_DEFAULT === $filter ) {
			return sanitize_text_field( (string) $value );
		}

		return filter_var( $value, $filter, $flag ? $flag : 0 );
	}
}
