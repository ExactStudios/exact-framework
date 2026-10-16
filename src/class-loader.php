<?php
/**
 * Class that manages loading integrations.
 *
 * @package ExactStudios\Framework
 * @author  Exact Studios <info@exactwp.com>
 * @since   1.0.0
 */

namespace ExactStudios\Framework;

defined( 'ABSPATH' ) || exit;

/**
 * Loader class
 */
class Loader {

	/**
	 * The registered integrations.
	 *
	 * @var array<string, mixed>
	 */
	protected array $integrations = [];

	/**
	 * The registered integrations.
	 *
	 * @var array<string, mixed>
	 */
	protected array $initializers = [];

	/**
	 * The registered routes.
	 *
	 * @var array<string, mixed>
	 */
	protected array $routes = [];

	/**
	 * Hold containers
	 *
	 * @var array<string, object>
	 */
	protected array $containers = [];

	/**
	 * Magic getter for containers.
	 *
	 * @param string $name Name of property.
	 *
	 * @return mixed|null
	 */
	public function __get( $name ) {
		if ( array_key_exists( $name, $this->containers ) ) {
			return $this->containers[ $name ];
		}

		// phpcs:disable
		// The __get frame already records the caller's file and line. The next frame is whoever included that file.
		$trace  = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 2 );
		$caller = $trace[0];
		\trigger_error(
			'Undefined property via __get(): ' . $name .
			' in ' . $caller['file'] .
			' on line ' . $caller['line'],
			E_USER_NOTICE
		);
		// phpcs:enable

		return null;
	}

	/**
	 * Loads all registered classes if their conditionals are met.
	 *
	 * @return void
	 */
	public function load(): void {
		$this->load_initializers();

		if ( ! \did_action( 'init' ) ) {
			\add_action( 'init', [ $this, 'load_integrations' ] );
		} else {
			$this->load_integrations();
		}

		// Load routes if defined.
		if ( ! empty( $this->routes ) ) {
			\add_action( 'rest_api_init', [ $this, 'load_routes' ] );
		}
	}

	/**
	 * Register as.
	 *
	 * @param string $register_as Register the container as.
	 * @param string $class_name  The class name of the registery to be loaded.
	 * @param string $alias       The class alias.
	 * @param array  $args        The constructor arguments.
	 *
	 * @return void
	 */
	private function register( string $register_as, $class_name, string $alias = '', $args = null ): void {
		if ( ! empty( $args ) ) {
			$class_name = [ $class_name, $args ];
		}

		if ( '' === $alias ) {
			$this->{$register_as}[] = $class_name;
		} else {
			$this->{$register_as}[ $alias ] = $class_name;
		}
	}

	/**
	 * Register an integration.
	 *
	 * @param string $integration The class name of the integration to be loaded.
	 * @param string $alias       The class alias.
	 * @param array  $args        The constructor arguments.
	 *
	 * @return void
	 */
	public function register_integration( string $integration, string $alias = '', $args = null ): void {
		$this->register( 'integrations', $integration, $alias, $args );
	}

	/**
	 * Register an initializer.
	 *
	 * @param string $initializer The class name of the initializer to be loaded.
	 * @param string $alias       The class alias.
	 * @param array  $args        The constructor arguments.
	 *
	 * @return void
	 */
	public function register_initializer( string $initializer, string $alias = '', $args = null ): void {
		$this->register( 'initializers', $initializer, $alias, $args );
	}

	/**
	 * Register a route.
	 *
	 * @param string $router The class name of the route to be loaded.
	 * @param string $alias  The class alias.
	 * @param array  $args   The constructor arguments.
	 *
	 * @return void
	 */
	public function register_route( string $router, string $alias = '', $args = null ): void {
		$this->register( 'routes', $router, $alias, $args );
	}

	/**
	 * Loads all registered initializers if their conditionals are met.
	 *
	 * @return void
	 */
	protected function load_initializers(): void {
		foreach ( $this->initializers as $alias => $class ) {
			$this->create_container( $class, 'initialize', $alias );
		}
	}

	/**
	 * Loads all registered integrations if their conditionals are met.
	 *
	 * @return void
	 */
	public function load_integrations(): void {
		foreach ( $this->integrations as $alias => $class ) {
			$this->create_container( $class, 'hooks', $alias );
		}
	}

	/**
	 * Loads all registered routes if their conditionals are met.
	 *
	 * @return void
	 */
	public function load_routes(): void {
		foreach ( $this->routes as $alias => $class ) {
			$this->create_container( $class, 'register_routes', $alias );
		}
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param bool|string $value Constant value.
	 *
	 * @return void
	 */
	protected function define( $name, $value ): void {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Create container if needed.
	 *
	 * @param string $data   Class data.
	 * @param string $method Method to execute.
	 * @param string $alias  Class alias.
	 *
	 * @return void
	 */
	private function create_container( $data, string $method, string $alias ): void {
		$class_name = is_string( $data ) ? $data : $data[0];
		$arguments  = is_string( $data ) ? [] : $data[1];

		if ( ! \class_exists( $class_name, true ) ) {
			\_doing_it_wrong( __METHOD__, 'Class not found: ' . esc_html( $class_name ), '1.0.0' );
			return;
		}

		$container = empty( $arguments ) ? new $class_name() : new $class_name( ...$arguments );

		$container->$method();

		if ( is_string( $alias ) ) {
			$this->containers[ $alias ] = $container;
		}
	}
}
