<?php
/**
 * Installation routine
 *
 * @package ExactStudios\Installation
 * @author  Exact Studios <info@exactwp.com>
 * @since   1.0.0
 */

namespace ExactStudios\Framework\Installation;

use ExactStudios\Framework\Interfaces\Initializer_Interface;
use ExactStudios\Framework\Utilities\WordPress;
use WP_Site;

defined( 'ABSPATH' ) || exit;

/**
 * Install.
 */
abstract class Install implements Initializer_Interface {

	/**
	 * Get the base file of the plugin.
	 *
	 * @return string Plugin base file path.
	 */
	abstract protected function get_base_file(): string;

	/**
	 * Register lifecycle hooks as soon as the plugin constructs this object.
	 */
	public function __construct() {
		$file = $this->get_base_file();
		if ( '' === $file ) {
			return;
		}

		register_activation_hook( $file, [ $this, 'activation' ] );
		register_deactivation_hook( $file, [ $this, 'deactivation' ] );
		register_uninstall_hook( $file, [ static::class, 'uninstall' ] );
	}

	/**
	 * Runs this initializer.
	 *
	 * @return void
	 */
	public function initialize(): void {
		if ( '' === $this->get_base_file() ) {
			return;
		}

		add_action( 'wp_initialize_site', [ $this, 'initialize_site' ] );
	}

	/**
	 * Activation routine.
	 *
	 * @param bool $network_wide Whether the plugin is being activated network-wide.
	 *
	 * @return void
	 */
	public function activation( bool $network_wide = false ): void {
		if ( ! is_multisite() || ! $network_wide ) {
			$this->activate();
			return;
		}

		$this->network_activate_deactivate( 'activate' );
	}

	/**
	 * Deactivation routine.
	 *
	 * @param bool $network_wide Whether the plugin is being activated network-wide.
	 *
	 * @return void
	 */
	public function deactivation( bool $network_wide = false ): void {
		if ( ! is_multisite() || ! $network_wide ) {
			$this->deactivate();
			return;
		}

		$this->network_activate_deactivate( 'deactivate' );
	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @param WP_Site $site The new site's object.
	 *
	 * @return void
	 */
	public function initialize_site( $site ): void {
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! is_plugin_active_for_network( plugin_basename( $this->get_base_file() ) ) ) {
			return;
		}

		switch_to_blog( $site->blog_id );
		$this->activate();
		restore_current_blog();
	}

	/**
	 * Run network-wide activation/deactivation of the plugin.
	 *
	 * @param string $action Action to perform.
	 *
	 * @return void
	 */
	private function network_activate_deactivate( string $action ): void {
		$site_ids = WordPress::get_sites();

		if ( empty( $site_ids ) ) {
			return;
		}

		foreach ( $site_ids as $site_id ) {
			switch_to_blog( $site_id );
			$this->$action();
			restore_current_blog();
		}
	}

	/**
	 * Plugin activation callback.
	 *
	 * @return void
	 */
	abstract protected function activate(): void;

	/**
	 * Plugin deactivation callback.
	 *
	 * @return void
	 */
	abstract protected function deactivate(): void;

	/**
	 * Plugin uninstall callback.
	 *
	 * @return void
	 */
	abstract public static function uninstall(): void;
}
