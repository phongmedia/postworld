<?php
/**
 * @todo Add an expire column in the cache table
 * Check when retreiving if the expire is past.
 * If it's expired, delete it and return false.
 *
 * @todo Find more things to cache.
 */

/**
 * When saving a post, clear the affected caches.
 */
add_action( 'update_postmeta', 'pw_delete_post_caches' );
add_action( 'save_post', 'pw_delete_post_caches' );
add_action( 'save_post', 'pw_delete_post_single_cache' );

/**
 * When editing a menu, clear the affected caches.
 */
add_action( 'wp_update_nav_menu', 'pw_delete_post_single_cache' );
add_action( 'wp_update_nav_menu_item', 'pw_delete_post_single_cache' );
add_action( 'wp_create_nav_menu', 'pw_delete_post_single_cache' );
add_action( 'wp_delete_nav_menu', 'pw_delete_post_single_cache' );

/**
 * When saving a terms, clear the affected caches.
 */
add_action( 'created_term', 'pw_delete_post_caches' );
add_action( 'edited_term', 'pw_delete_post_caches' );

/**
 * Clear all the caches which could change when
 * a post is updated.
 */
function pw_delete_post_caches(){
	
	if( in_array( 'post_cache', pw_enabled_modules() ) ||
		in_array( 'layout_cache', pw_enabled_modules() ) ){
		pw_delete_cache_type( 'feed-outline' );
		pw_delete_cache_type( 'feed-posts' );
		pw_delete_cache_type( 'post' );
		pw_delete_cache_type( 'slider' );
		pw_delete_cache_type( 'layout' );
	}

	pw_delete_cache_type( 'term-feed' );
	pw_delete_cache_type( 'pw-query' );

}

/**
 * Clear all the caches associated with a particular post
 */
function pw_delete_post_single_cache( $post_id ){
	pw_delete_cache( array(
		'cache_name' => 'post-'.$post_id
		));
}


/**
 * When saving a menu, clear the affected caches.
 */
add_action( 'wp_update_nav_menu', 'pw_clear_menu_caches' );

/**
 * Clear all the caches which could change when
 * a menu is updated.
 */
function pw_clear_menu_caches(){
	pw_delete_cache_type( 'slider' );
}


/**
 * Gets the first matching row from the cache table.
 *
 * @param array $field Available keys are 'cache_name' and 'cache_hash'.
 * @param string $operator (Optional) Operator on which to query.
 */
function pw_get_cache( $fields, $operator = 'AND' ){

	if( !pw_config_in_db_tables('cache') )
		return false;

	global $wpdb;
	$table_name = $wpdb->postworld_prefix . 'cache';
	$supported_fields = array( 'cache_name', 'cache_hash' );
	$suported_operators = array( 'AND', 'OR' );

	$count = count( $fields );
	if( empty($count) )
		return false;

	if( !in_array( $operator, $suported_operators ) )
		$operator = 'AND';
	
	///// WHERE /////
	// Generate where query clause
	$where = '';
	$i = 1;
	$last = false;
	foreach( $fields as $key => $value ){
		if( $i == $count )
			$last = true;
		if( in_array( $key, $supported_fields ) ){
			$where .= $key.'="'.$value.'"';
			if( !$last )
				$where .= ' '.$operator.' ';
		}
		$i++;
	}

	$query = '
		SELECT *
		FROM '.$table_name.'
		WHERE '.$where;

	// Get the cached row from the database
	$data = $wpdb->get_row( $query, 'ARRAY_A');

	return $data;

	// If there's no result, return false
	if( empty( $data ) )
		return false;

	/** 
	 * If the cache is expired, delete the row and return false.
	 */ 
	if( is_numeric( $data['cache_expire'] ) &&
		$data['cache_expire'] <= time() ){
		pw_delete_cache( array( 'cache_id' => $data['cache_id'] ) );
		return false;
	}

	return $data;
	
}

/**
 * Sets a record in the cache table.
 *
 * @param array $data 	Required keys are 'cache_content' and 'cache_hash'.
 *						Addional keys are 'cache_type' and 'cache_name'
 */
