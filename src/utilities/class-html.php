<?php
/**
 * HTML formatting utilities
 *
 * @package ExactStudios\Framework\Utilities
 * @author  Exact Studios <info@exactwp.com>
 * @since   1.0.0
 */

namespace ExactStudios\Framework\Utilities;

defined( 'ABSPATH' ) || exit;

/**
 * HTML class.
 */
class HTML {

	/**
	 * Generate html attribute string from array.
	 *
	 * @param array  $attributes Contains key/value pair to generate a string.
	 * @param string $prefix     If you want to append a prefic before every key.
	 *
	 * @return string
	 */
	public static function build_attributes( $attributes, $prefix = '' ): string {
		// Early Bail!
		if ( empty( $attributes ) ) {
			return '';
		}

		$out = [];
		foreach ( $attributes as $name => $value ) {
			if ( false === $value ) {
				continue;
			}

			$name = esc_attr( $prefix . $name );

			if ( true === $value || null === $value ) {
				$out[] = $name;
				continue;
			}

			$out[] = $name . '="' . esc_attr( $value ) . '"';
		}

		return implode( ' ', $out );
	}

	/**
	 * Generate classnames from arguments
	 *
	 * @return string
	 */
	public static function classnames(): string {
		$args    = func_get_args();
		$data    = array_reduce( $args, [ __CLASS__, 'classnames_reduce' ], [] );
		$classes = array_map(
			[ __CLASS__, 'classnames_mapper' ],
			array_keys( $data ),
			array_values( $data )
		);
		$classes = array_filter( $classes );

		return implode( ' ', $classes );
	}

	/**
	 * Classnames helper function
	 *
	 * @param mixed $carry Holds the return value of the previous iteration.
	 * @param mixed $item  Holds the value of the current iteration.
	 *
	 * @return mixed
	 */
	private static function classnames_reduce( $carry, $item ) {
		if ( is_array( $item ) ) {
			return array_merge( $carry, $item );
		}

		$carry[] = $item;

		return $carry;
	}

	/**
	 * Classnames helper function
	 *
	 * @param mixed $key   Key of array item.
	 * @param mixed $value Value of array item.
	 *
	 * @return array
	 */
	private static function classnames_mapper( $key, $value ) {
		$condition = $value;
		$return    = $key;

		if ( is_int( $key ) ) {
			$condition = null;
			$return    = $value;
		}

		$is_array             = is_array( $return );
		$is_object            = is_object( $return );
		$is_stringable_type   = ! $is_array && ! $is_object;
		$is_stringable_object = $is_object && method_exists( $return, '__toString' );

		if ( ! $is_stringable_type && ! $is_stringable_object ) {
			return null;
		}

		if ( null === $condition ) {
			return $return;
		}

		return $condition ? $return : null;
	}
}
