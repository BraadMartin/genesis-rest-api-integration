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
 * Description:         Adds content output from the Genesis Framework hooks to the post object response when using the WP REST API v2.
 * Version:             1.0.0
 * Author:              Braad Martin
 * Author URI:          http://braadmartin.com
 * License:             GPL-2.0+
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:         genesis-rest-api-integration
 * Domain Path:         /languages
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

	// Do the full genesis loop (the equivalent of all the hooks that would
	// fire on a single post page) while capturing the output of each hook.
	ob_start();
	do_action( 'genesis_before' );
	$genesis_before = ob_get_clean();

	ob_start();
	do_action( 'genesis_before_header' );
	$genesis_before_header = ob_get_clean();

	ob_start();
	do_action( 'genesis_header' );
	$genesis_header = ob_get_clean();

	ob_start();
	do_action( 'genesis_site_title' );
	$genesis_site_title = ob_get_clean();

	ob_start();
	do_action( 'genesis_site_description' );
	$genesis_site_description = ob_get_clean();

	ob_start();
	do_action( 'genesis_header_right' );
	$genesis_header_right = ob_get_clean();

	ob_start();
	do_action( 'genesis_after_header' );
	$genesis_after_header = ob_get_clean();

	ob_start();
	do_action( 'genesis_before_content_sidebar_wrap' );
	$genesis_before_content_sidebar_wrap = ob_get_clean();

	ob_start();
	do_action( 'genesis_before_content' );
	$genesis_before_content = ob_get_clean();

	ob_start();
	do_action( 'genesis_before_loop' );
	$genesis_before_loop = ob_get_clean();

	ob_start();
	do_action( 'genesis_loop' );
	$genesis_loop = ob_get_clean();

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

	ob_start();
	do_action( 'genesis_after_loop' );
	$genesis_after_loop = ob_get_clean();

	ob_start();
	do_action( 'genesis_before_sidebar_widget_area' );
	$genesis_before_sidebar_widget_area = ob_get_clean();

	ob_start();
	do_action( 'genesis_after_sidebar_widget_area' );
	$genesis_after_sidebar_widget_area = ob_get_clean();

	ob_start();
	do_action( 'genesis_after_content_sidebar_wrap' );
	$genesis_after_content_sidebar_wrap = ob_get_clean();

	ob_start();
	do_action( 'genesis_before_footer' );
	$genesis_before_footer = ob_get_clean();

	ob_start();
	do_action( 'genesis_footer' );
	$genesis_footer = ob_get_clean();

	ob_start();
	do_action( 'genesis_after_footer' );
	$genesis_after_footer = ob_get_clean();

	ob_start();
	do_action( 'genesis_after' );
	$genesis_after = ob_get_clean();

	// By default we return only fields that have output, but this filter
	// allows the option to return all fields even if they are empty.
	$return_empty = apply_filters( 'genesis_rest_api_return_empty_hooks', false );

	// Only return hooks that have output unless the filter is set otherwise.
	if ( '' !== $genesis_before || $return_empty ) {
		$data->data['genesis_before'] = $genesis_before;
	}
	if ( '' !== $genesis_before_header || $return_empty ) {
		$data->data['genesis_before_header'] = $genesis_before_header;
	}
	if ( '' !== $genesis_header || $return_empty ) {
		$data->data['genesis_header'] = $genesis_header;
	}
	if ( '' !== $genesis_site_title || $return_empty ) {
		$data->data['genesis_site_title'] = $genesis_site_title;
	}
	if ( '' !== $genesis_site_description || $return_empty ) {
		$data->data['genesis_site_description'] = $genesis_site_description;
	}
	if ( '' !== $genesis_header_right || $return_empty ) {
		$data->data['genesis_header_right'] = $genesis_header_right;
	}
	if ( '' !== $genesis_after_header || $return_empty ) {
		$data->data['genesis_after_header'] = $genesis_after_header;
	}
	if ( '' !== $genesis_before_content_sidebar_wrap || $return_empty ) {
		$data->data['genesis_before_content_sidebar_wrap'] = $genesis_before_content_sidebar_wrap;
	}
	if ( '' !== $genesis_before_content || $return_empty ) {
		$data->data['genesis_before_content'] = $genesis_before_content;
	}
	if ( '' !== $genesis_before_loop || $return_empty ) {
		$data->data['genesis_before_loop'] = $genesis_before_loop;
	}
	if ( '' !== $genesis_loop || $return_empty ) {
		$data->data['genesis_loop'] = $genesis_loop;
	}
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
	if ( '' !== $genesis_after_loop || $return_empty ) {
		$data->data['genesis_after_loop'] = $genesis_after_loop;
	}
	if ( '' !== $genesis_before_sidebar_widget_area || $return_empty ) {
		$data->data['genesis_before_sidebar_widget_area'] = $genesis_before_sidebar_widget_area;
	}
	if ( '' !== $genesis_after_sidebar_widget_area || $return_empty ) {
		$data->data['genesis_after_sidebar_widget_area'] = $genesis_after_sidebar_widget_area;
	}
	if ( '' !== $genesis_before_footer || $return_empty ) {
		$data->data['genesis_before_footer'] = $genesis_before_footer;
	}
	if ( '' !== $genesis_footer || $return_empty ) {
		$data->data['genesis_footer'] = $genesis_footer;
	}
	if ( '' !== $genesis_after_footer || $return_empty ) {
		$data->data['genesis_after_footer'] = $genesis_after_footer;
	}
	if ( '' !== $genesis_after || $return_empty ) {
		$data->data['genesis_after'] = $genesis_after;
	}

	return $data;
}
