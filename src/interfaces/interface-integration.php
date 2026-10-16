<?php
/**
 * Interface for registering integrations with WordPress.
 *
 * @package ExactStudios\Framework
 * @author  Exact Studios <info@exactwp.com>
 * @since   1.0.0
 */

namespace ExactStudios\Framework\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Integration Interface
 *
 * Implement this interface to define and register hooks and filters for WordPress.
 */
interface Integration_Interface {

	/**
	 * Register hooks and filters with WordPress.
	 *
	 * This method should contain all necessary `add_action` or `add_filter` calls
	 * required to integrate the functionality with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function hooks(): void;
}
