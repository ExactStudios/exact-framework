<?php
/**
 * String utilities
 *
 * @package ExactStudios\Framework\Utilities
 * @author  Exact Studios <info@exactwp.com>
 * @since   1.0.0
 */

namespace ExactStudios\Framework\Utilities;

defined( 'ABSPATH' ) || exit;

/**
 * Str class.
 */
class Str {
	/**
	 * Capitalizes a string.
	 *
	 * @param string $str The string to be capitalized.
	 *
	 * @return string The capitalized string.
	 */
	public static function capitalize( string $str ): string {
		return ucwords( str_replace( '_', ' ', $str ) );
	}

	/**
	 * Check if the string contains the given value.
	 *
	 * @param string $needle      The sub-string to search for.
	 * @param string $haystack    The string to search.
	 * @param bool   $ignore_case Whether to ignore case.
	 *
	 * @return bool
	 */
	public static function contains( string $needle, string $haystack, bool $ignore_case = false ): bool {
		if ( self::is_empty( $needle ) ) {
			return false;
		}

		if ( $ignore_case ) {
			$needle   = self::to_lower( $needle );
			$haystack = self::to_lower( $haystack );
		}

		return mb_strpos( $haystack, $needle ) !== false;
	}

	/**
	 * Validates whether the passed variable is an empty string.
	 *
	 * @param mixed $variable The variable to validate.
	 *
	 * @return bool Whether or not the passed value is an empty string.
	 */
	public static function is_empty( $variable ): bool {
		return ! is_string( $variable ) || '' === $variable;
	}

	/**
	 * Validates whether the passed variable is a non-empty string.
	 *
	 * @param mixed $variable The variable to validate.
	 *
	 * @return bool Whether or not the passed value is a non-empty string.
	 */
	public static function is_non_empty( $variable ): bool {
		return is_string( $variable ) && '' !== $variable;
	}

	/**
	 * Check if the string ends with the given value.
	 *
	 * @param string $needle   The sub-string to search for.
	 * @param string $haystack The string to search.
	 *
	 * @return bool
	 */
	public static function ends_with( string $needle, string $haystack ): bool {
		if ( self::is_empty( $needle ) ) {
			return false;
		}

		$needle_length = self::length( $needle );
		return mb_substr( $haystack, -$needle_length ) === $needle;
	}

	/**
	 * Return the length of the given string.
	 *
	 * @param string      $value    The string to get the length of.
	 * @param string|null $encoding The encoding to use.
	 *
	 * @return int
	 */
	public static function length( string $value, ?string $encoding = null ): int {
		return $encoding ? mb_strlen( $value, $encoding ) : mb_strlen( $value );
	}

	/**
	 * Check if the string begins with the given value.
	 *
	 * @param string $needle   The sub-string to search for.
	 * @param string $haystack The string to search.
	 *
	 * @return bool
	 */
	public static function starts_with( string $needle, string $haystack ): bool {
		if ( self::is_empty( $needle ) ) {
			return false;
		}

		return mb_substr( $haystack, 0, self::length( $needle ) ) === $needle;
	}

	/**
	 * Convert a string to uppercase.
	 *
	 * @param string      $str      The string to convert.
	 * @param string|null $encoding The encoding to use.
	 *
	 * @return string
	 */
	public static function to_upper( string $str, ?string $encoding = 'UTF-8' ): string {
		return $encoding ? mb_strtoupper( $str, $encoding ) : mb_strtoupper( $str );
	}

	/**
	 * Convert a string to lowercase.
	 *
	 * @param string      $str      The string to convert.
	 * @param string|null $encoding The encoding to use.
	 *
	 * @return string
	 */
	public static function to_lower( string $str, ?string $encoding = 'UTF-8' ): string {
		return $encoding ? mb_strtolower( $str, $encoding ) : mb_strtolower( $str );
	}
}
