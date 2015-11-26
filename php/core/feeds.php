<?php

function pw_get_feed_by_id( $feed_id ){
	$feeds = pw_get_option( array( 'option_name'	=>	PW_OPTIONS_FEEDS ) );
	if( empty( $feeds ) )
		return false;

	foreach( $feeds as $feed ){
		if( $feed['id'] == $feed_id )
			return $feed;
	}
	return false;
}

function pw_get_feed_by_context( $context = array() ){
	// Gets a context-absed feed settings for the given context array

	global $pw;
	$feed = array();

	// Get the context key of the feed settings
	$context_feeds = pw_get_option( array(
		'option_name'	=>	PW_OPTIONS_FEED_SETTINGS,
		'key'			=> 'context',
		));

	// If there's no context key
	if( empty( $context_feeds ) )
		// Return empty array
		return array();

	// If no context parameter is given
	if( empty( $context ) )
		// Get the global context
		$context = $pw['view']['context'];

	// Add default context to the beginning of the array
	// This will enable the default feed setting to work globally
	$context = array_merge( array('default'), $context );

	//pw_log( "CONTEXT : " . json_encode($context) );

	// Iterate through each of the contexts
	foreach( $context as $c ){
		// Get the context feed coorosponding to the current context
		$check_feed = _get( $context_feeds, $c );
		// If it exists
		if( $check_feed != false )
			// Set it as the feed
			$feed = $check_feed;
	}

	if( empty( $feed ) )
		return array();
	else
		return $feed;

}

/**
 * @depreciated Use pw_feed() instead.
 * @see pw_feed()
 */
function pw_live_feed( $vars = array(), $return_empty = true ){
	$vars['return_empty'] = $return_empty;
	return pw_feed( $vars );
}

/**
 * Used to insert a feed into the DOM.
 * Prints the `script` and `html` tags for a defined feed.
 *
 * @param array $vars An array of variables.
 * @return string The feed elements.
 */