function pw_set_cache( $data ){

	if( !pw_config_in_db_tables('cache') )
		return false;

	global $pw;
	global $wpdb;
	
	///// CONTENT /////
	// Content is required
	$cache_content = _get( $data, 'cache_content' );
	if( empty( $cache_content ) )
		return false;

	///// HASH /////
	// Hash is required to validate the cache content
	$cache_hash = _get( $data, 'cache_hash' );
	if( empty( $cache_hash ) )
		return false;

	///// SETUP DATA /////
	$cache_expire = time() + 60*60*24; // One day
	$defaultData = array(
		'cache_type'	=>	'undefined',
		'cache_name'	=>	pw_random_string(),
		'cache_expire'  => 	$cache_expire
		);
	$data = array_replace_recursive($defaultData, $data);

	///// UNIQUE CACHE NAME & CACHE HASH /////
	// Ensure that the data entered doesn't have have the same name or hash as data already cached
	$unique_keys = array( 'cache_name', 'cache_hash' );
	// Iterate through each unique key
	foreach( $unique_keys as $key ){
		// Key associated value
		$value = _get( $data, $key );
		// If there is a cache name
		if( !empty( $value ) ){
			// Delete all instances of that cache name
			pw_delete_cache( array( $key => $value ) );
		}
	}

	///// INSERT /////
	return $wpdb->insert(
		$wpdb->postworld_prefix . 'cache',
		$data
		);

}

function pw_delete_cache( $where ){

	if( !pw_config_in_db_tables('cache') )
		return false;

	global $pw;
	global $wpdb;
	return $wpdb->delete(
		$wpdb->postworld_prefix . 'cache',
		$where
		);
}

/**
 * Deletes all caches and truncates the entire cache table
 */
function pw_truncate_cache(){
	global $wpdb;
	// Delete all the Script Caches for added measure
	pw_scripts_flush();
	// If cache table is not enabled, end here
	if( !pw_config_in_db_tables('cache') )
		return false;	
	// Truncate the table
	return $wpdb->query("TRUNCATE TABLE `" . $wpdb->postworld_prefix . "cache`");
}

function pw_delete_cache_type( $type ){
	// Deletes coorosponding cache types
	$where = array(
		'cache_type' => $type,
		);
	return pw_delete_cache( $where );
}

function pw_delete_cache_name( $name ){
	// Deletes coorosponding cache names
	$where = array(
		'cache_name' => $name,
		);
	return pw_delete_cache( $where );
}


function pw_get_cache_hash_content( $cache_hash ){
	// Gets the first matching row with the given hash
	// And returns the content field
}


function pw_get_cache_types_readout(){

	if( !pw_config_in_db_tables('cache') )
		return false;

	// Returns an array containing the counts of each of the cache types
	// ie. [{"cache_type":"feed","type_count":"4"},{"cache_type":"term-feed","type_count":"2"}]
	global $wpdb;
	$table_name = $wpdb->postworld_prefix . 'cache';
	$query = '
		SELECT cache_type, count(distinct cache_hash) as type_count
		FROM '.$table_name.'
		GROUP BY cache_type
	';
	return $wpdb->get_results($query);
}


////////////////////////////////////////////////////////////////////

/**
 * The runtime cache is used to quickly store calculations
 * Which may be done many times through a single runtime cycle.
 *
 * @param string $key The ID/key of the cache.
 * @param mixed $value The value to store in the cache.
 *
 *  
 */
global $pw_runtime_cache;
function pw_set_runtime_cache( $key, $value ){
	global $pw_runtime_cache;
	$pw_runtime_cache[$key] = $value;
	return true;
}

function pw_get_runtime_cache( $key ){
	global $pw_runtime_cache;
	return _get( $pw_runtime_cache, $key );
}

function pw_has_runtime_cache( $key ){
	global $pw_runtime_cache;
	return isset( $pw_runtime_cache[$key] );
}


////////////////////////////////////////////////////////////////////


/**
 * Cycles through each post in each post_type scheduled for Rank Score caching
 * Calculates and caches each post's current rank with pw_cache_rank_score() method
 * Tracks progress with the Postworld Progress API
 */
