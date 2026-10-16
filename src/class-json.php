<?php
/**
 * JSON manager class
 *
 * It handles JSON output for use on the backend and frontend.
 *
 * @since   1.0.0
 * @package ExactStudios\JSON
 * @author  Exact Studios <info@exactwp.com>
 */

namespace ExactStudios\JSON;

use InvalidArgumentException;

/**
 * JSON class
 */
class JSON {

	/**
	 * JSON Holder.
	 *
	 * @var array
	 */
	private array $data = [];

	/**
	 * Default Object name.
	 *
	 * @var string|null
	 */
	private $default_object_name = null;

	/**
	 * The constructor
	 *
	 * @param string $object_name Object name to be used.
	 */
	public function __construct( string $object_name ) {
		$this->default_object_name = $object_name;
	}

	/**
	 * Bind all hooks.
	 *
	 * @since 1.0.0
	 *
	 * @throws InvalidArgumentException When the object name is not defined.
	 *
	 * @return void
	 */
	public function hooks(): void {
		if ( empty( $this->default_object_name ) ) {
			throw new InvalidArgumentException( 'Please set a default object name to be used when printing JSON.' );
		}

		$hook = is_admin() ? 'admin_footer' : 'wp_footer';
		add_action( $hook, [ $this, 'output' ], 0 );
	}

	/**
	 * Add to JSON object.
	 *
	 * @since  1.0.0
	 *
	 * @param mixed ...$args Arguments.
	 *
	 * @return self
	 */
	public function add( ...$args ): self {
		[ $key, $value, $object_name ] = $this->get_add_data( $args );

		// Early Bail!!
		if ( empty( $key ) ) {
			return $this;
		}

		// If array is passed.
		if ( is_array( $key ) ) {
			foreach ( $key as $arr_key => $arr_value ) {
				$this->add_to_storage( (string) $arr_key, $arr_value, $object_name );
			}
		} else {
			$this->add_to_storage( (string) $key, $value, $object_name );
		}

		return $this;
	}

	/**
	 * Add to storage.
	 *
	 * @since  1.0.0
	 *
	 * @param string $key         Unique identifier.
	 * @param mixed  $value       The data itself, which can be either a single or an array.
	 * @param string $object_name Name for the JavaScript object.
	 *                            Passed directly, so it should be a qualified JS variable.
	 * @return void
	 */
	private function add_to_storage( string $key, mixed $value, string $object_name ): void {
		if ( ! isset( $this->data[ $object_name ][ $key ] ) ) {
			$this->data[ $object_name ][ $key ] = $value;
			return;
		}

		// If key already exists.
		$old_value = $this->data[ $object_name ][ $key ];
		$is_array  = is_array( $old_value ) && is_array( $value );

		$this->data[ $object_name ][ $key ] = $is_array ? array_replace_recursive( $old_value, $value ) : $value;
	}

	/**
	 * Remove from JSON object.
	 *
	 * @since  1.0.0
	 *
	 * @param string      $key         Unique identifier.
	 * @param string|null $object_name Name for the JavaScript object.
	 *
	 * @return self
	 */
	public function remove( string $key, ?string $object_name = null ): self {
		if ( empty( $key ) ) {
			return $this;
		}

		if ( empty( $object_name ) ) {
			$object_name = $this->default_object_name;
		}

		if ( isset( $this->data[ $object_name ][ $key ] ) ) {
			unset( $this->data[ $object_name ][ $key ] );
		}

		return $this;
	}

	/**
	 * Clear all data.
	 *
	 * @since 1.0.0
	 *
	 * @return self
	 */
	public function clear_all(): self {
		$this->data                               = [];
		$this->data[ $this->default_object_name ] = [];

		return $this;
	}

	/**
	 * Print data.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function output(): void {
		$script = $this->encode();
		if ( ! $script ) {
			return;
		}

		wp_print_inline_script_tag( $script );
	}

	/**
	 * Get encoded string.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	private function encode(): string {
		$script = '';
		foreach ( $this->data as $object_name => $object_data ) {
			$script .= $this->single_object( $object_name, $object_data );
		}

		return $script;
	}

	/**
	 * Encode single object.
	 *
	 * @since  1.0.0
	 *
	 * @param string $object_name Object name to use as JS variable.
	 * @param array  $object_data Object data to JSON encode.
	 *
	 * @return string
	 */
	private function single_object( string $object_name, array $object_data ): string {
		if ( empty( $object_data ) || ! preg_match( '/^[A-Za-z_$][A-Za-z0-9_$]*$/', $object_name ) ) {
			return '';
		}

		return "var $object_name = " . wp_json_encode( $object_data ) . ';' . PHP_EOL;
	}

	/**
	 * Normalize add arguments.
	 *
	 * @param array $args Arguments array.
	 *
	 * @return array
	 */
	private function get_add_data( array $args ): array {
		$key         = $args[0] ?? null;
		$value       = $args[1] ?? null;
		$object_name = $args[2] ?? $this->default_object_name;

		if ( is_array( $key ) && ! empty( $value ) ) {
			$object_name = $value;
			$value       = null;
		}

		return [
			$key,
			$value,
			$object_name,
		];
	}
}
