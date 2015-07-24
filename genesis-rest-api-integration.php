<?php
/**
 * Genesis REST API Integration
 *
 * @package 			Genesis_REST_API_Integration
 * @author				Braad Martin <wordpress@braadmartin.com>
 * @license 			GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: 		Genesis REST API Integration
 * Plugin URI: 			https://wordpress.org/plugins/genesis-rest-api-integration/
 * Description: 		Adds any content output from the Genesis Framework hooks to the post object response when using the WP REST API v2.
 * Version: 			1.0.0
 * Author:				Braad Martin
 * Author URI: 			http://braadmartin.com
 * License: 			GPL-2.0+
 * License URI: 		http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: 		genesis-rest-api-integration
 * Domain Path: 		/languages
 */

define( 'GENESIS_REST_API_INTEGRATION_VERSION', '1.0.0' );

add_filter( 'rest_prepare_post', 'genesis_rest_api_integration_prepare_post', 10, 3 );
/**
 * Add any output from the Genesis loop hooks to the post object response.
 */
function genesis_rest_api_integration_prepare_post( $data, $post, $request ) {

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
	$query = new WP_Query( 'p=' . $post_id );

	// Bail if the query didn't return a post.
	if ( ! $query->have_posts() ) {
		return $data;
	}

	// Set the $post and $wp_query globals.
	$post = $query->post;
	$wp_query = $query;

	// Do the genesis loop while capturing the output of each hook.
	ob_start();
	do_action( 'genesis_before_while' );
	$genesis_before_while = ob_get_clean();

		ob_start();
		do_action( 'genesis_before_entry' );
		$genesis_before_entry = ob_get_clean();

			ob_start();
			do_action( 'genesis_entry_header' );
			$genesis_entry_header = ob_get_clean();

			ob_start();
			do_action( 'genesis_before_entry_content' );
			$genesis_before_entry_content = ob_get_clean();

			ob_start();
			do_action( 'genesis_entry_content' );
			$genesis_entry_content = ob_get_clean();

			ob_start();
			do_action( 'genesis_after_entry_content' );
			$genesis_after_entry_content = ob_get_clean();

			ob_start();
			do_action( 'genesis_entry_footer' );
			$genesis_entry_footer = ob_get_clean();

		ob_start();
		do_action( 'genesis_after_entry' );
		$genesis_after_entry = ob_get_clean();

	ob_start();
	do_action( 'genesis_after_endwhile' );
	$genesis_after_endwhile = ob_get_clean();

	$return_empty = apply_filters( 'genesis_rest_api_return_empty_hooks', false );

	// Only return hooks that have output.
	if ( '' !== $genesis_before_while || $return_empty ) {
		$data->data['genesis_before_while'] = $genesis_before_while;
	}
	if ( '' !== $genesis_before_entry || $return_empty ) {
		$data->data['genesis_before_entry'] = $genesis_before_entry;
	}
	if ( '' !== $genesis_entry_header || $return_empty ) {
		$data->data['genesis_entry_header'] = $genesis_entry_header;
	}
	if ( '' !== $genesis_before_entry_content || $return_empty ) {
		$data->data['genesis_before_entry_content'] = $genesis_before_entry_content;
	}
	if ( '' !== $genesis_entry_content || $return_empty ) {
		$data->data['genesis_entry_content'] = $genesis_entry_content;
	}
	if ( '' !== $genesis_after_entry_content || $return_empty ) {
		$data->data['genesis_after_entry_content'] = $genesis_after_entry_content;
	}
	if ( '' !== $genesis_entry_footer || $return_empty ) {
		$data->data['genesis_entry_footer'] = $genesis_entry_footer;
	}
	if ( '' !== $genesis_after_entry || $return_empty ) {
		$data->data['genesis_after_entry'] = $genesis_after_entry;
	}
	if ( '' !== $genesis_after_endwhile || $return_empty ) {
		$data->data['genesis_after_endwhile'] = $genesis_after_endwhile;
	}

	return $data;
}