function pw_feed( $vars = array() ){
	global $post;
	global $pw;

	/// $VARS : (STRING) ///
	// Check if the $vars is a string or if a feed ID is defined in the vars
	$feed_id = ( is_string( $vars ) ) ? $vars : _get( $vars, 'feed_id' ); 

	if( !empty( $feed_id ) ){
		// Check if it's referencing a known feed ID
		$feed = pw_get_feed_by_id( $feed_id );
		if( !$feed )
			return false;
	} 
	else{
		$feed = array();
		$feed_id = 'pwFeed_' . pw_random_string();
	}

	pw_set_microtimer($feed_id);

	// Run filters on the feed vars
	$vars = apply_filters( 'pw_feed', $vars );

	///// DEFAULT VARS /////
	$default_vars = array(
		'element'		=>	'div',
		'directive'		=>	'pw-feed',
		'feed_id'		=>	$feed_id,
		'classes'		=>	'feed',
		'attributes'	=>	'',
		'echo'			=>	true,
		'feed'			=>	$feed,
		'aux_template'	=>	null,
		'return_empty'	=> true,
		'preload_templates' => true,
		);

	if( is_array( $vars ) )
		$vars = array_replace_recursive( $default_vars, $vars );
	else
		$vars = $default_vars;
	
	extract( $vars );

	//pw_set_microtimer('pw_live_feed-'.$feed_id);

	///// DEFAULT FEED /////
	$default_feed = array(
		'feed_outline'		=>	array(),
		'preload'			=>	10,
		'load_increment' 	=> 	10,
		'offset'			=>	0,
		'order_by'			=>	'-post_date',
		'view'	=>	array(
			'current' 	=> 'list',
			'options'	=>	array( 'list' ),
			),
		//'query' 		=> 	$default_query,
		'aux_template'	=>	'seo-list',
		'options'		=>	array(
			'galleries'	=>	array(
				'include_galleries'	=>	false,
				'move_galleries'	=>	true,
				'parent_post'		=>	true,
				'require_image'		=>	false,
				'include_posts'		=>	true,
				),
			),
		'blocks'	=>	false,

		/* BLOCKS OBJECT MODEL :
		array(
			'offset'	=>	0,
			'increment'	=>	3,
			'max' 		=> 	50,
			'template' 	=> 	'widget-grid',
			'classes'	=>	'x-wide',
			'widgets'	=>	array(
				'sidebar'	=>	'home-page-sidebar',
				),
			)
		*/

		// 'cache'	=>	array(
		//		'posts'	=>	true, 		// determines whether the posts or just outline is cached
		//		'interval'	=>	5000,
		//		),

		);


	///// FEED SETTINGS /////

	// FILTER : DEFAULT FEED
	// Allow themes to set default feed settings
	$default_feed = apply_filters( PW_FEED_DEFAULT, $default_feed );

	// SETTINGS FROM CONTEXT
	// If any context-based feed settings are specified, use those to over-ride
	$context_feed = pw_get_feed_by_context();

	// MERGE DEFAULTS WITH CONTEXT SETTINGS
	// Over-ride default settings with context feed settings
	$default_feed = array_replace_recursive( $default_feed, $context_feed );

	// MERGE WITH INPUT FEED SETTINGS
	// Over-ride default settings with provided settings
	$feed = array_replace_recursive( $default_feed, $feed );

	// FILTER : OVERRIDE FEED
	// Allow themes to override feed settings
	$feed = apply_filters( PW_FEED_OVERRIDE, $feed );

	/**
	 * If the feed is empty,
	 * Set the default query values from the Postworld globals	
	 */
	if( !isset( $feed['query'] ) || empty( $feed['query'] ) ){
		$feed['query'] = $pw['query'];
	}

	

	/**
	 * Set the default query variables
	 * To create a predictable and good performance result.
	 */
	$default_query = array(
		'post_status'		=>	'publish',
		'post_type'			=>	'post',
		'fields'			=>	'preview',
		'posts_per_page'	=>	200
		);
	$default_query = apply_filters( 'pw_feed_default_query', $default_query );
	$feed['query'] = array_replace_recursive( $default_query, $feed['query'] );
		
	/**
	 * Set the default field model based on the current view name.
	 *
	 * @example To register a field model for a view, see pw_register_post_field_model()
	 */
	$query_fields = pw_get_field_model( 'post', $feed['view']['current'] );
	if( $query_fields !== false )
		$feed['query']['fields'] = $query_fields;

	/**
	 * Set and filter the fields
	 * @example Filter name for, list view : pw_fields_view_list, grid view: pw_fields_view_grid
	 */
	$feed['query']['fields'] = apply_filters( 'pw_fields_view_'.$feed['view']['current'], $feed['query']['fields'] );

	/**
	 * Prepare the query by running various filters over
	 * Known and extendable smart query variables.
	 * @example See filters for pw_prepare_query
	 */
	$feed['query'] = apply_filters( 'pw_prepare_query', $feed['query'] );

	// Get the live feed data
	$feed_data = pw_get_live_feed( $feed );

	// Merge feed data with feed settings
	$feed = array_replace_recursive( $feed, $feed_data );

	// If no posts, and not set to return empty, return false
	if( empty($feed['feed_outline']) && $vars['return_empty'] == false )
		return false;

	///// BLOCKS : GET WIDGET DATA /////
	$widgets = array();
	$sidebar_id = _get( $feed, 'blocks.widgets.sidebar' );
	if( is_string( $sidebar_id ) )
		$widgets = pw_get_sidebar( $sidebar_id );
	$has_widgets = ( is_array($widgets) && !empty($widgets) ) ? true : false;

	//pw_log( "widgets : " . json_encode($widgets) );

	///// GENERATE OUTPUT /////
	$output = '';

	///// PRELOAD TEMPLATES /////
	if($vars['preload_templates'] === true){
		$current_view = $feed['view']['current'];

		///// FEEDS /////
		// Preload Feed template
		$feed_ng_template = pw_get_ng_template(array(
			'subdir' => 'feeds',
			'id' => 'feed-'.$current_view,
			));
		$output .= $feed_ng_template . "\n";

		///// POSTS /////
		// Preload Post template(s)
		$post_ng_template = pw_get_ng_template(array(
			'subdir' => 'posts',
			'id' => $current_view,
			'post_type' => $feed['query']['post_type']
			));
		$output .= $post_ng_template . "\n";

		///// BLOCKS /////
		$blocks_template_id = _get( $feed, 'blocks.template' );
		if( $blocks_template_id !== false ){
			// Preload Blocks template(s)
			$block_ng_template = pw_get_ng_template(array(
				'subdir' => 'blocks',
				'id' => $blocks_template_id,
				));
			$output .= $block_ng_template . "\n";
		}


	}

	// PRELOAD BLOCKS
	// templates/blocks/widget-grid.html
	
	//pw_log($feed_ng_template);
	//pw_log( $feed['view']['current'] . ' - ' . $feed_template );
	//pw_log( 'feed', $feed );
	
	// Print front-loaded data
	$output  .= '<script>';

	// FEED
	$output .= 'pw.feeds["'.$feed_id.'"] = '. json_encode($feed) .';';

	// WIDGETS
	if( $has_widgets )
		$output .= 'pw.widgets["'.$sidebar_id.'"] = '. json_encode($widgets) .';';

	$output .= '</script>';

	// HTML
	$output .= '<'.$element.' '.$directive.'="'.$feed_id.'" class="'.$classes.'" '.$attributes.'></'.$element.'>';


	///// AUXILLARY FEED /////
	if( !empty($aux_template) ){
		// Get the specified template path
		$template = pw_get_template ( 'feeds', $aux_template, 'php', 'dir' );
		// If a template is found
		if( $template ){
			// Add it to the output
			$output .= pw_ob_include( $template, $feed );
		}

	}

	//pw_log_microtimer('pw_live_feed-'.$feed_id);

	//pw_log_microtimer($feed_id);

	///// OUTPUT /////
	if( $echo )
		echo $output;
	else
		return $output;

	return;
}


