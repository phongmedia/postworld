<?php
////////////// ADD METABOX //////////////
add_action('admin_init','pw_metabox_init_link_url');
function pw_metabox_init_link_url(){    
	global $pwSiteGlobals;

	// Add to Post Types
	$metabox_post_types = pw_get_obj( $pwSiteGlobals, 'wp_admin.metabox.link_url.post_types' );
	
	// Set the default Post Types
	if( !$metabox_post_types )
		$metabox_post_types = array( 'post', 'page' );

	// Add Metabox to each specified Post Type
    foreach( $metabox_post_types as $post_type ){
        add_meta_box(
        	'link_url_meta',
        	'Link URL',
        	'pw_link_url_meta_ui',
        	$post_type,
        	'side',
        	'high'
        	);
    }
    // Add Callback Function on Save
    add_action('save_post','pw_link_url_meta_save');
}

////////////// ADD SCRIPTS & STYLES //////////////
function pw_metabox_link_url_scripts(){
	// Add Styles
    wp_enqueue_style( 'metabox-link_url-style',
    	POSTWORLD_URI . 'admin/less/metabox-link_url.less' );
}
add_action( 'admin_enqueue_scripts', 'pw_metabox_link_url_scripts' );

////////////// CREATE UI //////////////
function pw_link_url_meta_ui(){
    global $post;

	// Load post meta
	$pw_post_meta = pw_get_post_meta($post->ID);
	$link_url = $pw_post_meta['link_url'];
	$link_format = $pw_post_meta['link_format'];

	// Include the template
	$metabox_template = pw_get_template ( 'admin', 'metabox-link_url', 'php', 'dir' );
	include $metabox_template;

}

////////////// SAVE POST //////////////
function pw_link_url_meta_save($post_id){
	// STOP FROM DOING AUTOSAVE TO PRESERVE META DATA
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
        return $post_id;
	// SAVE URL
	pw_set_post_meta($post_id,
		array(
			'link_url' 		=> $_POST['link_url'],
			'link_format' 	=> $_POST['link_format'],
			)
		);
    return $post_id;
}
 

?>