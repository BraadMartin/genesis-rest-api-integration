=== Genesis REST API Integration ===
Contributors: Braad
Donate link: http://braadmartin.com/
Tags: genesis, rest, api, integration, framework, post, object, response
Requires at least: 4.0
Tested up to: 4.3
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds content output from the Genesis framework hooks to the response data for posts, pages, and custom post types when using the WP REST API v2.

== Description ==

If your site uses the Genesis framework, it's likely that you're making use of Genesis hooks like genesis_before_content or genesis_entry_footer. When using the WP REST API, any content that has been added to these hooks is not included in the respose by default.

This plugin filters the response from the API to include any content added via Genesis hooks. It adds this content to the post object response, which means you don't have to do a separate request or modify your request to get the data. Simply ask for a post in the standard way like /wp-json/wp/v2/posts/1 and you'll get the extra content from the Genesis hooks.

[Example diff showing the response with and without the plugin active](https://www.diffchecker.com/v9ttyrq7)

The Genesis hook content that is returned is generated with respect to the context of the specific post being retrieved. The context will match what it would be on the single post page for the given post, page, or custom post type. This means that if you've got some code like this:

	add_action( 'genesis_before_entry', 'mytheme_output_special_content' );
	function mytheme_output_special_content() {
		// Only on posts in a specific category
		if ( in_category( '5' ) ) {
			echo 'Some Content';
		}
	}

You'll get back 'Some Content' as the value of the property 'genesis_before_entry' on the response object only if the post is actually in category 5. All of the standard conditionals like is_archive(), is_single(), has_tag(), etc. will be respected.

By default this plugin only adds properties to the response object for hooks that have output, but you can modify this behavior using a filter like so:

	add_filter( 'genesis_rest_api_return_empty_hooks', '__return_true' );

By default this plugin adds the extra properties to posts, pages, and all custom post types, but you can specify which post types you want to include the extra data with another filter:

	add_filter( 'genesis_rest_api_supported_post_types', 'mytheme_genesis_rest_api_supported_post_types' );
	function mytheme_genesis_rest_api_supported_post_types( $post_types ) {

		// Only add data to the movie post type.
		$post_types = array( 'movie' );

		return $post_types;
	}

**NOTE:** In v2 of the REST API, custom post types need to have the extra properties `show_in_rest`, `rest_base`, and `rest_controller_class` set on their post type objects in order for the API to start serving data for them. I've included a filter in this plugin for adding these properties so that you can declare REST API support for CPTs and add the extra Genesis data in one step, but the filter is off by default and won't override properties that are already set (because if core doesn't assume you want all your CPTs publicly accessible by default, I don't think I should either). To turn this functionality on, use the included filter like this:

	add_filter( 'genesis_rest_api_register_cpt_api_support', '__return_true' );

If you do this, you're CPTs will be available at routes that match the official name of the post type found on the post type object, so if your post type is 'movie' and you have a movie with an id of 8, the movie will be accessible at /wp-json/v2/movie/8. It's probably a better idea to match the core convention of /posts/ and /pages/, so if you want to make the route available at /movies/ instead of /movie/ you just need to specifically set the `rest_base` property like so:

	add_action( 'init', 'mytheme_change_cpt_routes', 11 );
	function mytheme_change_cpt_routes() {

		global $wp_post_types;

		$wp_post_types['movie']->rest_base = 'movies';
	}

Here's the full list of all the Genesis hooks that are currently supported:

	genesis_before
	genesis_before_header
	genesis_site_title
	genesis_site_description
	genesis_header_right
	genesis_after_header
	genesis_before_content_sidebar_wrap
	genesis_before_content
	genesis_before_loop
	genesis_before_while
	genesis_before_entry
	genesis_entry_header
	genesis_before_entry_content
	genesis_entry_content
	genesis_after_entry_content
	genesis_entry_footer
	genesis_after_entry
	genesis_after_endwhile
	genesis_after_loop
	genesis_before_sidebar_widget_area
	genesis_after_sidebar_widget_area
	genesis_after_content_sidebar_wrap
	genesis_before_footer
	genesis_footer
	genesis_after_footer
	genesis_after

And naturally, there is a filter to control which hooks are supported:

	add_filter( 'genesis_rest_api_supported_hooks', 'mytheme_genesis_rest_api_supported_hooks' );
	function mytheme_genesis_rest_api_supported_hooks( $genesis_hooks ) {

		// Only include certian hooks.
		$genesis_hooks = array(
			'genesis_before_entry',
			'genesis_after_entry',
		);

		return $genesis_hooks;
	}

**NOTE:** The hooks genesis_header and genesis_loop are not included by default because they mostly call other hooks that are included, but you can always add them back in using the genesis_rest_api_supported_hooks filter.

**NOTE:** Returning formatted HTML over the REST API is not the best way to make use of a REST API to build a website. It would be preferable to return only the raw object data and build all of your HTML on the client side using the object data. With this plugin you can do exactly this with a little help from `json_encode`:

	add_action( 'genesis_before_entry_content', 'mytheme_pass_array' );
	function mytheme_pass_array() {

		if ( is_single( 124 ) ) {
			$json = array(
				'a_key' => 'some value',
				'another_key' => 'another value',
			);
			echo json_encode( $json );
		}
	}

Or the object version:

	add_action( 'genesis_after_entry_content', 'mytheme_pass_object' );
	function mytheme_pass_object() {

		if ( in_category( 2 ) ) {
			$json = new stdClass();
			$json->some_key = 'some value';
			$json->another_key = 'another value';
			echo json_encode( $json );
		}
	}

Passing arbitrary objects and arrays like this really opens up some interesting possibilities.

If you have any ideas for new features or find a bug, please open an issue [on Github](https://github.com/BraadMartin/genesis-rest-api-integration "Genesis REST API Integration"). Pull requests are also encouraged :).

== Installation ==

= Manual Installation =

1. Upload the entire `/genesis-rest-api-integration` directory to the `/wp-content/plugins/` directory.
1. Activate Genesis REST API Integration through the 'Plugins' menu in WordPress.

= Better Installation =

1. Go to Plugins > Add New in your WordPress admin and search for Genesis REST API Integration.
1. Click Install.

== Frequently Asked Questions ==

= How does it work? =

The WP REST API includes a filter on the response data it returns, and this plugin uses that filter to add the Genesis hook data. The plugin sets the context based on the post being requested and then runs through the full genesis loop once, capturing the output from the Genesis hooks and setting up the response data to be returned along the way.

== Changelog ==

= 1.1.0 =
* Added better support for working with custom post types
* Now you can register basic CPT support for the REST API using a filter
* Switched the primary initialization hook to rest_api_init

= 1.0.0 =
* First Release

== Upgrade Notice ==

= 1.1.0 =
* Added better support for working with custom post types
* Now you can register basic CPT support for the REST API using a filter
* Switched the primary initialization hook to rest_api_init

= 1.0.0 =
* First Release