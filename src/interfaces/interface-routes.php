<?php
/**
 * Interface for registering routes with WordPress.
 *
 * @package ExactStudios\Framework
 * @author  Exact Studios <info@exactwp.com>
 * @since   1.0.0
 */

namespace ExactStudios\Framework\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Routes Interface
 *
 * Implement this interface to define and register custom routes.
 */
interface Routes_Interface {

	/**
	 * Registers routes with WordPress.
	 *
	 * This method should be used to register routes with WordPress, typically
	 * through `register_rest_route()` or other WordPress routing mechanisms.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes(): void;
}