function pw_cache_all_rank_scores( $post_types = array() ){
	$fnName = 'pw_cache_all_rank_scores';
	
	global $wpdb;
	
	if( pw_dev_mode() )
		$wpdb->show_errors();

	if( empty( $post_types ) )
		$post_types = pw_config('rank.post_types');

	if( empty( $post_types ) )
		return false;

	/// TIMER ///
	pw_set_microtimer( $fnName );
	$timers = array();

	/// PROGRESS API ////
	$item = 0; 
	pw_update_progress(
		$fnName,
		$item,
		count($post_types));

	$posts_count_total = 0;

	/// ITERATE THROUGH POST TYPES ///
	foreach( $post_types as $post_type ){
		$item ++;

		/// PROGRESS API ////
		pw_progress_kill_if_inactive( $fnName );
		pw_update_progress(
			$fnName,
			$item,
			count($post_types),
			array(
				'current_label'	=>	$post_type,
				'current'		=>	0,
				'total'			=>	0,
				));

		/// TIMER ///
		$time_start = date("Y-m-d H:i:s");
		pw_set_microtimer( $fnName . '-' . $post_type );

		/// GET POSTS IN POST TYPE ///
		$post_ids = pw_get_all_post_ids_in_post_type( $post_type, 'publish' );

		/// SETUP PROGRESS DATA ///
		$ping = 100;
		$posts_count = count($post_ids);
		$posts_count_total += $posts_count;

		/// ITERATE THROUGH POSTS ///
		$i = 0; $ii = 0;
		foreach ($post_ids as $post_id){
			$i++; $ii++;

			// Calculate and cache the rank score for each post
			pw_cache_rank_score( $post_id );

			// Update Progress
			if( $ii >= $ping ){
				$ii = 0;
				pw_progress_kill_if_inactive( $fnName );
				pw_update_progress(
					$fnName,
					$item,
					count($post_types),
					array(
						'current_label'	=>	$post_type,
						'current'		=>	$i,
						'total'			=>	$posts_count,
						));
			}

		}

		/// TIMER ///
		$time_end = date("Y-m-d H:i:s");	
		$timer = pw_get_microtimer( $fnName . '-' . $post_type );
		$timers[$post_type]	= $timer;

		/// CRON LOG API ///
		pw_insert_cron_log( array(
			'time_start'	=>	$time_start,
			'time_end'		=>	$time_end,
			'timer'			=>	$timer,
			'posts'			=>	$posts_count_total,
			'function_type'	=>	$fnName,
			'process_id'	=>	$post_type
			));

	}

	/// PROGRESS API ///
	pw_end_progress($fnName);

	return array(
		'timers'	=>	$timers,
		'timer'		=>	pw_get_microtimer( $fnName ),
		);

}


function pw_cache_all_points (){
	pw_set_microtimer( 'pw_cache_all_points' );

	$post_points_cron_log = pw_cache_all_post_points();
	$user_points_cron_log = pw_cache_all_user_points();

	return array(
		'timer'	=>	pw_get_microtimer('pw_cache_all_points'),
		);
}

function pw_cache_all_post_points() {
	$fnName = 'pw_cache_all_post_points';
	/*
	 • Cycles through each post in each post type with points enabled
	 • Calculates each post's current points with pw_calculate_points()
	 • Stores points it in wp_postworld_meta 'points' column
	 • Tracks progress with the Postworld Progress API
	 */
		 
	global $wpdb;
	$post_types = pw_config('points.post_types');

	if( empty($post_types) )
		return array( 'error' => 'No post types defined in Postworld Config.' );

	/// TIMER ///
	$time_start = date("Y-m-d H:i:s");
	pw_set_microtimer($fnName);

	/// PROGRESS API ////
	$item = 0; 
	pw_update_progress(
		$fnName,
		$item,
		count($post_types));

	///// ITERATE THROUGH EACH POST TYPE /////
	foreach( $post_types as $post_type ){
		$item ++;

		// Get all the published post IDs in this post type
		$post_ids = pw_get_all_post_ids_in_post_type( $post_type, 'publish' );

		/// PROGRESS API ////
		pw_progress_kill_if_inactive( $fnName );
		pw_update_progress(
			$fnName,
			$item,
			count($post_types),
			array(
				'current_label'	=>	$post_type,
				'current'		=>	0,
				'total'			=>	0,
				));


		///// ITERATE THROUGH EACH POST /////
		$i = 0; $ii = 0;
		foreach( $post_ids as $post_id ) {
			$i++; $ii++;

			pw_cache_post_points( $post_id );

			// Update Progress
			if( $ii >= 100 ){
				$ii = 0;
				/// PROGRESS API ////
				pw_progress_kill_if_inactive( $fnName );
				pw_update_progress(
					$fnName,
					$item,
					count($post_types),
					array(
						'current_label'	=>	$post_type,
						'current'		=>	$i,
						'total'			=>	count($post_ids),
						));
			}

		}

	}

	/// TIMER ///
	$time_end = date("Y-m-d H:i:s");	
	$timer = pw_get_microtimer($fnName);

	/// CRON LOG ///
	pw_insert_cron_log( array(
		'time_start'	=>	$time_start,
		'time_end'		=>	$time_end,
		'posts'			=>	count($posts),
		'timer'			=>	$timer,
		'function_type'	=>	$fnName,
		));

	/// PROGRESS API ///
	pw_end_progress($fnName);

	return array(
		'timer'	=>	$timer,
		);

}

