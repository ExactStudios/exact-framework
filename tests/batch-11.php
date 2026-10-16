<?php
/**
 * Tests for tasks 011 through 015.
 *
 * @package ExactStudios\Framework
 */

use ExactStudios\Framework\Fixture_Assets;
use ExactStudios\Framework\Installation\Fixture_Install;
use ExactStudios\Framework\Loader;
use ExactStudios\Framework\Utilities\Sanitize;
use ExactStudios\Framework\Utilities\WordPress;
use ExactStudios\Updates\Fixture_Updates;

$root = sys_get_temp_dir() . '/exact-confine-' . uniqid( '', true );
mkdir( $root );
mkdir( $root . '/updates' );
file_put_contents(
	$root . '/updates/ok.php',
	'<?php $GLOBALS["exact_updates_ran"][] = "1.0.1";'
);
file_put_contents(
	$root . '/escape.php',
	'<?php $GLOBALS["exact_updates_ran"][] = "escaped";'
);

$GLOBALS['exact_updates_ran']                    = [];
$GLOBALS['exact_test_options']['plugin_version'] = '1.0.0';
$updater          = new Fixture_Updates();
$updater->folder  = $root . '/updates/';
$updater->version = '2.0.0';
$updater->updates = [
	'1.0.1' => 'ok.php',
	'1.0.2' => '../escape.php',
];
$updater->do_updates();
expect_same( [ '1.0.1' ], $GLOBALS['exact_updates_ran'], 'an update path outside the folder is not included' );
expect_same( '1.0.1', get_option( 'plugin_version' ), 'an escaping update path does not mark the plugin current' );

$asset_root = sys_get_temp_dir() . '/exact-asset-confine-' . uniqid( '', true );
mkdir( $asset_root );
mkdir( $asset_root . '/plugin' );
file_put_contents(
	$asset_root . '/evil.asset.php',
	'<?php $GLOBALS["exact_evil"] = true; return array( "dependencies" => array( "evil-dep" ), "version" => "nope" );'
);
$GLOBALS['exact_evil']     = false;
$GLOBALS['exact_wp_calls'] = [];
$assets                    = new Fixture_Assets();
$assets->base_path         = $asset_root . '/plugin/';
$assets->register_script( 'editor', '../evil.js', [ 'jquery' ], null, [ '@asset' => true ] );
expect_same( false, $GLOBALS['exact_evil'], 'an asset php file outside the plugin is not included' );
expect_same( [ 'jquery' ], $GLOBALS['exact_wp_calls'][0][1][2], 'an escaping asset path adds no dependencies' );

try {
	expect_same( true, Sanitize::string_to_bool( 1 ), 'string_to_bool accepts int 1' );
} catch ( Throwable $e ) {
	expect_same( 'no throw', get_class( $e ), 'string_to_bool accepts int 1' );
}
expect_same( true, Sanitize::string_to_bool( 'yes' ), 'string_to_bool accepts yes' );
expect_same( true, Sanitize::string_to_bool( 'TRUE' ), 'string_to_bool accepts true' );
expect_same( true, Sanitize::string_to_bool( 'on' ), 'string_to_bool accepts on' );
expect_same( true, Sanitize::string_to_bool( '1' ), 'string_to_bool accepts string 1' );
expect_same( false, Sanitize::string_to_bool( 'no' ), 'string_to_bool rejects no' );
expect_same( true, Sanitize::string_to_bool( true ), 'string_to_bool keeps true' );

try {
	expect_same( '2', Sanitize::float_to_string( 2 ), 'float_to_string casts a non-float' );
} catch ( Throwable $e ) {
	expect_same( 'no throw', get_class( $e ), 'float_to_string casts a non-float' );
}
expect_same( '1.5', Sanitize::float_to_string( 1.5 ), 'float_to_string keeps a decimal point' );

expect_same( false, WordPress::get_action(), 'a missing action is false' );

$GLOBALS['exact_hooks'] = [];
$install                = new Fixture_Install();
expect_same( '/plugins/example/example.php', $GLOBALS['exact_hooks']['activation'][0][0], 'activation hook is registered when the install object is created' );
expect_same( '/plugins/example/example.php', $GLOBALS['exact_hooks']['deactivation'][0][0], 'deactivation hook is registered when the install object is created' );
expect_same( [ Fixture_Install::class, 'uninstall' ], $GLOBALS['exact_hooks']['uninstall'][0][1], 'uninstall hook is a static callback' );

$GLOBALS['exact_network_plugins'] = [];
$GLOBALS['exact_switched']        = [];
$install->initialize_site( (object) [ 'blog_id' => 4 ] );
expect_same( [], $install->calls, 'a new site is not activated unless the plugin is network-active' );

$GLOBALS['exact_network_plugins']['example.php'] = true;
$install->initialize_site( (object) [ 'blog_id' => 4 ] );
expect_same( [ 'activate' ], $install->calls, 'a new site is activated for a network-active plugin' );
expect_same( [ 4 ], $GLOBALS['exact_switched'], 'network site activation switches to the new blog' );

$loader = new Loader();
set_error_handler(
	static function ( $errno, $errstr ) {
		$GLOBALS['exact_notice'] = $errstr;
		return true;
	}
);
$missing_line             = __LINE__ + 1;
$missing                  = $loader->missing_container;
restore_error_handler();
expect_same( null, $missing, 'an unknown container is null' );
expect_same( true, false !== strpos( $GLOBALS['exact_notice'], 'batch-11.php' ), 'undefined container notice points at the caller file' );
expect_same( true, false !== strpos( $GLOBALS['exact_notice'], 'on line ' . $missing_line ), 'undefined container notice points at the caller line' );

$GLOBALS['exact_wrong'] = [];
$loader->register_integration( 'ExactStudios\\Framework\\Missing_Class', 'missing' );
$loader->load_integrations();
expect_same( true, false !== strpos( $GLOBALS['exact_wrong'][0][1] ?? '', 'Missing_Class' ), 'a missing class is reported' );
expect_same( false, method_exists( Loader::class, 'define' ), 'Loader::define() is removed' );
expect_same( false, false !== strpos( (string) file_get_contents( dirname( __DIR__ ) . '/src/utilities/class-wordpress.php' ), 'DateTimeZone' ), 'DateTimeZone import is removed' );
