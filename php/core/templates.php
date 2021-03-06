<?php
/**
 * Gets pre-filtered templates available for various contexts, with meta-data.
 * 
 * @param string $context The context for which to get views.
 *			Options: feed | related_posts
 */
function pw_template_options( $context ){
	$options = array();
	switch( $context ){
		case 'feeds':
		case 'related_posts':
			// If no context views listed, use all supported views
			$views = pw_config( 'post_views.supported' );
			$context_views = pw_config( 'post_views.options.'.$context );
			if( !empty( $context_views ) )
				$views = $context_views;
			/**
			 * Form an array with meta data
			 * Plus keys 'name' with the view slug
			 */
			$post_views_meta = pw_config( 'post_views.meta' );
			foreach( $views as $view ){
				$view_meta = $post_views_meta[$view];
				$view_meta['name'] = $view;
				$options[] = $view_meta;
			}
			break;
	}
	return $options;

}

/**
 * Shortform wrapper for pw_get_ng_template method.
 */
function pw_grab_ng_template( $subdir, $id, $post_type = false ){

	return pw_get_ng_template( array(
		'subdir' => $subdir,
		'id' => $id,
		'post_type' => $post_type
		) );

}

/**
 * Gets an actual template contents wrapped in
 * text/ng-template script type.
 *
 * @todo use same fallback mechanism as in JS liveFeed Controller, method: updateTemplateUrl
 *
 * @param array $vars An array of variables.
 * @return string The template wrapper in text/ng-template script tags
 */
function pw_get_ng_template( $vars = array() ){
	/*
		$vars = array(
			"subdir"	=>	[string],		// subdirectory inside templates dir
			"id"		=>	[string]		// file basename, assumed .html
			"post_type" => [string/array]	// post types (for subdir 'posts')
		)
	*/

	global $pw;

	$default_vars = array(
		'subdir' 	=> 'posts',
		'id'		=> 'list',
		'post_type' => array('post'),
		);

	$vars = array_replace($default_vars, $vars);

	// Get templates paths
	$templates_dir = pw_get_templates(array(
		'path_type' => 'dir',
		'ext'		=> 'html'
		));

	// Get templates urls
	$templates_url = pw_get_templates(array(
		'path_type' => 'url',
		'ext'		=> 'html'
		));

	//pw_log( 'pw_get_ng_template, post_type', $vars['post_type'] );

	$append_url = '?ver='.$pw['info']['site_version'];

	// Get the device type, false if devices module not enabled
	$device_type = pw_get_device_type();

	$output = '';

	///// POSTS /////
	if( $vars['subdir'] === 'posts' ){

		// Get all post types, if value is 'any'
		if( $vars['post_type'] === 'any' )
			$vars['post_type'] = pw_get_post_types( array(), 'slug' );
		
		// If value is a string, convert to array
		if( is_string( $vars['post_type'] ) )
			$vars['post_type'] = array( $vars['post_type'] );
		
		// Iterate through post types
		// And preload all post type templates
		// For the specified view
		foreach( $vars['post_type'] as $post_type ){
			$template_path = false;
			$template_url = false;

			// Check for an override template with the device type appended
			if( $device_type !== false ){
				$template_path = _get( $templates_dir, $vars['subdir'].'.'.$post_type.'.'.$vars['id'].'-'.$device_type );
				$template_url = _get( $templates_url, $vars['subdir'].'.'.$post_type.'.'.$vars['id'].'-'.$device_type );
			}

			if( !$template_path ) 
				$template_path = _get( $templates_dir, $vars['subdir'].'.'.$post_type.'.'.$vars['id'] );
			if( !$template_url ) 
				$template_url = _get( $templates_url, $vars['subdir'].'.'.$post_type.'.'.$vars['id'] );
		
			if( $template_path !== false )
				$output .= pw_get_ng_template_contents(
					$template_path,
					$template_url,
					$append_url
					);

		}

	///// ALL OTHER TEMPLATES /////
	} else{

		// Check for an override template with the device type appended
		if( $device_type !== false ){
			$template_path = _get( $templates_dir, $vars['subdir'].'.'.$vars['id'].'-'.$device_type );
			$template_url = _get( $templates_url, $vars['subdir'].'.'.$vars['id'].'-'.$device_type );
		}
		
		// If no device specific template, use normal template
		if( !$template_path )
			$template_path = _get( $templates_dir, $vars['subdir'].'.'.$vars['id'] );
		if( !$template_url )
			$template_url = _get( $templates_url, $vars['subdir'].'.'.$vars['id'] );

		// Get Fallback Vars
		if( $template_path === false ){
			$vars = apply_filters( 'pw_get_ng_template_fallback', $vars );
			$template_path = _get( $templates_dir, $vars['subdir'].'.'.$vars['id'] );
			$template_url = _get( $templates_url, $vars['subdir'].'.'.$vars['id'] );
		}
	
		if( $template_path !== false )
			$output .= pw_get_ng_template_contents(
				$template_path,
				$template_url,
				$append_url
				);

	}

	return $output;

}



