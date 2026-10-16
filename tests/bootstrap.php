<?php
/**
 * Stubs the WordPress functions these tests call.
 *
 * @package ExactStudios\Framework
 */

define( 'ABSPATH', __DIR__ . '/' );

$GLOBALS['exact_test_options']     = [];
$GLOBALS['exact_test_can_update']  = true;
$GLOBALS['exact_test_doing_cron']  = false;
$GLOBALS['exact_updates_ran']      = [];

/**
 * Option getter.
 *
 * @param string $name    Option name.
 * @param mixed  $default Default.
 * @return mixed
 */
function get_option( $name, $default = false ) {
	return $GLOBALS['exact_test_options'][ $name ] ?? $default;
}

/**
 * Option setter.
 *
 * @param string $name  Option name.
 * @param mixed  $value Value.
 * @return bool
 */
function update_option( $name, $value ) {
	$GLOBALS['exact_test_options'][ $name ] = $value;
	return true;
}

/**
 * Add an option only when it is missing.
 *
 * @param string $name      Option name.
 * @param mixed  $value     Value.
 * @param string $deprecated Unused.
 * @param bool   $autoload  Unused.
 * @return bool
 */
function add_option( $name, $value = '', $deprecated = '', $autoload = 'yes' ) {
	unset( $deprecated, $autoload );

	if ( array_key_exists( $name, $GLOBALS['exact_test_options'] ) ) {
		return false;
	}

	$GLOBALS['exact_test_options'][ $name ] = $value;
	return true;
}

/**
 * Option deleter.
 *
 * @param string $name Option name.
 * @return bool
 */
function delete_option( $name ) {
	unset( $GLOBALS['exact_test_options'][ $name ] );
	return true;
}

/**
 * Capability check.
 *
 * @param string $capability Capability.
 * @return bool
 */
function current_user_can( $capability ) {
	unset( $capability );
	return (bool) $GLOBALS['exact_test_can_update'];
}

/**
 * Cron request check.
 *
 * @return bool
 */
function wp_doing_cron() {
	return (bool) $GLOBALS['exact_test_doing_cron'];
}

/**
 * Text field sanitizer, enough of core for textarea tests.
 *
 * @param mixed $str Value.
 * @return string
 */
function sanitize_text_field( $str ) {
	$str = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', (string) $str );
	$str = strip_tags( (string) $str );
	$str = preg_replace( '/[\r\n\t ]+/', ' ', $str );

	return trim( $str );
}

require dirname( __DIR__ ) . '/src/class-updates.php';
require dirname( __DIR__ ) . '/src/utilities/class-sanitize.php';