/**
 * Adds fallback exception handling to template preloading fallback
 */
add_filter( 'pw_get_ng_template_fallback', 'pw_get_ng_template_fallback_filter' );
function pw_get_ng_template_fallback_filter( $vars ){

	// Set default feed template
	if( $vars['subdir'] === 'feeds' )
		$vars['id'] = "feed-list";
	
	return $vars;

}


function pw_get_live_feed ( $vars ){

	// TODO : Cleanup logic pattern in this function

	//pw_log($vars);

	extract($vars);

	// Defaults
	if( !isset( $preload ) )
		$preload = 10;
	if( !isset( $options ) )
		$options = array();

	// Sanitize
	$preload = (int) $preload;

	/// GET FEED OUTLINE FROM QUERY ///
	if( empty( $feed_outline ) && !empty( $query ) && empty( $posts ) ){
		// Get the Feed Outline from the query
		$query = $vars["query"];
		$feed_outline = pw_feed_outline( $query );
	}

	/// GET FEED OUTLINE FROM RELATED POSTS ///
	if( isset( $related_posts ) && !empty( $related_posts ) && empty( $posts ) ){
		$feed_outline = pw_related_posts( $related_posts );
	}
	
	// If the posts have contents
	if( !empty( $posts ) ){
		// Add the post's IDs to preload_posts array
		$feed_outline = array();
		foreach( $posts as $post ){
			if( is_object( $post ) )
				$feed_outline[] = $post->ID;
			elseif( is_array( $post ) )
				$feed_outline[] = $post['ID'];
		}
		// Set all in the outline as preloaded
		$preload_posts = $feed_outline;
	
	}

	// If the feed outline has contents
	else if( !empty( $feed_outline ) ){
		// Select which posts to preload
		$preload_posts = array_slice( $feed_outline, 0, $preload );
		// Default Fields
		if( !isset( $query["fields"] ) || empty( $query["fields"] ) )
			$query["fields"] = 'preview';
		// Preload selected posts
		$posts = pw_get_posts( $preload_posts, $query["fields"], $options );
	}

	else{
		$posts = array();
		$preload_posts = array();
	}

	$vars = array(
		"feed_id" 		=> 	_get($vars,'feed_id'),
		"query" 		=> 	$vars["query"],
		"feed_outline" 	=> 	$feed_outline,
		"loaded"		=>	$preload_posts,
		"preload" 		=> 	count($posts),
		"posts"			=>	$posts,
		"options"		=>	$options,
		);

	return $vars;

}

