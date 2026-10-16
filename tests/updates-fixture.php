<?php
/**
 * Concrete updates class for tests.
 *
 * @package ExactStudios\Framework
 */

namespace ExactStudios\Updates;

/**
 * Test double.
 */
class Fixture_Updates extends Updates {

	/**
	 * Update map.
	 *
	 * @var array<string, string>
	 */
	public array $updates = [];

	/**
	 * Folder path.
	 *
	 * @var string
	 */
	public string $folder = '';

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public string $version = '1.0.0';

	/**
	 * Option name.
	 *
	 * @var string
	 */
	public string $option = 'plugin_version';

	/**
	 * Updates.
	 *
	 * @return array<string, string>
	 */
	public function get_updates(): array {
		return $this->updates;
	}

	/**
	 * Folder.
	 *
	 * @return string
	 */
	public function get_folder(): string {
		return $this->folder;
	}

	/**
	 * Version.
	 *
	 * @return string
	 */
	public function get_version(): string {
		return $this->version;
	}

	/**
	 * Option.
	 *
	 * @return string
	 */
	public function get_option_name(): string {
		return $this->option;
	}
}