/**
 * Gets the contents of a template partial
 * And wraps it in text/ng-template script tag
 */
function pw_get_ng_template_contents( $template_path, $template_id, $append_id = '' ){
	
	if( empty( $template_path ) || !file_exists( $template_path ) )
		return false;
	
	$file_contents = file_get_contents( $template_path );

	if( empty( $file_contents ) )
		return '';

	// Generate Output
	$output = '<script type="text/ng-template" id="'.$template_id.$append_id.'">'."\n";
	//$output .= "PRELOADED"; // Just for test purposes
	$output .= $file_contents;
	$output .= "\n</script>\n";

	return $output;
}

/**
 * Gets the result of a template partial function
 */
function pw_get_template_partial( $vars ){
	/*
		$vars = array(
			"partial"	=>	[string],	// virtual path to partial
			"vars"		=>	[mixed]		// optional
		)
	*/
	
	extract($vars);

	// Return early if no partial defined
	if( !isset( $partial ) )
		return false;

	// Set the object to be filled with template partials
	$template_partials_obj = array();

	// Run the filter which collects all the template partials
	$template_partials_obj = apply_filters( 'pw_template_partials', $template_partials_obj );

	// Get the specified template partial
	$template_partial_function = pw_get_obj( $template_partials_obj, $partial );

	// If the partial has a function defined
	if( $template_partial_function ){

		if( !empty( $vars ) )
			return call_user_func( $template_partial_function, $vars );
		else
			return call_user_func( $template_partial_function );
	}
	else
		return false;
}

function pw_get_dirs($path = '.') {
	$dirs = array();
	
	if( file_exists($path) ){
		$subpaths = new DirectoryIterator($path);
		foreach ( $subpaths as $file) {
			if ($file->isDir() && !$file->isDot()) {
				$dirs[] = $file->getFilename();
			}
		}	
	}
	
	return $dirs;
}

function pw_construct_template_obj( $args ){
	/**
	 * @todo Generate runtime hash cache based on args
	 */

	extract($args);

	// Set Defaults
	if( !isset( $path_type ) )
		$path_type = 'dir';
	if( !isset( $ext ) )
		$ext = 'php';
	if( !isset( $url ) )
		$url = '';
	if( !isset( $subdirs ) )
		$subdirs = pw_get_dirs( $dir );

	$template_object = array();

	// Iterate through each Directory
	foreach( $subdirs as $subdir ){
		$template_object[$subdir] = array();
		$files = glob( trailingslashit($dir) . $subdir . '/*' . '.' . $ext );

		// Iterate through each File
		foreach( $files as $file ){
			$basename = basename($file);
			$basename_noext = basename( $file, '.' . $ext );

			// Output Directory Path
			if( $path_type == 'dir' )
				$template_object[$subdir][$basename_noext] = $file;
			else if ( $path_type == 'url' )
			// Output URL Path
				$template_object[$subdir][$basename_noext] = trailingslashit($url) . trailingslashit($subdir) . $basename;
			
		}
	
	}
	
	//pw_log($template_object);
	return $template_object;
}

