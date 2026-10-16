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

/**
 * Attribute escaper.
 *
 * @param mixed $text Value.
 * @return string
 */
function esc_attr( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}

/**
 * HTML escaper.
 *
 * @param mixed $text Value.
 * @return string
 */
function esc_html( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}

/**
 * JSON encoder with the WordPress hex flags.
 *
 * @param mixed $data    Data.
 * @param int   $options Options.
 * @param int   $depth   Depth.
 * @return string|false
 */
function wp_json_encode( $data, $options = 0, $depth = 512 ) {
	return json_encode(
		$data,
		$options | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
		$depth
	);
}

/**
 * Inline script printer.
 *
 * @param string $data       Script body.
 * @param array  $attributes Unused.
 * @return void
 */
function wp_print_inline_script_tag( $data, $attributes = [] ) {
	unset( $attributes );
	echo '<script>' . $data . '</script>';
}

/**
 * Normalize a path.
 *
 * @param string $path Path.
 * @return string
 */
function wp_normalize_path( $path ) {
	return str_replace( '\\', '/', (string) $path );
}

/**
 * Record a script registration.
 *
 * @param mixed ...$args Arguments.
 * @return bool
 */
function wp_register_script( ...$args ) {
	$GLOBALS['exact_wp_calls'][] = [ 'wp_register_script', $args ];
	return true;
}

/**
 * Record a style registration.
 *
 * @param mixed ...$args Arguments.
 * @return bool
 */
function wp_register_style( ...$args ) {
	$GLOBALS['exact_wp_calls'][] = [ 'wp_register_style', $args ];
	return true;
}

/**
 * REST prefix.
 *
 * @return string
 */
function rest_get_url_prefix() {
	return 'wp-json';
}

/**
 * Add a trailing slash.
 *
 * @param string $value Value.
 * @return string
 */
function trailingslashit( $value ) {
	return rtrim( (string) $value, '/\\' ) . '/';
}

/**
 * AJAX request check.
 *
 * @return bool
 */
function wp_doing_ajax() {
	return ! empty( $GLOBALS['exact_test_doing_ajax'] );
}

/**
 * Key sanitizer.
 *
 * @param mixed $key Key.
 * @return string
 */
function sanitize_key( $key ) {
	$key = strtolower( (string) $key );
	return preg_replace( '/[^a-z0-9_\-]/', '', $key );
}

/**
 * Record an action callback.
 *
 * @param string $hook     Hook.
 * @param mixed  $callback Callback.
 * @return void
 */
function add_action( $hook, $callback ) {
	$GLOBALS['exact_actions'][ $hook ][] = $callback;
}

/**
 * Record an activation hook.
 *
 * @param string $file     Plugin file.
 * @param mixed  $callback Callback.
 * @return void
 */
function register_activation_hook( $file, $callback ) {
	$GLOBALS['exact_hooks']['activation'][] = [ $file, $callback ];
}

/**
 * Record a deactivation hook.
 *
 * @param string $file     Plugin file.
 * @param mixed  $callback Callback.
 * @return void
 */
function register_deactivation_hook( $file, $callback ) {
	$GLOBALS['exact_hooks']['deactivation'][] = [ $file, $callback ];
}

/**
 * Record an uninstall hook.
 *
 * @param string $file     Plugin file.
 * @param mixed  $callback Callback.
 * @return void
 */
function register_uninstall_hook( $file, $callback ) {
	$GLOBALS['exact_hooks']['uninstall'][] = [ $file, $callback ];
}

/**
 * Plugin basename.
 *
 * @param string $file File.
 * @return string
 */
function plugin_basename( $file ) {
	return basename( (string) $file );
}

/**
 * Network-active check.
 *
 * @param string $plugin Plugin basename.
 * @return bool
 */
function is_plugin_active_for_network( $plugin ) {
	return ! empty( $GLOBALS['exact_network_plugins'][ $plugin ] );
}

/**
 * Record the arguments used to list network sites.
 *
 * @param array $args Query arguments.
 * @return array<int, int>
 */
function get_sites( $args = [] ) {
	$GLOBALS['exact_get_sites_args'] = $args;

	return [ 1, 2 ];
}

/**
 * Record a blog switch.
 *
 * @param int $blog_id Blog id.
 * @return void
 */
function switch_to_blog( $blog_id ) {
	$GLOBALS['exact_switched'][] = $blog_id;
}

/**
 * Record restoring the current blog.
 *
 * @return void
 */
function restore_current_blog() {
	$GLOBALS['exact_restored'] = true;
}

/**
 * Record a doing-it-wrong notice.
 *
 * @param string $function Function.
 * @param string $message  Message.
 * @param string $version  Version.
 * @return void
 */
function _doing_it_wrong( $function, $message, $version ) {
	$GLOBALS['exact_wrong'][] = [ $function, $message, $version ];
}

$GLOBALS['exact_actions']         = [];
$GLOBALS['exact_hooks']           = [];
$GLOBALS['exact_network_plugins'] = [];
$GLOBALS['exact_switched']        = [];
$GLOBALS['exact_restored']        = false;
$GLOBALS['exact_wrong']           = [];

$GLOBALS['exact_wp_calls']        = [];
$GLOBALS['exact_test_doing_ajax'] = false;

require dirname( __DIR__ ) . '/src/class-updates.php';
require dirname( __DIR__ ) . '/src/utilities/class-sanitize.php';
require dirname( __DIR__ ) . '/src/utilities/class-str.php';
require dirname( __DIR__ ) . '/src/interfaces/interface-integration.php';
require dirname( __DIR__ ) . '/src/class-assets-registry.php';
require dirname( __DIR__ ) . '/src/utilities/class-arr.php';
require dirname( __DIR__ ) . '/src/class-json.php';
require dirname( __DIR__ ) . '/src/utilities/class-params.php';
require dirname( __DIR__ ) . '/src/utilities/class-conditional.php';
require dirname( __DIR__ ) . '/src/utilities/class-html.php';
require dirname( __DIR__ ) . '/src/utilities/class-wordpress.php';
require dirname( __DIR__ ) . '/src/interfaces/interface-initializer.php';
require dirname( __DIR__ ) . '/src/installation/class-install.php';
require dirname( __DIR__ ) . '/src/class-loader.php';
