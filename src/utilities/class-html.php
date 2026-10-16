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
	public static function classnames( ...$args ): string {
		$classes = [];

		foreach ( $args as $item ) {
			foreach ( is_array( $item ) ? $item : [ $item ] as $key => $value ) {
				$name = is_int( $key ) ? $value : $key;
				if ( is_array( $name ) || ( is_object( $name ) && ! method_exists( $name, '__toString' ) ) ) {
					continue;
				}
				if ( is_int( $key ) || $value ) {
					$classes[] = $name;
				}
			}
		}

		return implode( ' ', array_filter( $classes ) );
	}
}
