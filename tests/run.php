<?php
/**
 * Runs the framework unit tests.
 *
 * @package ExactStudios\Framework
 */

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/updates-fixture.php';
require __DIR__ . '/assets-fixture.php';
require __DIR__ . '/install-fixture.php';

use ExactStudios\Framework\Utilities\Params;

if ( '1' === getenv( 'EXACT_TEST_REQUEST_ORDER' ) ) {
	set_error_handler(
		static function () {
			exit( 1 );
		}
	);
	$value = Params::request( 'not_sent' );
	exit( false === $value ? 0 : 1 );
}

use ExactStudios\Framework\Utilities\Sanitize;
use ExactStudios\Updates\Fixture_Updates;

$failures = 0;

/**
 * Fail the run when two values differ.
 *
 * @param mixed  $expected Expected.
 * @param mixed  $actual   Actual.
 * @param string $message  Label.
 * @return void
 */
function expect_same( $expected, $actual, $message ) {
	global $failures;

	if ( $expected === $actual ) {
		echo "ok  {$message}\n";
		return;
	}

	++$failures;
	fwrite( STDERR, "not {$message}\nexpected: " . var_export( $expected, true ) . "\nactual:   " . var_export( $actual, true ) . "\n" );
}

/**
 * Reset stub state.
 *
 * @return void
 */
function reset_state() {
	$GLOBALS['exact_test_options']    = [];
	$GLOBALS['exact_test_can_update'] = true;
	$GLOBALS['exact_test_doing_cron'] = false;
	$GLOBALS['exact_updates_ran']     = [];
}

/**
 * Build an updater whose files live in a temp directory.
 *
 * @param array<string, bool> $files Version => whether the file exists.
 * @param string              $plugin_version Plugin version.
 * @return Fixture_Updates
 */
function make_updater( array $files, $plugin_version = '2.0.0' ) {
	$dir = sys_get_temp_dir() . '/exact-updates-' . uniqid( '', true ) . '/';
	mkdir( $dir );

	$updates = [];
	foreach ( $files as $version => $exists ) {
		$name = 'update-' . $version . '.php';
		if ( $exists ) {
			file_put_contents(
				$dir . $name,
				'<?php $GLOBALS["exact_updates_ran"][] = ' . var_export( (string) $version, true ) . ';'
			);
		}
		$updates[ $version ] = $name;
	}

	$updater          = new Fixture_Updates();
	$updater->folder  = $dir;
	$updater->updates = $updates;
	$updater->version = $plugin_version;

	return $updater;
}

if ( '1' === getenv( 'EXACT_TEST_WP_CLI' ) ) {
	define( 'WP_CLI', true );
	reset_state();
	$GLOBALS['exact_test_can_update']             = false;
	$GLOBALS['exact_test_options']['plugin_version'] = '1.0.0';

	$updater = make_updater( [ '1.0.1' => true ], '1.0.1' );
	$updater->do_updates();

	$ok = [ '1.0.1' ] === $GLOBALS['exact_updates_ran']
		&& '1.0.1' === get_option( 'plugin_version' );

	exit( $ok ? 0 : 1 );
}

reset_state();
$GLOBALS['exact_test_options']['plugin_version'] = '1.0.0';
$updater = make_updater(
	[
		'1.0.2' => true,
		'1.0.1' => true,
		'1.0.3' => false,
	]
);
$updater->do_updates();
expect_same( [ '1.0.1', '1.0.2' ], $GLOBALS['exact_updates_ran'], 'runs existing files in version order and stops at a missing file' );
expect_same( '1.0.2', get_option( 'plugin_version' ), 'mid-list failure leaves the last version that ran' );
expect_same( false, array_key_exists( 'plugin_version_lock', $GLOBALS['exact_test_options'] ), 'missing file releases the lock' );

reset_state();
$GLOBALS['exact_test_options']['plugin_version'] = '1.0.0';
$updater = make_updater(
	[
		'1.0.2' => true,
		'1.0.1' => true,
	],
	'1.0.2'
);
$updater->do_updates();
expect_same( [ '1.0.1', '1.0.2' ], $GLOBALS['exact_updates_ran'], 'sorts update files before running them' );
expect_same( '1.0.2', get_option( 'plugin_version' ), 'successful run saves the plugin version' );

reset_state();
$GLOBALS['exact_test_options']['plugin_version']      = '1.0.0';
$GLOBALS['exact_test_options']['plugin_version_lock'] = time();
$updater = make_updater( [ '1.0.1' => true ], '1.0.1' );
$updater->do_updates();
expect_same( [], $GLOBALS['exact_updates_ran'], 'held lock skips the migration' );
expect_same( '1.0.0', get_option( 'plugin_version' ), 'held lock leaves the installed version' );

reset_state();
$updater = make_updater( [ '1.0.1' => true ], '1.0.1' );
$updater->do_updates();
expect_same( [], $GLOBALS['exact_updates_ran'], 'first install does not run update files' );
expect_same( '1.0.1', get_option( 'plugin_version' ), 'first install saves the plugin version' );

reset_state();
$GLOBALS['exact_test_can_update']                = false;
$GLOBALS['exact_test_doing_cron']                = true;
$GLOBALS['exact_test_options']['plugin_version'] = '1.0.0';
$updater = make_updater( [ '1.0.1' => true ], '1.0.1' );
$updater->do_updates();
expect_same( [ '1.0.1' ], $GLOBALS['exact_updates_ran'], 'cron runs updates without update_plugins' );

reset_state();
$GLOBALS['exact_test_can_update']                = false;
$GLOBALS['exact_test_options']['plugin_version'] = '1.0.0';
$updater = make_updater( [ '1.0.1' => true ], '1.0.1' );
$updater->do_updates();
expect_same( [], $GLOBALS['exact_updates_ran'], 'a request without cap, cron, or CLI does not migrate' );
expect_same( '1.0.0', get_option( 'plugin_version' ), 'a blocked request leaves the installed version' );

reset_state();
$clean = Sanitize::sanitize_textarea( "  hello <b>there</b>  \n<script>alert(1)</script>world" );
expect_same( "hello there\nworld", $clean, 'textarea clean keeps line breaks and sanitizes each line' );

$cli = 'EXACT_TEST_WP_CLI=1 ' . PHP_BINARY . ' ' . escapeshellarg( __FILE__ );
passthru( $cli, $cli_status );
expect_same( 0, $cli_status, 'WP-CLI runs updates without update_plugins' );

require __DIR__ . '/later.php';

$order = 'EXACT_TEST_REQUEST_ORDER=1 ' . PHP_BINARY . ' -d request_order=ES ' . escapeshellarg( __FILE__ );
passthru( $order, $order_status );
expect_same( 0, $order_status, 'request() ignores request_order letters other than G, P, and C' );

require __DIR__ . '/batch-11.php';

exit( $failures > 0 ? 1 : 0 );
