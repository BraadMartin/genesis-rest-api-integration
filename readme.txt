=== Genesis REST API Integration ===
Contributors: Braad
Donate link: http://braadmartin.com/
Tags: genesis, rest, api, integration, framework, post, object, response
Requires at least: 4.0
Tested up to: 4.2.3
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds content output from the Genesis Framework hooks to the post object response when using the WP REST API v2.

== Description ==

If your site uses the Genesis framework, it's likely that you're making using of Genesis hooks like genesis_before_content or genesis_entry_footer. When using the WP REST API any content that has been added to these hooks is not included in the respose by default.

This plugin filters the response from the API to include any content added via Genesis hooks. It adds this content to the post object response, which means you don't have to do a separate request or modify your request to get the data. Simply ask for a post in the standard way like /wp-json/wp/v2/posts/1 and you'll get the extra content from the Genesis hooks.

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

**NOTE:** Returning formatted HTML over the REST API is not the best way to make use of a REST API to build a website. It would be preferable to return only the raw object data and build all of your HTML on the client side using the object data. The best use case for this plugin is probably for existing websites that were built using Genesis and already have a bunch of content on Genesis hooks, but if you're starting fresh you might consider building a deeper integration with the WP REST API so that you can keep formatted HTML out of the response data.

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

= 1.0.0 =
* First Release

== Upgrade Notice ==

= 1.0.0 =
* First Release