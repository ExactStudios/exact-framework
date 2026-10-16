<?php
/**
 * Array utilities
 *
 * @since   1.0.0
 * @package ExactStudios\Framework\Utilities
 * @author  Exact Studios <info@exactwp.com>
 */

namespace ExactStudios\Framework\Utilities;

use ArrayAccess;

defined( 'ABSPATH' ) || exit;

/**
 * Array utilities class.
 */
class Arr {

	/**
	 * Determine whether the given value is array accessible.
	 *
	 * @param mixed $value Value to check.
	 *
	 * @return bool
	 */
	public static function accessible( $value ): bool {
		return is_array( $value ) || $value instanceof ArrayAccess;
	}

	/**
	 * Determine whether the given value is an empty array.
	 *
	 * @param mixed $value Value to check.
	 *
	 * @return bool
	 */
	public static function is_empty( $value ): bool {
		return ! static::accessible( $value ) || [] === $value;
	}

	/**
	 * Determine whether the given value is a non-empty array.
	 *
	 * @param mixed $value Value to check.
	 *
	 * @return bool
	 */
	public static function is_non_empty( $value ): bool {
		return static::accessible( $value ) && [] !== $value;
	}

	/**
	 * Add an element to an array using "dot" notation if it doesn't exist.
	 *
	 * @param array      $arr   Array to add to.
	 * @param string|int $key   Key.
	 * @param mixed      $value Value.
	 *
	 * @return array
	 */
	public static function add( array &$arr, $key, $value ): array {
		if ( null === static::get( $arr, $key ) ) {
			static::set( $arr, $key, $value );
		}

		return $arr;
	}

	/**
	 * Determine if the given key exists in the provided array.
	 *
	 * @param ArrayAccess|array $arr Array to check key in.
	 * @param string|int        $key Key to check for.
	 *
	 * @return bool
	 */
	public static function exists( $arr, $key ): bool {
		if ( $arr instanceof ArrayAccess ) {
			return $arr->offsetExists( $key );
		}

		return array_key_exists( $key, $arr );
	}

	/**
	 * Create an array from a string by splitting it by the separator.
	 *
	 * @param string $str       The string to split.
	 * @param string $separator Specifies where to break the string.
	 *
	 * @return array
	 */
	public static function from_string( string $str, string $separator = ',' ): array {
		return array_values( array_filter( array_map( 'trim', explode( $separator, $str ) ) ) );
	}

	/**
	 * Get an item from an array using "dot" notation.
	 *
	 * @param ArrayAccess|array $arr     Array to use.
	 * @param string|int|null   $key     Key to get.
	 * @param mixed             $default Default value if not found.
	 *
	 * @return mixed
	 */
	public static function get( $arr, $key, $default = null ) {
		if ( ! static::accessible( $arr ) ) {
			return $default;
		}

		if ( null === $key ) {
			return $arr;
		}

		if ( static::exists( $arr, $key ) ) {
			return $arr[ $key ];
		}

		foreach ( explode( '.', (string) $key ) as $segment ) {
			if ( static::accessible( $arr ) && static::exists( $arr, $segment ) ) {
				$arr = $arr[ $segment ];
			} else {
				return $default;
			}
		}

		return $arr;
	}

	/**
	 * Check if an item or items exist in an array using "dot" notation.
	 *
	 * @param ArrayAccess|array $arr  Array to use.
	 * @param string|array      $keys Keys to check.
	 *
	 * @return bool
	 */
	public static function has( $arr, $keys ): bool {
		$keys = (array) $keys;

		if ( ! $arr || [] === $keys ) {
			return false;
		}

		foreach ( $keys as $key ) {
			$subkey_array = $arr;

			if ( static::exists( $arr, $key ) ) {
				continue;
			}

			foreach ( explode( '.', $key ) as $segment ) {
				if ( static::accessible( $subkey_array ) && static::exists( $subkey_array, $segment ) ) {
					$subkey_array = $subkey_array[ $segment ];
				} else {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Determine if any of the keys exist in an array using "dot" notation.
	 *
	 * @param ArrayAccess|array $arr  Array to use.
	 * @param string|array      $keys Keys to check.
	 *
	 * @return bool
	 */
	public static function has_any( $arr, $keys ): bool {
		$keys = (array) $keys;

		if ( ! $arr || [] === $keys ) {
			return false;
		}

		foreach ( $keys as $key ) {
			if ( static::has( $arr, $key ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Insert a single array item inside another array at a set position.
	 *
	 * @param array $arr      Array to modify. Is passed by reference.
	 * @param array $new      New array to insert.
	 * @param int   $position Position in the main array to insert the new array.
	 */
	public static function insert( array &$arr, array $new, int $position ) {
		$arr = array_merge(
			array_slice( $arr, 0, $position ),
			$new,
			array_slice( $arr, $position )
		);
	}

	/**
	 * Push an item onto the beginning of an array.
	 *
	 * @param array $arr   Array to prepend to.
	 * @param mixed $value Value.
	 * @param mixed $key   Key.
	 *
	 * @return array
	 */
	public static function prepend( array $arr, $value, $key = null ): array {
		if ( null === $key ) {
			array_unshift( $arr, $value );
		} else {
			$arr = [ $key => $value ] + $arr;
		}
		return $arr;
	}

	/**
	 * Set an array item to a given value using "dot" notation.
	 *
	 * @param array           $arr   Array to set into.
	 * @param string|int|null $key   Key.
	 * @param mixed           $value Value.
	 *
	 * @return array
	 */
	public static function set( array &$arr, $key, $value ): array {
		if ( null === $key ) {
			$arr = $value;
			return $arr;
		}

		$cursor = &$arr;
		$keys   = explode( '.', (string) $key );
		$last   = array_pop( $keys );

		foreach ( $keys as $segment ) {
			// If the key doesn't exist at this depth, we will just create an empty array
			// to hold the next value, allowing us to create the arrays to hold final
			// values at the correct depth. Then we'll keep digging into the array.
			if ( ! isset( $cursor[ $segment ] ) || ! is_array( $cursor[ $segment ] ) ) {
				$cursor[ $segment ] = [];
			}

			$cursor = &$cursor[ $segment ];
		}

		$cursor[ $last ] = $value;

		return $arr;
	}

	/**
	 * Sort the array in descending order.
	 *
	 * @param array $arr Array to sort.
	 *
	 * @return array
	 */
	public static function sort_desc( array $arr ): array {
		arsort( $arr );

		return $arr;
	}

	/**
	 * Filter the array using the given callback.
	 *
	 * @param array    $arr      Array to filter.
	 * @param callable $callback Callback to run on array.
	 *
	 * @return array
	 */
	public static function where( array $arr, callable $callback ): array {
		return array_filter( $arr, $callback, ARRAY_FILTER_USE_BOTH );
	}

	/**
	 * Filter items where the value is not null.
	 *
	 * @param array $arr Array to filter.
	 *
	 * @return array
	 */
	public static function where_not_null( array $arr ): array {
		return static::where( $arr, fn( $value ) => null !== $value );
	}

	/**
	 * If the given value is not an array and not null, wrap it in one.
	 *
	 * @param mixed $value Value to wrap.
	 *
	 * @return array
	 */
	public static function wrap( $value ): array {
		if ( null === $value ) {
			return [];
		}

		return is_array( $value ) ? $value : [ $value ];
	}
}