/**
 * @todo : cache at runtime, $pw_templates, or $pw['templates']
 */
function pw_get_templates( $vars = array() ){
	/*
		$vars = array(
			'subdirs'			=>	[array]	(optional)
			'posts'				=>	[array]	(optional)
			'path_type'			=>	[string] ( 'url' / 'dir' ),
			'ext'				=>	[string] ( 'php' / 'html' ),
			'source'			=>	[string] ( 'default' / 'merge' ),
			'output'			=>	[string] (optional) 'default' / 'ids'
			)
	*/

	pw_set_microtimer('pw_get_templates');

	$pw_config = pw_config();
	extract($vars);

	// Set Defaults
	if( !isset($templates_object) )
		$templates_object = array(); // TODO - Add handling for this for performance, folder or file specifics
	if( !isset( $path_type ) )
		$path_type = 'url';
	if( !isset( $source ) )
		$source = 'merge';
	if( !isset( $ext ) )
		$ext = 'html';

	// Check to see if there is a templates folder in the child folder
	//$has_override_templates_dir = is_dir( $pw_config['templates']['dir']['override'] );

	// Setup Variables
	$default_template_dir = $pw_config['templates']['dir']['default'];
	$default_template_url = $pw_config['templates']['url']['default'];
	$override_template_dir = $pw_config['templates']['dir']['override'];
	$override_template_url = $pw_config['templates']['url']['override'];

	///// DEFAULT Templates Object /////
	$default_template_obj_args = array(
		'dir'	=>	$default_template_dir,
		'url'	=>	$default_template_url,
		'ext'	=>	$ext,
		'path_type'	=>	$path_type,
		);

	if( isset($subdirs) )
		$default_template_obj_args['subdirs'] = (array) $subdirs;

	if( $source == 'default' || $source == 'merge' )
		$default_template_obj = pw_construct_template_obj( $default_template_obj_args );


	///// OVERRIDE Templates Object /////
	$override_template_obj_args = array(
		'dir'	=>	$override_template_dir,
		'url'	=>	$override_template_url,
		'ext'	=>	$ext,
		'path_type'	=>	$path_type,
		);

	if( isset($subdirs) )
		$override_template_obj_args['subdirs'] = (array) $subdirs;

	if( $source == 'override' || $source == 'merge' )
		$override_template_obj = pw_construct_template_obj( $override_template_obj_args );


	///// Merge Results /////
	if( $source == 'merge' ){
		// Start with Default Template Object
		$template_obj = $default_template_obj;

		// Iterate over the Override Template Object
		foreach( $override_template_obj as $subdir => $templates ){

			// Iterate over the Templates
			foreach( $templates as $template_id => $template_value ){
				
				// Create the Subobject if it doesn't exist
				if( !isset($template_obj[$subdir]) )
					$template_obj[$subdir] = array();

				// Add the Override Value
				$template_obj[$subdir][$template_id] = $template_value;

			}

		}

	}

	///// RETURN : BEFORE POSTS /////
	// If 'subdirs' is specified, and 'posts' is not specified
	if( isset($subdirs) &&
		!in_array( 'posts', $subdirs )){
		//pw_log_microtimer('pw_get_templates', 'NON-POSTS');
		// Return before processing post templates
		return $template_obj;
	}

	////////// POST TEMPLATES : OBJECT STRUCTURE //////////
	///// GET POST TYPES /////
	$post_types_defined = ( isset ( $posts['post_types'] ) ) ? true : false;
	$post_types = $post_types_defined
		? // Post Types defined
		$posts['post_types']
		: // Post Types are not defined
		get_post_types( array( 'public'   => true ), 'names', 'and' );

	// If the post types were not defined and so derived from `get_post_types`
	if( !$post_types_defined ){
		// Flatten Array
		$post_types_final = array();
		foreach ( $post_types as $post_type ) {
				$post_types_final[] = $post_type ;
		}
		$post_types = $post_types_final;
	}
	
	//pw_log( "TEMPLATE POST TYPES : " . json_encode($post_types) );

	///// GET VIEWS /////
	$post_views = ( isset( $posts['post_views'] ) ) ?
		$posts['post_views'] :
		pw_config( 'post_views.supported' );

	///// CONSTRUCT POSTS TEMPLATE OBJECT /////
	$post_template_obj = array();

	$devices_module = pw_module_enabled('devices');

	// Iterate through post types
	foreach( $post_types as $post_type ){

		// Create empty array for post type		
		$post_template_obj[ $post_type ] = array();

		// Iterate through post views
		foreach( $post_views as $post_view ){

			/// DEVICES LAYER ///
			if( $devices_module ){
				$devices = array( 'mobile', 'tablet', 'desktop' );
				foreach( $devices as $device ){
					// Define the id for the current template
					$device_view = $post_view . '-' . $device;
					$device_template_id = $post_type . '-' . $device_view;
					if( isset( $template_obj['posts'][ $device_template_id ] ) )
						$post_template_obj[ $post_type ][ $device_view ] =
							$template_obj['posts'][ $device_template_id ];
				}
			}

			// Define the id for the current template
			$template_id = $post_type . '-' . $post_view;

			// If not available, default to the 'post' post_type
			$default_template_id = 'post' . '-' . $post_view;

			// Set the template object / string to use
			$existing_template_id = 
				( isset( $template_obj['posts'][ $template_id ] ) ) ?
				$template_id : $default_template_id;

			// Check if the object exists, otherwise return empty string
			$post_template_obj[ $post_type ][ $post_view ] =
				( isset( $template_obj['posts'][ $existing_template_id ] ) ) ? 
				$template_obj['posts'][ $existing_template_id ] : '';

		}

	}

	$template_obj['posts'] = $post_template_obj;

	///// OUTPUT /////
	// If output is 'ids', strip the path data
	if( _get( $vars, 'output' ) == 'ids' ){
		$new_obj = array();
		foreach( $template_obj as $key => $value ){
			// Handle Posts (extra level of recursion)
			if( $key == 'posts' ){
				$new_obj[$key] = array();
				foreach( $value as $subkey => $subvalue ){
					$new_obj[$key][$subkey] = array();
					foreach( $subvalue as $subsubkey => $subsubvalue ){
						$new_obj[$key][$subkey][] = $subsubkey;
					}
				}
			}
			// Handle Others
			else
				foreach( $value as $subkey => $subvalue ){
					$new_obj[$key][] = $subkey;
				}
		}
		$template_obj = $new_obj;
	}

	//pw_log_microtimer('pw_get_templates', 'WITH POSTS');

	return $template_obj;

}


