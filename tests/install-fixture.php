<?php
/**
 * Concrete install class for tests.
 *
 * @package ExactStudios\Framework
 */

namespace ExactStudios\Framework\Installation;

/**
 * Test double.
 */
class Fixture_Install extends Install {

	/**
	 * Activation and deactivation calls.
	 *
	 * @var array<int, string>
	 */
	public array $calls = [];

	/**
	 * Plugin file.
	 *
	 * @return string
	 */
	protected function get_base_file(): string {
		return '/plugins/example/example.php';
	}

	/**
	 * Activation.
	 *
	 * @return void
	 */
	protected function activate(): void {
		$this->calls[] = 'activate';
	}

	/**
	 * Deactivation.
	 *
	 * @return void
	 */
	protected function deactivate(): void {
		$this->calls[] = 'deactivate';
	}

	/**
	 * Uninstall.
	 *
	 * @return void
	 */
	public static function uninstall(): void {}
}