function pw_cache_all_user_points(){
	$fnName = 'pw_cache_all_user_points';
	/*
	 • Cycles through all users with cache_user_points() method
	 • Tracks progress with the Postworld Progress API
	 */

	global $wpdb;

	/// TIMER ///
	$time_start = date("Y-m-d H:i:s");
	pw_set_microtimer($fnName);

	/// PROGRESS API ////
	pw_update_progress( $fnName, 0, 0 );

	/// GET USER IDS ///
	$user_ids = pw_get_all_user_ids();
	$user_count = count($user_ids);

	/// ITERATE THROUGH EACH USER ID ///
	$i = 0; $ii = 0; 
	foreach( $user_ids as $user_id ){
		$i++; $ii++; 

		/// CACHE USER POST POINTS ///
		pw_cache_user_posts_points( $user_id );

		/// UPDATE PROGRESS ///
		if( $ii >= 100 ){
			$ii = 0;
			/// PROGRESS API ////
			pw_progress_kill_if_inactive( $fnName );
			pw_update_progress( $fnName, $i, $user_count );
		}

	}

	/// TIMER ///
	$time_end = date("Y-m-d H:i:s");
	$timer = pw_get_microtimer($fnName);

	/// PROGRESS API ///
	pw_end_progress($fnName);

	/// CRON LOGS ///
	pw_insert_cron_log( array(
		'time_start'	=>	$time_start,
		'time_end'		=>	$time_end,
		'timer'			=>	$timer,
		'function_type'	=>	$fnName,
		));

	return array(
		'timer'	=>	$timer,
		);

}


function pw_cache_all_comment_points(){
	$fnName = 'pw_cache_all_comment_points';

	/*• Cycles through all columns
	• Calculates and caches each comment's current points with pw_cache_comment_points() method
	return : cron_logs Object (add to table wp_postworld_cron_logs)*/
	
	global $wpdb;

	/// TIMER ///
	pw_set_microtimer( $fnName );
	$time_start = date("Y-m-d H:i:s");

	/// PROGRESS API ////
	pw_update_progress( $fnName, 0, 0 );

	/// GET ALL APPROVED COMMENTS ///
	$comment_ids = pw_get_all_comment_ids();
	$comment_count = count( $comment_ids );

	/// PROGRESS API ////
	pw_update_progress( $fnName, 0, $comment_count );

	/// ITERATE THROUGH EACH COMMENT ///	
	$i = 0; $ii = 0; 
	foreach( $comment_ids as $comment_id ){
		$i++; $ii++;

		/// CACHE COMMENT POINTS ///
		pw_cache_comment_points( $comment_id );

		/// UPDATE PROGRESS ///
		if( $ii >= 100 ){
			$ii = 0;
			/// PROGRESS API ////
			pw_progress_kill_if_inactive( $fnName );
			pw_update_progress( $fnName, $i, $comment_count );
		}
	}

	/// TIMER ///
	$time_end = date("Y-m-d H:i:s");
	$timer = pw_get_microtimer($fnName);

	/// PROGRESS API ///
	pw_end_progress($fnName);

	pw_insert_cron_log( array(
		'time_start'	=>	$time_start,
		'time_end'		=>	$time_end,
		'timer'			=>	$timer,
		'function_type'	=>	$fnName,
		));

	return array(
		'timer'	=>	$timer,
		);
	
}

