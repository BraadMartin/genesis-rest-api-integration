<?php
/**
 * Genesis REST API Integration
 *
 * @package             Genesis_REST_API_Integration
 * @author              Braad Martin <wordpress@braadmartin.com>
 * @license             GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name:         Genesis REST API Integration
 * Plugin URI:          https://wordpress.org/plugins/genesis-rest-api-integration/
 * Description:         Adds content output from the Genesis framework hooks to the response data for posts, pages, and custom post types when using the WP REST API v2.
 * Version:             1.0.0
 * Author:              Braad Martin
 * Author URI:          http://braadmartin.com
 * License:             GPL-2.0+
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:         genesis-rest-api-integration
 * Domain Path:         /languages
 */

define( 'GENESIS_REST_API_INTEGRATION_VERSION', '1.0.0' );

add_action( 'rest_api_init', 'genesis_rest_api_integration_init', 0 );
/**
 * Set up the the rest_prepare_{$post_type} filters that we'll use to add
 * content to the post object response.
 *
 * @since  1.0.0
 */
function genesis_rest_api_integration_init() {

    global $wp_post_types;

	// Get an array of all the registered custom post types.
	$args = array(
		'public'   => true,
		'_builtin' => false,
	);
	$post_types = get_post_types( $args, 'names', 'and' );

	// Manually add back in posts and pages.
	$post_types[] = 'post';
	$post_types[] = 'page';

	// Allow the array of post type objects to be filtered.
	$post_types = apply_filters( 'genesis_rest_api_supported_post_types', $post_types );

	// Allow the choice of also registering API support for CPTs with this plugin.
	$register_cpt_api_support = apply_filters( 'genesis_rest_api_register_cpt_api_support', false );

	// Loop over each post type, register support for the API, and add the response filter.
	foreach ( $post_types as $post_type ) {

		$post_type_object = get_post_type_object( $post_type );

		// Only register support for the API on custom post types if specified.
		if ( $register_cpt_api_support && 'post' !== $post_type_object->name && 'page' !== $post_type_object->name ) {

			// Only set these properties if they are not already set.
			if ( ! isset( $wp_post_types[ $post_type_object->name ]->show_in_rest ) ) {
				$wp_post_types[ $post_type_object->name ]->show_in_rest = true;
			}
			if ( ! isset( $wp_post_types[ $post_type_object->name ]->rest_base ) ) {
				$wp_post_types[ $post_type_object->name ]->rest_base = $post_type_object->name;
			}
			if ( ! isset( $wp_post_types[ $post_type_object->name ]->rest_controller_class ) ) {
				$wp_post_types[ $post_type_object->name ]->rest_controller_class = 'WP_REST_Posts_Controller';
			}
		}

		add_filter( 'rest_prepare_' . $post_type_object->name, 'genesis_rest_api_integration_add_post_data', 10, 3 );
	}
}

/**
 * Add any output from the Genesis hooks to the response data.
 *
 * @since   1.0.0
 *
 * @param   object  $data     The post object response data.
 * @param   object  $post     The post object.
 * @param   object  $request  The request object.
 *
 * @return  object  $data     The response data.
 */
function genesis_rest_api_integration_add_post_data( $data, $post, $request ) {

	// Store the post object.
	$post_object = $post;

	// These are necessary to set the context for the genesis loop.
	global $post;
	global $wp_query;

	// Get the id that was passed in.
	$post_id = $data->data['id'];

	// Bail if the id isn't valid.
	if ( ! is_numeric( $post_id ) ) {
		return $data;
	}

	// Do the query.
	if ( 'page' == $post_object->post_type ) {
		$query = new WP_Query( 'page_id=' . $post_id );
	} else {
		$query = new WP_Query( 'p=' . $post_id . '&post_type=' . $post_object->post_type );
	}

	// Bail if the query didn't return a post.
	if ( ! $query->have_posts() ) {
		return $data;
	}

	// Set the $post and $wp_query globals.
	$post = $post_object;
	$wp_query = $query;

	// Set up an array of all the genesis hooks we'll support.
	$genesis_hooks = array(
		'genesis_before',
		'genesis_before_header',
		'genesis_site_title',
		'genesis_site_description',
		'genesis_header_right',
		'genesis_after_header',
		'genesis_before_content_sidebar_wrap',
		'genesis_before_content',
		'genesis_before_loop',
		'genesis_before_while',
		'genesis_before_entry',
		'genesis_entry_header',
		'genesis_before_entry_content',
		'genesis_entry_content',
		'genesis_after_entry_content',
		'genesis_entry_footer',
		'genesis_after_entry',
		'genesis_after_endwhile',
		'genesis_after_loop',
		'genesis_before_sidebar_widget_area',
		'genesis_after_sidebar_widget_area',
		'genesis_after_content_sidebar_wrap',
		'genesis_before_footer',
		'genesis_footer',
		'genesis_after_footer',
		'genesis_after',
	);

	// Allow the Genesis hooks that we support to be filtered.
	$genesis_hooks = apply_filters( 'genesis_rest_api_supported_hooks', $genesis_hooks );

	// By default we return only fields that have output, but this filter
	// allows the option to return all fields even if they are empty.
	$return_empty = apply_filters( 'genesis_rest_api_return_empty_hooks', false );

	// Do each Genesis hook and add any output to the response object.
	foreach ( $genesis_hooks as $genesis_hook ) {

		ob_start();

		do_action( $genesis_hook );

		$output = ob_get_clean();

		if ( '' !== $output || $return_empty ) {
			$data->data[ $genesis_hook ] = $output;
		}

	}

	return $data;
}
