<?php
/**
 * WordPress utilities
 *
 * @package ExactStudios\Installation
 * @author  Exact Studios <info@exactwp.com>
 * @since   1.0.0
 */

namespace ExactStudios\Framework\Utilities;

defined( 'ABSPATH' ) || exit;

/**
 * WordPress class.
 */
class WordPress {
	/**
	 * Get network sites
	 *
	 * @return array|int
	 */
	public static function get_sites() {
		global $wpdb;

		return get_sites(
			[
				'archived'   => 0,
				'spam'       => 0,
				'deleted'    => 0,
				'network_id' => $wpdb->siteid,
				'fields'     => 'ids',
				'number'     => 0,
			]
		);
	}

	/**
	 * Get roles.
	 *
	 * @param string $output How to return roles.
	 *
	 * @return array
	 */
	public static function get_roles( $output = 'names' ) {
		$wp_roles = wp_roles();

		if ( 'names' !== $output ) {
			return $wp_roles->roles;
		}

		return $wp_roles->get_names();
	}

	/**
	 * Get action from request.
	 *
	 * @return bool|string
	 */
	public static function get_action() {
		foreach ( [ 'action', 'action2' ] as $key ) {
			$action = Params::request( $key );

			if ( false === $action || '' === $action || '-1' === $action ) {
				continue;
			}

			return sanitize_key( $action );
		}

		return false;
	}

	/**
	 * Instantiates the WordPress filesystem for use.
	 *
	 * @return object
	 */
	public static function get_filesystem() {
		global $wp_filesystem;

		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		return $wp_filesystem;
	}

	/**
	 * Improve WP_Query performance
	 *
	 * @param array $args Query arguments.
	 *
	 * @return array
	 */
	public static function improve_wp_query( $args ): array {
		$args['no_found_rows']          = true;
		$args['update_post_meta_cache'] = false;
		$args['update_post_term_cache'] = false;

		return $args;
	}
}