function pw_feed_outline ( $query ){
	// Generates an array of post_ids based on the $query

	///// CACHING LAYER /////
	if( in_array( 'post_cache', pw_enabled_modules() ) ){
		$cache_hash = hash( 'sha256', json_encode( $query ) );
		$get_cache = pw_get_cache( array( 'cache_hash' => $cache_hash ) );
		if( !empty( $get_cache ) ){
			return json_decode($get_cache['cache_content'], true);
		}
	}
		
	///// GET FEED OUTLINE /////
	$query["fields"] = "ids";
	$query_results = pw_query( $query );
	$post_ids = (array) $query_results->posts;
	$post_ids = pw_sanitize_numeric_array( $post_ids );

	///// CACHING LAYER /////
	if( in_array( 'post_cache', pw_enabled_modules() ) )
		pw_set_cache( array(
			'cache_type'	=>	'feed-outline',
			'cache_hash' 	=> 	$cache_hash,
			'cache_content'	=>	json_encode($post_ids),
			));

	return $post_ids;
}


function pw_merge_galleries( $posts, $options ){
	// Take in a post array
	// Return a post array with all gallery posts merged into the main feed
	/*
	$options = array(
		'move_galleries'	=>	[ boolean ],	// Delete galleries in posts
		'require_image'		=>	[ boolean ],	// Require posts to have an image
		'include_posts'		=>	[ boolean ],	// Include the posts too
		'max_posts'			=> 	[ integer ],	// Maxmimum number of posts total
		'parent_post'		=>	[ boolean ],	// Whether or not to insert the parent post of the gallery item into the gallery item as parent_post
		);
	*/

	///// SETUP DEFAULT OPTIONS /////
	$default_options = array(
		'move_galleries'	=>	true,
		'require_image'		=>	true,
		'include_posts'		=>	true,
		'max_posts'			=> 	0,
		'parent_post'		=> 	true,
		);
	$options = array_merge( $default_options, $options );

	///// PROCESS POSTS /////
	$newPosts = array();

	// Iterate through each post
	foreach( $posts as $post ){

		// Get the posts from the gallery
		$galleryPosts = pw_get_obj( $post, 'gallery.posts' );
		if( $galleryPosts == false ) $galleryPosts = array();

		///// OPTION : MOVE GALLERIES /////
		if( $options['move_galleries'] == true && !empty( $galleryPosts ) )
			// Clear the array
			$post['gallery']['posts'] = array();


		///// OPTION : REQUIRE IMAGE /////
		// If require image is on
		if( $options['require_image'] == true ){
			// Test if the post has an image
			$post_array = pw_require_image( array( $post ) );
			// If it failed the test
			if( empty( $post_array ) )
				// Empty the post
				$post = array();
		}


		///// OPTION : PARENT POST /////
		// Move the parent post into the gallery post as parent_post
		// If the option is turned on
		if( $options['parent_post'] == true &&
			// And there are gallery posts
			!empty( $galleryPosts ) ){
			// Setup new array
			$newGalleryPosts = array();
			// Iterate through each gallery post
			foreach( $galleryPosts as $galleryPost ){
				// Clone the post
				$parent_post = $post;
				// Remove the gallery posts object from the post
				$parent_post['gallery']['posts'] = array();
				// Add it under parent_post key
				$galleryPost['parent_post'] = $parent_post;
				// Add it to the new array
				$newGalleryPosts[] = $galleryPost;
			}
			// Overwrite the old galleryPosts with the new one
			$galleryPosts = $newGalleryPosts;
		}


		///// ADD : POST /////
		// If the post isn't empty
		if( !empty( $post ) &&
			// And include posts is true
			///// OPTION : INCLUDE POSTS /////
			$options['include_posts'] != false )
			// Add it to the posts array
			array_push( $newPosts, $post );


		///// ADD : GALLERY POSTS /////
		// Add the gallery posts to the new posts array
		$newPosts = array_merge( $newPosts, $galleryPosts );

		///// OPTION : MAX POSTS /////
		// If the maximum number of posts is reached already, stop here
		if( $options['max_posts'] != 0 &&
			$options['max_posts'] != false &&
			$options['max_posts'] &&
			count( $newPosts ) >= $options['max_posts'] ){
			// Slice the number of posts to the max number
			$newPosts = array_slice( $newPosts, 0, $options['max_posts'] );
			// Stop iterating here
			break;
		}
	}
	return $newPosts;

}



