<?php
/**
 * Tests for tasks 006 through 010.
 *
 * @package ExactStudios\Framework
 */

use ExactStudios\Framework\Assets_Registry;
use ExactStudios\Framework\Fixture_Assets;
use ExactStudios\Framework\Utilities\Arr;
use ExactStudios\Framework\Utilities\Conditional;
use ExactStudios\Framework\Utilities\HTML;
use ExactStudios\Framework\Utilities\Params;
use ExactStudios\Framework\JSON;

try {
	$method = new ReflectionMethod( Assets_Registry::class, 'get_base_path' );
	expect_same( true, $method->isAbstract(), 'get_base_path is abstract' );
} catch ( ReflectionException $e ) {
	expect_same( 'declared', $e->getMessage(), 'get_base_path is abstract' );
}

$dir = sys_get_temp_dir() . '/exact-assets-' . uniqid( '', true ) . '/';
mkdir( $dir );
file_put_contents(
	$dir . 'app.asset.php',
	"<?php return array( 'dependencies' => array( 'wp-element' ), 'version' => 'abc123' );"
);

$GLOBALS['exact_wp_calls'] = [];
$assets                    = new Fixture_Assets();
$assets->base_path         = $dir;
$assets->register_script( 'editor', 'app.js', [ 'jquery' ], null, [ '@asset' => true, 'in_footer' => true ] );
expect_same(
	[
		'wp_register_script',
		[
			'ex-editor',
			'https://example.com/plugin/app.js',
			[ 'jquery', 'wp-element' ],
			'abc123',
			[ 'in_footer' => true ],
		],
	],
	$GLOBALS['exact_wp_calls'][0],
	'register_script loads .asset.php dependencies and version'
);

$GLOBALS['exact_wp_calls'] = [];
try {
	$assets->register_script( 'bare' );
	$assets->register_style( 'bare' );
	expect_same( '', $GLOBALS['exact_wp_calls'][0][1][1], 'missing script src is an empty string' );
	expect_same( '', $GLOBALS['exact_wp_calls'][1][1][1], 'missing style src is an empty string' );
} catch ( Throwable $e ) {
	expect_same( 'no throw', get_class( $e ) . ': ' . $e->getMessage(), 'missing src does not throw' );
}

$GLOBALS['exact_wp_calls'] = [];
$assets->register_script( 'editor', 'app.js', [ '_pre_foo_pre_bar', 'jquery' ], '1' );
expect_same(
	[ 'ex-foo_pre_bar', 'jquery' ],
	$GLOBALS['exact_wp_calls'][0][1][2],
	'only a leading _pre_ dependency marker is prefixed'
);

$dotted = [ 'keep' => 1 ];
expect_same(
	[
		'keep' => 1,
		'a'    => [ 'b' => 2 ],
	],
	Arr::set( $dotted, 'a.b', 2 ),
	'Arr::set returns the root array for a dotted key'
);
expect_same(
	[
		'keep' => 1,
		'a'    => [ 'b' => 2 ],
	],
	$dotted,
	'Arr::set still updates the passed array'
);

$added = [ 'keep' => 1 ];
expect_same(
	[
		'keep' => 1,
		'a'    => [ 'b' => 2 ],
	],
	Arr::add( $added, 'a.b', 2 ),
	'Arr::add sets a missing dotted key'
);

$json = new JSON( 'exactData' );
$json->add( 'label', '&lt;b&gt;' );
ob_start();
$json->output();
$printed = ob_get_clean();
expect_same( true, false !== strpos( $printed, '<script>' ), 'JSON output uses wp_print_inline_script_tag' );
expect_same( false, false !== strpos( $printed, 'CDATA' ), 'JSON output drops the hand-rolled script wrapper' );
expect_same( true, false !== strpos( $printed, '\u0026lt;b\u0026gt;' ), 'JSON output does not decode HTML entities' );

$evil = new JSON( 'exactData' );
$evil->add( 'k', 'v', 'x;alert(1)//' );
ob_start();
$evil->output();
$evil_printed = ob_get_clean();
expect_same( '', $evil_printed, 'a non-identifier object name is not printed' );

$_SERVER['REQUEST_URI'] = '/wp-json/<script>x</script>posts';
expect_same( '/wp-json/posts', Params::server( 'REQUEST_URI' ), 'server reads and sanitizes $_SERVER' );
expect_same( true, Conditional::is_rest(), 'is_rest sees REQUEST_URI from $_SERVER' );

$_ENV['EXACT_TEST_ENV'] = '  from-env  ';
expect_same( 'from-env', Params::env( 'EXACT_TEST_ENV' ), 'env reads and sanitizes $_ENV' );

$GLOBALS['exact_test_doing_ajax']      = false;
$_SERVER['HTTP_X_REQUESTED_WITH']      = 'XMLHttpRequest';
expect_same( true, Conditional::doing_ajax(), 'doing_ajax sees X-Requested-With from $_SERVER' );

$attrs = HTML::build_attributes(
	[
		'disabled'   => false,
		'checked'    => true,
		'data-label' => 'a<b>&"',
		'id'         => null,
	]
);
expect_same( 'checked data-label="a&lt;b&gt;&amp;&quot;" id', $attrs, 'false attributes are omitted and true is valueless' );
