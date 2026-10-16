<?php
/**
 * Update manger class
 *
 * @since   1.0.0
 * @package ExactStudios\Updates
 * @author  Exact Studios <info@exactwp.com>
 */

namespace ExactStudios\Updates;

use InvalidArgumentException;

/**
 * Updates class
 */
abstract class Updates {

	/**
	 * Get the list of updates that need to run.
	 *
	 * This method should return an associative array where each key is a version
	 * number (as a string) and each value is the path to the update file relative
	 * to the updates folder. The version number represents the minimum plugin
	 * version required for the update, and the file contains the logic to execute
	 * for that update.
	 *
	 * Example return value:
	 * [
	 *     '1.0.1' => 'updates/update-1.0.1.php',
	 *     '1.0.2' => 'updates/update-1.0.2.php',
	 * ]
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	abstract public function get_updates(): array;

	/**
	 * Get folder path
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	abstract public function get_folder(): string;

	/**
	 * Get plugin version number
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	abstract public function get_version(): string;

	/**
	 * Get plugin option name.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	abstract public function get_option_name(): string;

	/**
	 * Bind all hooks.
	 *
	 * @since 1.0.0
	 *
	 * @throws InvalidArgumentException When folder not defined.
	 * @throws InvalidArgumentException When version not defined.
	 * @throws InvalidArgumentException When option name not defined.
	 *
	 * @return void
	 */
	public function hooks(): void {
		if ( empty( $this->get_folder() ) ) {
			throw new InvalidArgumentException( 'Please set the folder path for update files.' );
		}

		if ( empty( $this->get_version() ) ) {
			throw new InvalidArgumentException( 'Please set the plugin version number.' );
		}

		if ( empty( $this->get_option_name() ) ) {
			throw new InvalidArgumentException( 'Please set option name to save version in database.' );
		}

		add_action( 'admin_init', [ $this, 'do_updates' ] );
	}

	/**
	 * Get installed version number
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_installed_version(): string {
		return get_option( $this->get_option_name(), '0' );
	}

	/**
	 * Check if need any update
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function do_updates(): void {
		if ( ! $this->can_run_updates() ) {
			return;
		}

		$installed_version = $this->get_installed_version();

		// Maybe it's the first install.
		if ( ! $installed_version ) {
			$this->save_version();
			return;
		}

		if ( version_compare( $installed_version, $this->get_version(), '<' ) ) {
			$this->perform_updates();
		}
	}

	/**
	 * Perform all updates
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function perform_updates(): void {
		$lock = $this->get_option_name() . '_lock';

		// ponytail: one option lock per plugin. A killed request holds it until the option is deleted.
		if ( ! add_option( $lock, time(), '', false ) ) {
			return;
		}

		try {
			$installed_version = $this->get_installed_version();
			$updates           = $this->get_updates();
			uksort( $updates, 'version_compare' );

			foreach ( $updates as $version => $path ) {
				if ( version_compare( $installed_version, (string) $version, '<' ) ) {
					$file_path = $this->get_folder() . $path;
					if ( ! file_exists( $file_path ) ) {
						error_log( "Update file {$file_path} not found." ); // phpcs:ignore
						return;
					}

					include_once $file_path;
					$this->save_version( (string) $version );
				}
			}

			$this->save_version();
		} finally {
			delete_option( $lock );
		}
	}

	/**
	 * Whether this request is allowed to migrate.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function can_run_updates(): bool {
		if ( current_user_can( 'update_plugins' ) ) {
			return true;
		}

		if ( wp_doing_cron() ) {
			return true;
		}

		return defined( 'WP_CLI' ) && WP_CLI;
	}

	/**
	 * Save version info.
	 *
	 * @since 1.0.0
	 *
	 * @param string $version Version number to save.
	 *
	 * @return void
	 */
	private function save_version( $version = false ): void {
		if ( empty( $version ) ) {
			$version = $this->get_version();
		}

		update_option( $this->get_option_name(), $version );
	}
}