function pw_clear_cron_logs ( $timestamp ){
	if( !pw_config_in_db_tables('cron_logs') )
		return false;

	/*  • Count number of rows in wp_postworld_cron_logs (rows_before)
		• Deletes all rows which are before the specified timestamp (rows_removed)
		• Count number of rows after clearing (rows_after)
		return : Object
		rows_before: {{integer}}
		rows_removed: {{integer}}
		rows_after: {{integer}}
		
		 $timestamp format : '2013-09-25 14:39:55'
	*/
	
	
	global $wpdb;
	
	if( pw_dev_mode() )
		$wpdb -> show_errors();
	
	$query = "select COUNT(*) FROM $wpdb->postworld_prefix"."cron_logs";

	$total_logs = $wpdb-> get_var($query);

	if($total_logs == 0){
		return array('rows_before'=> 0,'rows_removed'=> 0,'rows_after'=>0); 
	}
	else{
	
		$query ="DELETE FROM $wpdb->postworld_prefix"."cron_logs WHERE time_end < '".$timestamp."'";

		$deleted_rows = $wpdb->query($query);

		if($deleted_rows === FALSE)
			$deleted_rows=0;
	
		return array('rows_before'=> $total_logs,'rows_removed'=> $deleted_rows,'rows_after'=>($total_logs - $deleted_rows));
	}
}


function pw_cache_shares ( $cache_all = FALSE ){ 
	/*
	
	 *  Description
	Caches user and post share reports from the Shares table
	
	 * Paramaters
	
		-$cache_all : boolean
		-Default : false
	
	 * Process
	
		-If $cache_all = false, just update the recently changed share reports
			-Check Cron Logs table for the most recent start time of the last pw_cache_shares() operation
			-POSTS :
				Get an array of all post_IDs from Shares table which have been updated since the most recent run of cache_shares() by checking the last time column
				Run pw_cache_post_shares($post_id) for all recently updated shares
			-AUTHORS :
				Get an array of all post_author_IDs from Shares table which have been updated since the last cache.
				Run cache_user_post_shares($user_id) for all recently updated user's shares
			-USERS :
				Get an array of all user_IDs from Shares table which have been updated since the last cache. Run pw_cache_user_shares($user_id) for all recently updated user's shares
	
	 	-If $cache_all = true
			-Cycle through every post and run pw_cache_post_shares($post_id)
			-Cycle through every author and run cache_user_post_shares($user_id)
			-Cycle through every user and run pw_cache_user_shares($user_id)
	return : cron_logs Object (store in table wp_postworld_cron_logs)
	 */	
	
	 $cron_logs=array();

	 if( $cache_all === FALSE ){
	 	
		$recent_log = pw_get_most_recent_cache_shares_log();
		// print_r($recent_log);
		 
		 if(!is_null($recent_log)){
		 	$time_start = date("Y-m-d H:i:s");
			 //print_r($recent_log->time_start);
			$post_ids = pw_get_recent_shares_post_ids($recent_log->time_start);
		
			foreach ($post_ids as $post_id) {
		//		print_r($post_id);
			
				pw_cache_post_shares($post_id->post_id);
			}
			
			$user_ids = pw_get_recent_shares_user_ids($recent_log->time_start);
			print_r($user_ids);
			foreach ($user_ids as $user_id) {
				print_r($user_id);
				pw_cache_user_shares($user_id->user_id,'outgoing');
			}
			
			$author_ids = pw_get_recent_shares_author_ids( $recent_log->time_start );
			//print_r($author_ids);
			foreach ($author_ids as $author_id) {
				 pw_cache_user_shares($author_id->author_id,'incoming' );
			}
			
			$time_end = date("Y-m-d H:i:s");
			
			$current_cron_log_object = pw_insert_cron_log( array(
				'time_start'	=>	$time_start,
				'time_end'		=>	$time_end,
				'function_type'	=>	'cache_shares',
				));

			return $current_cron_log_object;

		 } else{
		 	
		 }
		
		
	 } else{

 		//-If $cache_all = true
 		$time_start = date("Y-m-d H:i:s");

 		/*-Cycle through every post and run pw_cache_post_shares($post_id) */

 		$post_ids = pw_get_all_post_ids_as_array();

		foreach ($post_ids as $post_id) {
			pw_cache_post_shares($post_id->ID);
		}
		/*-Cycle through every author and run cache_user_post_shares($user_id)*/
		/*-Cycle through every user and run pw_cache_user_shares($user_id)*/

		$user_ids = pw_get_all_user_ids_as_array();

		foreach ($user_ids as $user_id) {
			pw_cache_user_shares($user_id->ID,'both');
		}
			 
		$time_end = date("Y-m-d H:i:s");
	
		$current_cron_log_object = pw_insert_cron_log( array(
				'time_start'	=>	$time_start,
				'time_end'		=>	$time_end,
				'function_type'	=>	'cache_shares',
				));

		return $current_cron_log_object;
			
	 }
	 

}