/*
1-pw_get_templates
2-load_feed
3-pw_cache_feed
4-pw_register_feed
5-pw_get_feed
*/

function add_new_feed( $feed_id, $feed_query ){
	global $wpdb;
	$wpdb->show_errors();
	$query = "insert into $wpdb->pw_prefix"."feeds values('$feed_id','".json_encode($feed_query)."',null,null,null,null)";
	//echo $query;
	$wpdb->query($query);
}

function pw_register_feed ( $args ){
	/*
		Description:

		Registers the feed in feeds table
		Process:

		If the feed_id doesn't appear in the wp_postworld_feeds table :

		Create a new row
		Enable write_cache
		Store $args['feed_query'] in the feed_query column in Postworld feeds table as a JSON Object

		If write_cache is true, run pw_cache_feed(feed_id)

		return : $args Array

		Parameters : $args

		feed_id : string

		feed_query : array

		default : none
		The query object which is stored in feed_query in feeds table, which is input directly into pw_query
		write_cache : boolean

		If the feed_id is new to the feeds table, set write_cache = true
		false (default) - Wait for cron job to update feed outline later, just update feed_query
		true - Cache the feed with method : run pw_cache_feed( $feed_id )
		Usage :

		$args = array (
		    'feed_id' => 'front_page_feed',
		    'write_cache'  => true,
		    'feed_query' => array(
		        // pw_query() $args
		    )
		);
		pw_register_feed ($args);

	 */
	global $wpdb;
	$wpdb->show_errors();


	 if($args['feed_id']){
	 	$feed_row = pw_get_feed($args['feed_id']);
		// echo json_encode($feed_row);
		if(!$feed_row){
			add_new_feed($args['feed_id'],$args['feed_query']);
				$args['write_cache'] =  TRUE;
				pw_cache_feed($args['feed_id']);

		}else{
			// echo ($args['write_cache']);
			//update feed query
			update_feed_query($args['feed_id'], $args['feed_query']);
			if (array_key_exists('write_cache', $args)){
				if( $args['write_cache'] ===  TRUE){
					pw_cache_feed($args['feed_id']);
				}
			}
		}
	 }
	return $args;
}

function update_feed_query($feed_id, $feed_query){

	global $wpdb;
	$wpdb->show_errors();
	$query = "update $wpdb->pw_prefix"."feeds set feed_query='".json_encode($feed_query)."' where feed_id='".$feed_id."'";
	//echo $query;
	$wpdb->query($query);
}

function pw_cache_feed ( $feed_id ){

	$feed_row = pw_get_feed($feed_id);
	if(!is_null($feed_row)){

		//echo ($feed_row->feed_query);
		$time_start = date("Y-m-d H:i:s");

		$feed_query_finalized  = finalize_feed_query($feed_row->feed_query);

		$feed_outline = pw_feed_outline($feed_query_finalized);
		$time_end = date("Y-m-d H:i:s");
		$timer = (strtotime( $time_end )-strtotime( $time_start))*1000;
		//echo json_encode($feed_outline);
		global $wpdb;
		$wpdb->show_errors();
		$query = "update $wpdb->pw_prefix"."feeds set feed_outline='".implode(",", $feed_outline)."',feed_json=null,time_start='$time_start',time_end='$time_end',timer='$timer' where feed_id='".$feed_id."'";
		//echo $query;
		$wpdb->query($query);
		return array('number_of_posts'=>count($feed_outline), 'feed_query'=> $feed_row->feed_query);
	}
}

function finalize_feed_query($feed_query_stringified){
	$pw_query_args = (array)json_decode($feed_query_stringified);
	if(isset($pw_query_args["tax_query"]))
		$pw_query_args["tax_query"][0]= get_object_vars(($pw_query_args["tax_query"][0])) ;

	return $pw_query_args;
}

