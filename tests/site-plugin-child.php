<?php
/**
 * Proves a new site loads plugin.php before the network-active check.
 *
 * @package ExactStudios\Framework
 */

$root = sys_get_temp_dir() . '/exact-abspath-' . uniqid( '', true );
mkdir( $root . '/wp-admin/includes', 0777, true );
file_put_contents(
	$root . '/wp-admin/includes/plugin.php',
	'<?php function is_plugin_active_for_network( $plugin ) { $GLOBALS["exact_plugin_php"] = true; return true; }'
);

define( 'ABSPATH', $root . '/' );

/**
 * Activation hook stub.
 *
 * @return void
 */
function register_activation_hook() {}

/**
 * Deactivation hook stub.
 *
 * @return void
 */
function register_deactivation_hook() {}

/**
 * Uninstall hook stub.
 *
 * @return void
 */
function register_uninstall_hook() {}

/**
 * Plugin basename stub.
 *
 * @param string $file File.
 * @return string
 */
function plugin_basename( $file ) {
	return basename( (string) $file );
}

/**
 * Blog switch stub.
 *
 * @return void
 */
function switch_to_blog() {}

/**
 * Restore blog stub.
 *
 * @return void
 */
function restore_current_blog() {}

require dirname( __DIR__ ) . '/src/interfaces/interface-initializer.php';
require dirname( __DIR__ ) . '/src/installation/class-install.php';
require __DIR__ . '/install-fixture.php';

$install = new ExactStudios\Framework\Installation\Fixture_Install();
$install->initialize_site( (object) [ 'blog_id' => 4 ] );