function pw_get_most_recent_cache_shares_log(){
	if( !pw_config_in_db_tables('cron_logs') )
		return false;

	global $wpdb;
	$wpdb->show_errors();
	$query="SELECT * FROM $wpdb->postworld_prefix"."cron_logs  WHERE time_start = (SELECT MAX(time_start) FROM $wpdb->postworld_prefix"."cron_logs where function_type = 'pw_cache_shares')";
	$row = $wpdb->get_row($query);	
	return $row;
}

function pw_get_recent_shares_post_ids($last_time){
	if( !pw_config_in_db_tables('shares') )
		return false;

	 global $wpdb;	
	 $wpdb->show_errors();
	 $query = "select DISTINCT  post_id from  $wpdb->postworld_prefix"."shares where last_time>='$last_time'";
	
	 $post_ids = $wpdb->get_results($query);
	  
	 return $post_ids;
}	

function pw_get_recent_shares_author_ids($last_time){
	if( !pw_config_in_db_tables('shares') )
		return false;

	 global $wpdb;	
	 $wpdb->show_errors();
	 $query = "select DISTINCT  author_id from  $wpdb->postworld_prefix"."shares where last_time>='$last_time'";
	 $user_ids = $wpdb->get_results($query);
	 return $user_ids;
}	

function pw_get_recent_shares_user_ids($last_time){
	if( !pw_config_in_db_tables('shares') )
		return false;

	 global $wpdb;	
	 $wpdb->show_errors();
	 $query = "select DISTINCT user_id from  $wpdb->postworld_prefix"."shares where last_time>='$last_time'";
	 $author_ids = $wpdb->get_results($query);
	 return $author_ids;
}	


function pw_get_all_post_ids_as_array(){
	 global $wpdb;
	 $wpdb->show_errors();
	 
	 $query = "select ID from wp_posts";
	 $post_ids_array = $wpdb->get_results($query);
	 
	 return ($post_ids_array);
}
function pw_get_all_user_ids_as_array(){
	
	global $wpdb;
	$wpdb->show_errors();

	$query = "select ID from wp_users";
	$user_ids_array = $wpdb->get_results($query);

	return $user_ids_array;
	
}

//////////////// POST SHARES /////////////////////
function pw_calculate_post_shares($post_id){
	if( !pw_config_in_db_tables('shares') )
		return false;

	/*Calculates the total number of shares to the given post
	Process
	-Lookup the given post_id in the Shares table
	-Add up ( SUM ) the total number in shares column attributed to the post
	-return : integer (number of shares)*/
	
	global $wpdb;
	if( pw_dev_mode() )
		$wpdb -> show_errors();
	
	$query = "select SUM(shares) FROM $wpdb->postworld_prefix"."shares where post_id=".$post_id;
	$total_shares = $wpdb->get_var($query);
	if($total_shares)
		return $total_shares;
	else return 0;
}