function pw_get_feed ( $feed_id ){
	global $wpdb;
	$wpdb->show_errors();
	$query = "select * from $wpdb->pw_prefix"."feeds where feed_id='".$feed_id."'";
	$feed_row = $wpdb->get_row($query);
	return $feed_row;
}

function pw_load_feed ( $feed_id, $preload=0, $fields=null ){

	$feed_row = (array) pw_get_feed($feed_id);
	if($feed_row){
		$feed_row['feed_outline'] = array_map("intval", explode(",", $feed_row['feed_outline']));

		if($preload > 0){
			// Get the top preload post IDs
			$preload_posts = array_slice( $feed_row['feed_outline'], 0, $preload );

			if( $fields == null ){
				// Get the default fields
				$feed_query = (array)json_decode($feed_row['feed_query']);
				$fields = $feed_query['fields'];
			}

			$feed_row['posts'] = pw_get_posts($preload_posts,$fields);
		}

	}

	return (array)$feed_row;

}

function pw_print_feed( $vars ){

	// Load a cached feed
	if( isset($vars['feed_id']) ){
		// LOAD A CACHED FEED
		// Run Postworld Load Feed
		$load_feed = pw_load_feed( $vars['feed_id'], $vars['posts'], $vars['fields'] );
		$posts = $load_feed['posts'];

	} else if( isset($vars['feed_query']) ) {

		// LOAD A FRESH QUERY
		$feed_query = $vars['feed_query'];

		if( isset($vars['fields']) )
			// Override fields
			$feed_query['fields'] = $vars['fields'];

		$pw_query = pw_query( $feed_query );
		//return json_encode($pw_query);
		$posts = $pw_query->posts;

	} else if( isset( $vars['posts'] ) ) {
		$posts = $vars['posts'];

	} else {
		// RETURN ERROR
		return array('error' => 'No feed_id or feed_query defined.');
	}

	$pw_post = array();
	$post_html = "";

	// Iterate through each provided post
	foreach( $posts as $post ){

		// ID is a required field, to determine the post template
		$post_id = $post['ID'];

		// Get the template for this post
		if( isset($vars['view']) ){
			// TODO : Cache results for performance optimization
			$template_path = pw_get_post_template( $post_id, $vars['view'], 'dir' );
		}
		else if( isset($vars['template']) )
			$template_path = $vars['template'];

		// Initialize h2o template engine
		$h2o = new h2o( $template_path );

		// Seed the post data with 'post' for use in template, ie. {{post.post_title}}
		$pw_post['post'] = $post;
		//$pw_post['post_json'] = json_encode($post);

		//pw_log( 'post', $post );

		// Add rendered HTML to the return data
		$post_html .= $h2o->render($pw_post);
	}

	return $post_html;
}



function pw_print_menu_feed( $vars ){
	/*
		$vars = array(
			"menu" 		=> "" 		// Name or ID or slug of menu
			"fields"	=> array()	// Fields to pass to pw_get_post
			"view"		=> ""		// Which view to render
		)
	*/

	$posts = pw_get_menu_posts( $vars['menu'], $vars['fields'] );

	$html = pw_print_feed(
		array(
			"view"	=>	$vars["view"],
			"posts"	=>	$posts,
			)
		);

	return $html;

}

/**
 * Gets the post data associated with a menu
 * Also supports term items in menus
 *
 * @param string|integer $menu Menu name, slug, or term ID.
 * @param string|array $fields Postworld post fields model.
 */
