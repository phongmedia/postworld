<?php
///// DEV TESTING /////



function pw_test_related_posts(){

	//pw_log( time() );
	//pw_log( pw_time_units() );

	// 'date_after_ago_period' and 'date_after_ago_multiplier'

	/*
	$query = array(
		'date_from' => array(
			'after_ago' => array(
				'period' => 'months',
				'multiplier' => 3
				),
			),
		);

	//$query = pw_prepare_date_from($query);
	//pw_log('pw_test_related_posts: ',$query);
	*/

	/*
	$test1 = pw_related_posts(
		array(
			'post_id'	=>	250803,
			'depth' 	=> 	10000,
			'query'	=>	array(
				'post_type' => array( 'feature', 'blog' ),
				),
			'related_by'	=>	array(
				array(
					'type' => 'taxonomy',
					'taxonomies' => array(
						array(
							'taxonomy' 	=> 'post_tag',
							'weight'	=>	1.5,
							),
						array(
							'taxonomy' 	=> 'topic',
							'weight'	=>	1,
							)
						),
					),
				),
			));


	pw_log( 'pw_related_posts : ' . json_encode( $test1, JSON_PRETTY_PRINT) );
	*/

	/*
	$test2 = pw_related_posts_by_taxonomy( array(
		'post_id' 	=> 	250803,
		'depth' 	=> 	10000,
		'number'	=>	10,
		'output'	=>	'ids',
		'order_by'	=>	'score',
		'query' => array(
			'post_type' => array('feature','blog')
			),
		'taxonomies' => array(
			array(
				'taxonomy' => 'post_tag',
				'weight' => 1.5,
				),
			array(
				'taxonomy' => 'topic',
				'weight' => 1,
				),
			),
		));
	pw_log( 'pw_related_posts_by_taxonomy : ' . json_encode($test2, JSON_PRETTY_PRINT) );
	*/

}
add_action('wp_loaded', 'pw_test_related_posts');


?>