<?php
/* IMPORTANT: read the FAQ - no need to edit this file */

/* main constants */

if( !defined( 'BP_OEMBED_DISABLE_ACTIVITY' ) )
	add_filter( 'bp_get_activity_content_body', 'ray_bp_oembed', 9 );

if( !defined( 'BP_OEMBED_DISABLE_ACTIVITY_REPLIES' ) )
	add_filter( 'bp_get_activity_content', 'ray_bp_oembed', 9 );

if( !defined( 'BP_OEMBED_DISABLE_FORUM_POSTS' ) )
	add_filter( 'bp_get_the_topic_post_content', 'ray_bp_oembed', 9 );

if( !defined( 'BP_OEMBED_DISABLE_GROUP_DESCRIPTION' ) )
	define('BP_OEMBED_DISABLE_GROUP_DESCRIPTION', true);

	if( BP_OEMBED_DISABLE_GROUP_DESCRIPTION == false )
		add_filter( 'bp_get_group_description', 'ray_bp_oembed', 9 );
		
if( !defined( 'BP_OEMBED_DISABLE_XPROFILE' ) )
	define( 'BP_OEMBED_DISABLE_XPROFILE', true );

	if( BP_OEMBED_DISABLE_XPROFILE == false )
		add_filter('bp_get_the_profile_field_value','ray_bp_oembed', 9);


/* filters for embed content */

// resize the embed item
add_filter( 'embed_handler_html', 'ray_bp_oembed_resize' );
add_filter( 'embed_oembed_html', 'ray_bp_oembed_resize' );

// add wmode = transparent to embed item
add_filter( 'embed_handler_html', 'ray_bp_oembed_wmode', 1 );
add_filter( 'embed_oembed_html', 'ray_bp_oembed_wmode', 1 );

// if embed content is javascript, return url on an AJAX request
add_filter( 'embed_handler_html', 'ray_bp_oembed_is_js', 1, 2 );
add_filter( 'embed_oembed_html', 'ray_bp_oembed_is_js', 1, 2 );


/* misc */

// put a specific character in front of a link to prevent it from being parsed
if( !defined( 'BP_OEMBED_WHITELIST_CARAT' ) )
	define( 'BP_OEMBED_WHITELIST_CARAT', '^' );

// since BP doesn't offer a way to rescue deleted forum posts like bbPress,
// let's remove the oembed post cache from bbPress when a forum post is deleted
if ( bp_is_active( 'forums' ) )
	add_action( 'bb_delete_post', 'ray_bp_oembed_delete_forum_postmeta' );
	
// helper to check if an AJAX request is being made
if( !defined( 'IS_AJAX' ) )
	define( 'IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' );

// legacy settings
require_once('bp-oembed-legacy.php');

// blacklist hyperlinks (or domains or whatever)
$bp_oembed['blacklist'][] = 'href=';


/* really stop editing! */

