<?php
/**
 * Interface for registering initializers with WordPress.
 *
 * @package ExactStudios\Framework
 * @author  Exact Studios <info@exactwp.com>
 * @since   1.0.0
 */

namespace ExactStudios\Framework\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Initializer Interface
 *
 * Implement this interface to define and run initialization routines.
 */
interface Initializer_Interface {

	/**
	 * Runs the initializer.
	 *
	 * This method should perform all setup tasks required to initialize a component
	 * or functionality. For example, registering hooks, loading dependencies, or
	 * configuring services.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function initialize(): void;
}