function pw_cache_post_shares( $post_id ){

	if( !pw_config_in_db_tables('post_meta') )
		return false;

	/*Caches the total number of shares to the given post
	Process
	-Run pw_calculate_post_shares($post_id)
	-Write the result to the post_shares column in the Post Meta table
	-return : integer (number of shares)*/
	$total_shares = pw_calculate_post_shares($post_id);
	
	pw_insert_post_meta($post_id);
	
	global $wpdb;
	if( pw_dev_mode() )
		$wpdb -> show_errors();
	
	$query = "update $wpdb->postworld_prefix"."post_meta set post_shares=".$total_shares." where post_id=".$post_id;
	$wpdb->query($query);
	return $total_shares;
}

/**
 * Calculates the total number of shares relating to a given user
 * @param $post_id integer
 * @param $mode string (optional) Options:
 * 			-'both' (default) : Return both incoming and outgoing
 *			-'incoming' : Return shares attributed to the user's posts
 *			-'outgoing' : Return shares that the user has initiated
 */
function pw_calculate_user_shares( $user_id, $mode='both' ){
	/*	
	 * Process
	-Lookup the given user_id in the Shares table
	-Modes :
	 -For incoming : Match to author_id column in Shares table
	 -For outgoing : Match to user_id column in Shares table
	-Add up (SUM) the total number of the shares column attributed to the user, according to $mode

	 * return : Array (number of shares)
	array(
	    'incoming' => {{integer}},
	    'outgoing' => {{integer}}
	    )
	*/

	$output = array();

	global $wpdb;

	if( pw_dev_mode() )
		$wpdb -> show_errors();

	if($mode =='outgoing' || $mode=='both'){
		$user_share_report_outgoing = pw_user_share_report_outgoing($user_id);
		$outgoing = 0;
		for ( $i=0; $i < count($user_share_report_outgoing) ; $i++) { 
			$outgoing = $outgoing + $user_share_report_outgoing[$i]['shares'];
		}
		$output['outgoing'] = $outgoing;
	}
	
	if($mode == 'incoming' || $mode =='both'){
		$user_share_report_incoming = pw_user_share_report_incoming($user_id);
		$incoming = 0;
		for ($i=0; $i <count($user_share_report_incoming) ; $i++) { 
			$incoming=$incoming + $user_share_report_incoming[$i]['total_shares'];
		}
		$output['incoming'] = $incoming;
	}
	return $output;
}

function pw_cache_user_shares( $user_id, $mode ){

	if( !pw_config_in_db_tables('user_meta') )
		return false;

	/*
	Caches the total number of shares relating to a given user
	Process
	
	Run pw_calculate_user_shares()
	Update the post_shares column in the user Meta table
	return : integer (number of shares)
	*/
	 
	$user_shares = pw_calculate_user_shares($user_id,$mode);
	//print_r($user_shares);

	global $wpdb;
	
	if( pw_dev_mode() )
		$wpdb -> show_errors();
	
	$total_user_shares=0;
	if(isset($user_shares['incoming'])) $total_user_shares = $user_shares['incoming'];
	if(isset($user_shares['outgoing'])) $total_user_shares = $user_shares['outgoing'];
	
	// Check if cached before and replace json values
	$old_shares = pw_get_user_shares($user_id);

	if(!is_null($old_shares)){
		$old_shares = (array)json_decode($old_shares);
		if($mode =='incoming' || $mode='both')
			if(isset($user_shares['incoming'])) $old_shares['incoming'] = $user_shares['incoming'];
		if($mode =='outgoing' || $mode='both')
			if(isset($user_shares['outgoing'])) $old_shares['outgoing'] = $user_shares['outgoing'];
		
	} else{
		pw_insert_user_meta($user_id);	
		$old_shares = $user_shares;		
	}
	//$total_user_shares = ($user_shares['incoming']+$user_shares['outgoing']);
	$query = "update $wpdb->postworld_prefix"."user_meta set share_points=".$total_user_shares.",share_points_meta='".json_encode($old_shares)."' where user_id=".$user_id;
	$wpdb->query( $query );

	return $total_user_shares;	 
}

function pw_get_user_shares($user_id){

	if( !pw_config_in_db_tables('user_meta') )
		return false;

	global $wpdb;
	
	if( pw_dev_mode() )
		$wpdb -> show_errors();
	
	$query = "select share_points_meta from $wpdb->postworld_prefix"."user_meta where user_id=".$user_id;
	return $wpdb->get_var($query);
}

?>