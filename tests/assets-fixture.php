<?php
/**
 * Concrete assets registry for tests.
 *
 * @package ExactStudios\Framework
 */

namespace ExactStudios\Framework;

/**
 * Test double.
 */
class Fixture_Assets extends Assets_Registry {

	/**
	 * Filesystem base.
	 *
	 * @var string
	 */
	public string $base_path = '';

	/**
	 * Base URL.
	 *
	 * @return string
	 */
	public function get_base_url(): string {
		return 'https://example.com/plugin/';
	}

	/**
	 * Base path.
	 *
	 * @return string
	 */
	public function get_base_path(): string {
		return $this->base_path;
	}

	/**
	 * Handle prefix.
	 *
	 * @return string
	 */
	public function get_prefix(): string {
		return 'ex';
	}

	/**
	 * Asset version.
	 *
	 * @return string
	 */
	public function get_version(): string {
		return '9.9.9';
	}
}