function pw_get_menu_posts( $menu, $fields ){

	$menu_slug = wp_get_nav_menu_object( $menu )->slug;
	
	$query = array(
		"post_type"			=>	"nav_menu_item",
		"fields"			=>	array(
			"ID",
			"post_title",
			"post_type",
			"post_content",
			"post_excerpt",
			"post_meta(_all)"
			),
		"posts_per_page"	=>	200,
		'order'             => 'ASC',
		'orderby' 			=> 'menu_order',
		'output_key' 		=> 'menu_order',
		"tax_query"	=>	array(
			array(
				"taxonomy"	=>	"nav_menu",
				"field"		=>	"slug",
				"terms"		=>	$menu_slug,
				),
			),
		);

	$menu_items = pw_query( $query )->posts;

	//pw_log( 'MENU ITEMS : ',  $menu_items  );

	// If only the IDs are requested
	if( $fields == 'ids' ){
		$post_ids = array();
		// Iterate through the posts
		foreach( $menu_items as $item ){
			// And collect just the associated IDs
			$post_ids[] = $item['post_meta']['_menu_item_object_id'];
		}
		// Return, converting strings to numbers
		return pw_sanitize_numeric_array( $post_ids );
	}

	// Get the posts
	$posts = array();
	foreach( $menu_items as $item ){

		/**
		 * MENU ITEM TYPES
		 * Switch based on the type of item it is
		 */
		switch( _get( $item, 'post_meta._menu_item_type' ) ){

			// POST / PAGE / CPT
			case 'post_type':

				// Get the post
				$post_id = $item['post_meta']['_menu_item_object_id'];
				$post = pw_get_post( $post_id, $fields );

				// Over-ride post title with menu title
				if( !empty( $item['post_title'] ) )
					$post['post_title'] = $item['post_title'];

				// Include the menu item data in the post
				$post['menu_item'] = $item;

				// Filter here so theme can add additional meta-data
				$post = apply_filters( 'pw_get_menu_item_post', $post );

				$posts[] = $post;

				break;

			// TAXONOMY
			case 'taxonomy':

				$post = array();

				$term = get_term(
					$item['post_meta']['_menu_item_object_id'],
					$item['post_meta']['_menu_item_object'],
					'ARRAY_A' );

				// Use the menu item title to override the term title
				if( !empty( $item['post_title'] ) )
					$post['post_title'] = $item['post_title'];
				else
					$post['post_title'] = $term['name'];

				/**
				 * WordPress stores the nav item 'description'
				 * As a 'post_content' field, though the description
				 * Is generally impliment similar to excerpts
				 * So use the menu item description as the excerpt
				 */
				$post['post_excerpt'] = $item['post_content'];

				// Get the post link
				$post['post_permalink'] = get_term_link(
					(int) $item['post_meta']['_menu_item_object_id'],
					$item['post_meta']['_menu_item_object'] );

				// Include the menu item data in the post
				$post['term'] = $term;
				$post['menu_item'] = $item;

				// Filter here so theme can add additional meta-data
				$post = apply_filters( 'pw_get_menu_item_taxonomy', $post );
				$posts[] = $post;
				break;
		}
	}

	//pw_log( 'MENU : ',  $posts  );
	return $posts;

}


function pw_get_feed_posts( $vars ){
	// PW Feed offers PW Query combined with PW Get Posts
	// By first returning a feed outline, and then
	// This allows the additonal pw_get_posts() options to be passed in
	/*
		$vars = array(
			'query'		=>	[ array ]			// Query for pw_query() method, excluding the 'fields' key
			'fields'	=>	[ string / array ]	// Include the fields key
			'options'	=>	[ array ]			// Options for
			);
	*/
	// Set default variables
	$default_vars = array(
		'query'		=>	array(),
		'fields'	=>	'preview',
		'options'	=>	array(),
		);
	$vars = array_merge_recursive( $default_vars, $vars );
	// Get Feed Outline
	$feed_outline = pw_feed_outline( $vars['query'] );
	// Get Posts
	$posts = pw_get_posts( $feed_outline, $vars['fields'], $vars['options'] );
	// Return Posts
	return $posts;
}