function pw_get_template( $subdir, $template_id, $ext = "html", $path_type = "url" ){
	// Returns a single string for panel template from ID

	$panel_template = pw_get_templates ( array(
			"subdirs" 	=> 	array( $subdir ),
			"path_type"	=> 	$path_type,
			"ext"		=>	$ext,
			)
		);

	if( isset($panel_template) && isset($panel_template[$subdir][$template_id]) )
		return (string) $panel_template[$subdir][$template_id];
	else
		return false;
}


function  pw_get_post_template ( $post_id, $post_view, $path_type='url' ){

	/* Returns an template path based on the provided post ID and view
		Process
		
		Check the post type of the post as $post_type with get_post_type( $post_id )
		Using pw_get_templates(), get the template object
		Input :
		
		$args = array(
		    'posts' => array(
		        'post_types' => array( $post_type ),    // 'post'
		        'post_views' => array( $post_view )     // 'full'
		    ),
		);
		$post_template_object = pw_get_templates ($args);
		Output :
		
		{
		posts : {
		     'post' : {
		          'full' : '/wp-content/plugins/postworld/templates/posts/post-full.html',
		          },
		     },
		}
		return : string (The single template path) : /wp-content/plugins/postworld/templates/posts/post-full.html
	 */
		
	 $post_type =  get_post_type( $post_id );

	 $args = array(
	 		'subdirs'	=>	array('posts'),
			'posts' => array(
	    		'post_types' => array( $post_type ),    // 'post'
	    		'post_views' => array( $post_view ),    // 'full'
				),
			'path_type'	=>	$path_type,
		);
	
	$templates_object = pw_get_templates( $args );
	
	return $templates_object['posts'][$post_type][$post_view];

}