function ray_bp_oembed($content) {
	global $bp_oembed, $wp_embed;

	// WP(MU) 2.9 oEmbed check
	if( !function_exists('wp_oembed_get') )
		return $content;

	// match URLs - could use some work
//	preg_match_all( '@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', $content, $matches );
//	preg_match_all('`\S*?(https?://[\w#$&+,\/:;=?@.-%]+)[^\w#$&+,\/:;=?@.-%]*?`i', $content, $matches);
	preg_match_all('`\S*?(https?://[^\s"]+)\s*?`i', $content, $matches);
//	preg_match_all( '|^\S*(https?://[^\s"]+)\s*$|im', $content, $matches );

	// debug regex
	//if(!empty($matches[0]))
	//	print_r($matches[0]);

	// if there are no links to parse, return $content now!
	if(empty($matches[0]))
		return $content;

	$links = $matches[0];

	// WP_Embed handlers = not oEmbed providers, but offer embed support via regex callback
	// sort these embed handlers by priority
	ksort( $wp_embed->handlers );

	// set up a new WP oEmbed object
	require_once( ABSPATH . WPINC . '/class-oembed.php' );
	$wp_oembed = _wp_oembed_get_object();

	foreach ( (array)$links as $url ) {
		$is_wpembed_link = false;

		// check url with blacklist, if url matches any blacklist item, skip from parsing
		foreach ($bp_oembed['blacklist'] as $blacklist_item) {
			if (strpos($url,$blacklist_item) !== false) { 
				continue 2;
			}
		}

		// whitelist links beginning with "^" character (or whatever is defined), so they stay as links!
		if ( strpos( $url, BP_OEMBED_WHITELIST_CARAT . 'http://' ) !== false ) {
			$whitelist_url = substr( $url, 1 );
			$replace = apply_filters( 'bp_oembed_whitelist_url', '<a href="' . $whitelist_url . '" rel="nofollow">' . $whitelist_url . '</a>', $whitelist_url );

			// fake whitelist item as embeddable so it'll skip embed parsing
			$is_wpembed_link = true;
		}

		// setup default size, otherwise fallback to "Media" width size set in WP admin area
		if( defined( 'BP_OEMBED_ACTIVITY_PERMALINK_WIDTH' ) )
			$attr['width'] = BP_OEMBED_ACTIVITY_PERMALINK_WIDTH;
	
		$attr = wp_parse_args( $attr, wp_embed_defaults() );

		// check to see if url matches list of WP_Embed handlers first
		// we don't need to cache WP Embed handlers since this doesn't require pinging an external service
		if ( !$is_wpembed_link  ) :
			foreach ( $wp_embed->handlers as $priority => $handlers ) {
				foreach ( (array)$handlers as $id => $handler ) {
					if ( preg_match( $handler['regex'], $url, $matches ) && is_callable( $handler['callback'] ) ) {
						if ( false !== $return = call_user_func( $handler['callback'], $matches, $attr, $url, $rawattr ) ) {
							$replace = apply_filters( 'embed_handler_html', $return, $url, $attr );
							$is_wpembed_link = true;
							continue 2;
						}
					}
				}			
			}
		endif;	

		// if url doesn't match WP_Embed handlers, let's check oEmbed!
		if( !$is_wpembed_link ) :

			// Check to see if url matches list of known WP oEmbed providers
			$is_oembed_link = false;
			foreach ( (array)$wp_oembed->providers as $provider_matchmask => $provider ) {
				$regex = ( $is_regex = $provider[1] ) ? $provider_matchmask : '#' . str_replace( '___wildcard___', '(.+)', preg_quote( str_replace( '*', '___wildcard___', $provider_matchmask ), '#' ) ) . '#i';
	
				if ( preg_match( $regex, $url ) )
					$is_oembed_link = true;
			}

			// If url doesn't match a WP oEmbed provider, skip url
			if ( !$is_oembed_link )
				continue;			

			// grab oEmbed cache depending on BP component
			// not the prettiest code!
			$cachekey = '_oembed_' . md5($url);

			if ( bp_is_activity_component() && !bp_is_blog_page() || ( bp_is_activity_front_page() && bp_is_front_page() ) )
				$cache = bp_activity_get_meta( bp_get_activity_id(), $cachekey );
			elseif ( bp_is_group_forum_topic() )
				$cache = bb_get_postmeta( bp_get_the_topic_post_id(), $cachekey );
			elseif( bp_current_component() == 'groups' )
				$cache = groups_get_groupmeta( bp_get_group_id(), $cachekey );
			elseif( bp_current_component() == 'profile' )
				$cache = get_usermeta( $bp->displayed_user->id, $cachekey );

			// cache check
			if ( !empty($cache) ) {
				$replace = apply_filters( 'embed_oembed_html', $cache, $url );
			}
			// if no cache, let's start the show!
			else {
				// process url to oEmbed
				$oembed = wp_oembed_get($url);

				$replace = str_replace('
','',$oembed); // fix Viddler line break in <object> tag

				// save oEmbed cache depending on BP component
				// the same "not prettiness!"
				if ( bp_is_activity_component() && !bp_is_blog_page() || ( bp_is_activity_front_page() && bp_is_front_page() ) )
					bp_activity_update_meta( bp_get_activity_id(), $cachekey, $replace );
				elseif ( bp_is_group_forum_topic() )
					bb_update_postmeta(bp_get_the_topic_post_id(), $cachekey, $replace);
				elseif( bp_current_component() == 'groups' )
					groups_update_groupmeta(bp_get_group_id(), $cachekey, $replace);
				elseif( bp_current_component() == 'profile' )
					update_usermeta( $bp->displayed_user->id, $cachekey, $replace );				
				
				//add_filter('oembed_dataparse','ray_oembed_parse', 10, 3);

				$replace = apply_filters( 'embed_oembed_html', $oembed, $url );

			}
		
		endif;

		$content = str_replace($url, $replace, $content);
	}

	return $content;
}


function ray_bp_oembed_resize( $embed ) {

	// do not override settings for WP pages
	if ( bp_is_blog_page() && !bp_is_activity_front_page() && !IS_AJAX ) {
		return $embed;
	}

	if ( bp_is_activity_permalink() ) {
		if( !defined( "BP_OEMBED_ACTIVITY_PERMALINK_WIDTH" ) )
			return $embed;	
	
		$resize_width = BP_OEMBED_ACTIVITY_PERMALINK_WIDTH;
	}
	elseif ( bp_is_group_forum_topic() ) {
		if( !defined( "BP_OEMBED_FORUM_POST_WIDTH" ) )
			return $embed;	
	
		$resize_width = BP_OEMBED_FORUM_POST_WIDTH;
	}
	elseif( bp_current_component == 'groups' ) {
		if( !defined( "BP_OEMBED_GROUP_DESC_WIDTH" ) )
			return $embed;	
	
		$resize_width = BP_OEMBED_GROUP_DESC_WIDTH;
	}
	elseif( bp_current_component == 'profile' ) {
		if( !defined( "BP_OEMBED_XPROFILE_WIDTH" ) )
			return $embed;	
	
		$resize_width = BP_OEMBED_XPROFILE_WIDTH;
	}
	else {
		if( !defined( "BP_OEMBED_ACTIVITY_STREAM_WIDTH" ) )
			return $embed;

		$resize_width = BP_OEMBED_ACTIVITY_STREAM_WIDTH;
	}

	preg_match_all("/width=\"'?([^\"]*)\"'?/", $embed, $width_matches);
	preg_match_all("/height=\"'?([^\"]*)\"'?/", $embed, $height_matches);	
	
	$width = implode( '', array_unique($width_matches[1]) );
	$height = implode( '', array_unique($height_matches[1]) );
	
	if ($width)
		$embed = str_replace( $width, $resize_width, $embed );

	if ($height)
		$embed = str_replace( $height, round( $height * $resize_width / $width ) , $embed );
	
	// let's help designers! give them a CSS class to style / reposition content
	if ( !bp_is_activity_permalink() ) {
		$embed = '<div class="oembed-item">'.$embed.'</div>';
	
	}	

	return $embed;
}


function ray_bp_oembed_wmode( $embed ) {
	if ( strpos( $embed, '<param' ) !== false ) {
		if ( strpos( $embed, 'wmode' ) === false ) {	
			$embed = str_replace( '<embed', '<embed wmode="transparent" ', $embed );

			if ( strpos ( $embed, 'value="transparent"' ) === false )
				$embed = preg_replace( '/param>/', 'param><param name="wmode" value="transparent" />', $embed, 1);
		}
	}

	return $embed;
}


function ray_bp_oembed_is_js( $embed, $url ) {
	if ( strpos( $embed, '<script' ) !== false ) {
		if( IS_AJAX )
			return $url;
	}

	return $embed;
}


function ray_bp_oembed_delete_forum_postmeta($post_id) {
	global $bbdb;
	
	// let's also get rid of the "pingback_queued" meta
	bb_delete_postmeta( $post_id, 'pingback_queued' );

	$object_type = 'bb_post';
	$meta_key = '\_oembed\_%';

	$meta_sql = $bbdb->prepare( "SELECT `meta_id` FROM `$bbdb->meta` WHERE `object_type` = %s AND `object_id` = %d AND `meta_key` LIKE %s", $object_type, $post_id, $meta_key );

	if ( !$matches = $bbdb->get_results( $meta_sql ) )
		return false;

	foreach ($matches as $match) {
		$bbdb->query( $bbdb->prepare( "DELETE FROM `$bbdb->meta` WHERE `meta_id` = %d", $match->meta_id ) );	
	}
}


// TODO: change latest update and "in reply to" update to link to BP activity permalink if there's an embeddable link
// would require duplicating most of the main function again...
// need to think of an efficient way to do this

// alternative is to see if any link is posted and then simply link to the BP activity permalink
// why? because there's a character limit involved so that might be the way to go.
function ray_bp_oembed_activity_parent_content( $content ) {
	return $content;
}
//add_filter( 'bp_get_activity_parent_content', 'ray_bp_oembed_activity_parent_content' );
//add_filter( 'bp_get_activity_latest_update', 'ray_bp_oembed_activity_parent_content' );


// TODO: bp-oembed 0.7 thumbnail code - needs a lot of work
function ray_oembed_parse($return, $data, $url) {
	$thumb = ( !empty($data->thumbnail_url) ) ? $data->thumbnail_url : '';
	$return = '<img src="' . esc_attr( clean_url( $data->thumbnail_url ) ) . '" alt="' . esc_attr($title) . '" width="' . esc_attr($data->thumbnail_width) . '" height="' . esc_attr($data->thumbnail_height) . '" />';
	
	return $return;
}

?>