function pw_gallery_feed( $vars = array() ){
	/*
	$vars = array(
		'query'		=>	array(), 					// Query Vars for pw_query
		'get_posts'	=>	array(
			'post_ids'	=> [ array ]				// An array of post IDs to get using pw_get_posts
			'fields'	=> [ array / string ]		// Field model for which posts to retreive
			),
		'options'	=>	array(
			'move_galleries'	=>	[ boolean ],	// Delete galleries in posts
			'require_image'		=>	[ boolean ],	// Require posts to have an image
			'include_posts'		=>	[ boolean ],	// Include the posts too
			'max_posts'			=> 	[ integer ],	// Maxmimum number of posts total
			),
		);
	*/

	$default_vars = array(
		'query'		=>	array(),
		'get_posts'	=>	array(
			'fields'	=>	'preview',
			),
		'options'	=>	array(
			'require_image'	=>	true,
			'include_posts'	=>	false,
			),
		);

	$vars = array_replace_recursive( $default_vars, $vars );

	///// GET POSTS /////
	// Add gallery field to field models
	pw_add_gallery_field_filters();

	$post_ids = _get( $vars, 'get_posts.post_ids' );

	/// USE PW GET POSTS ///
	// If post IDs are provided
	if( is_array( $post_ids ) ){
		$posts = pw_get_posts( $post_ids, $vars['get_posts']['fields'] );
	}
	/// USE PW QUERY ///
	else if( !empty( $vars['query'] ) ){
		$posts = pw_query( $vars['query'] )->posts;
	}
	/// RETURN FALSE ///
	else{
		return false;
	}

	$posts = pw_merge_galleries( $posts, $vars['options'] );

	// Remove the gallery field from field models
	pw_remove_gallery_field_filters();

	return $posts;

}





function get_panel_ids(){
	global $pwSiteGlobals;
	$override_file_names =	list_dir_file_names( $pwSiteGlobals['template_paths']['panels']['dir']['override'] ); //['override_panel_template_abs_path']);
	$default_file_names = 	list_dir_file_names( $pwSiteGlobals['template_paths']['panels']['dir']['default'] ); //['default_panel_template_abs_path'] );

	$final_panel_names = array();
	for ($i=0; $i <count($default_file_names) ; $i++) {
		$final_panel_names[] = str_replace(".html", "", $default_file_names[$i]);
	}

	for ($i=0; $i < count($override_file_names); $i++) {
		$name = str_replace(".html", "", $override_file_names[$i] );
		if(!in_array($name,$final_panel_names)){
			$final_panel_names[] = $name;
		}
	}

	return $final_panel_names;
}



function get_comment_ids(){
	global $pwSiteGlobals;
	$override_file_names =	list_dir_file_names( $pwSiteGlobals['template_paths']['comments']['dir']['override'] ); //['override_comment_template_abs_path']);
	$default_file_names =	list_dir_file_names( $pwSiteGlobals['template_paths']['comments']['dir']['default'] );//['default_comment_template_abs_path']);


	$final_comment_names = array();
	for ($i=0; $i <count($default_file_names) ; $i++) {
		$final_comment_names[] = str_replace(".html", "", $default_file_names[$i]);
	}

	for ($i=0; $i < count($override_file_names); $i++) {
		$name = str_replace(".html", "", $override_file_names[$i] );
		if(!in_array($name,$final_comment_names)){
			$final_comment_names[] = $name;
		}
	}

	return $final_comment_names;
}


function list_dir_file_names($directory){

	$names_array=array();
	//echo("<br>".$directory."<br>");
	if (is_dir($directory)){
		//echo 'is directoruuu';

	$dir = new RecursiveDirectoryIterator($directory,
			    FilesystemIterator::SKIP_DOTS);

			// Flatten the recursive iterator, folders come before their files
			$it  = new RecursiveIteratorIterator($dir,
			    RecursiveIteratorIterator::SELF_FIRST);

			// Maximum depth is 1 level deeper than the base folder
			$it->setMaxDepth(1);


			// Basic loop displaying different messages based on file or folder
			foreach ($it as $fileinfo) {
			    if ($fileinfo->isFile()) {
			    	//echo $fileinfo->getFilename();
			        //$names_array[]= $url_path.($fileinfo->getFilename());
					$names_array[]= $fileinfo->getFilename();
			    }
			}
	}

	return $names_array;
}




//convert object to array $array =  (array) $yourObject;
class pw_query_args{
	public $post_type;
	public $link_format;//pw
	public $post_class;//pw
	public $author;
	public $author_name;
	public $year;
	public $month;
	public $tax_query;
	public $s;
	public $orderby='date';
	public $order='DESC';
	public $posts_per_page="-1";
	public $fields;
}


?>