/**
 * Gets the URL of an HTML panel template.
 */
function pw_panel_template( $panel_id, $ext = 'html', $type = 'url' ){
	// Returns a single string for panel template from ID
	return pw_get_template( 'panels', $panel_id, $ext, $type );
}
function pw_get_panel_template( $panel_id, $ext = 'html', $type = 'url' ){
	///// DEPRECIATED /////
	// Returns a single string for panel template from ID
	return pw_panel_template( $panel_id, $ext, $type );
}


/**
 * Gets the system path of a PHP module template.
 */

function pw_module_template( $panel_id, $ext = 'php', $type = 'dir' ){
	// Returns a single string for panel template from ID
	return pw_get_template( 'modules', $panel_id, $ext, $type );
}

function pw_get_shortcode_template( $template_id, $ext = 'php', $type = 'dir' ){
	// Returns a single string for panel template from ID
	return pw_get_template( 'shortcodes', $template_id, $ext, $type );
}

function pw_get_user_widget_template( $template_id, $ext = 'php', $type = 'dir' ){
	// Returns a single path string for template from ID
	return pw_get_template( 'user-widget', $template_id, $ext, $type );
}

function pw_get_user_feed_template( $template_id, $ext = 'html', $type = 'dir' ){
	// Returns a single string for user template from ID
	return pw_get_template( 'user-feeds', 'user-feed-'.$template_id, $ext, $type );
}

function pw_get_social_template( $template_id, $ext = 'php', $type = 'dir' ){
	// Returns a single string for social template from ID
	return pw_get_template( 'social', $template_id, $ext, $type );
}

function pw_get_user_template( $template_id, $ext = 'html', $type = 'dir' ){
	// Returns a single string for user template from ID
	return pw_get_template( 'users', 'user-'.$template_id, $ext, $type );
}

function pw_get_admin_template( $template_id, $ext = 'php', $type = 'dir' ){
	// Returns a single string for admin template from ID
	return pw_get_template( 'admin', $template_id, $ext, $type );
}

function pw_get_module_template( $template_id, $ext = 'php', $type = 'dir' ){
	// Returns a single string for module template from ID
	return pw_get_template( 'modules', $template_id, $ext, $type );
}

function pw_ob_admin_template( $template_id, $vars = array() ){
	// Use output buffering to include an admin template
	// And return the contents as a string
	$template = pw_get_admin_template( $template_id );
	return pw_ob_include( $template, $vars );
}

function pw_ob_social_template( $template_id, $vars ){
	$template = pw_get_social_template( $template_id );
	return pw_ob_include( $template, $vars );
}




// Include a Postworld Feed Template from templates/feeds
function pw_parse_template( $template_path, $vars = array() ){
	extract($vars);
	
	ob_start();
	include $template_path;
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}


function pw_get_menu_templates(){
	
	$templates = pw_get_templates(
		array(
			'subdirs' => array( 'menus' ),
			'ext' => 'php',
			'path_type' =>  'dir',
			)
		);
	$templates = $templates['menus'];

	return $templates;

}
