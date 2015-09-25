<?php
/*_                            _   
 | |    __ _ _   _  ___  _   _| |_ 
 | |   / _` | | | |/ _ \| | | | __|
 | |__| (_| | |_| | (_) | |_| | |_ 
 |_____\__,_|\__, |\___/ \__,_|\__|
             |___/                 
///////////////////////////////////*/

////////////// ADD METABOX //////////////
// TODO : Check why this is being called twice each page view

add_action('admin_init','pw_metabox_init_layout');
function pw_metabox_init_layout(){    

	global $pwSiteGlobals;
	global $post;

	// Get the settings
	$metabox_settings = pw_get_obj( $pwSiteGlobals, 'wp_admin.metabox.layout' );
	if( !$metabox_settings || !is_array( $metabox_settings ) )
		return false;
	
    //// ITERATE : POST TYPES /////
    $post_types = pw_get_obj( $metabox_settings, 'post_types' );

	// Iterate through the post types
    if( $post_types )
        foreach( $post_types as $post_type ){
            
            // Construct Variables for Callback
            $args = array(
                'post_type' =>  $post_type,
                );

            // Add the metabox
            add_meta_box(
                'pw_layout_meta',
                'Layout',
                'pw_layout_meta_init',
                $post_type,
                'normal',
                'core',
                $args //  Pass callback variables
                );

            // add a callback function to save any data a user enters in
            add_action( 'save_post','pw_layout_meta_save' );

        } // End Foreach : Post Type

}

////////////// CREATE UI //////////////
function pw_layout_meta_init( $post, $metabox ){
    global $post;
    global $pw;
    global $pwSiteGlobals;

    extract( $metabox['args'] );
    //pw_log( json_encode($vars) );

    //$pw_post = pw_get_post( $post->ID, array( 'ID', 'post_meta(all)' ) );

    // Apply filters for themes to over-ride
    $query = apply_filters( 'pw_layout_metabox_vars', $query );

	///// INCLUDE TEMPLATE /////
	include "metabox-layout-controller.php";

}



////////////// SAVE POST //////////////
function pw_layout_meta_save( $post_id ){
	// NOTE : Everything in the top portion of this function will be executed twice - why??

	// Stop autosave to preserve meta data
    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) )
        return $post_id;

    // Security Layer 
    if ( !current_user_can( 'edit_post', $post_id ) )
        return $post_id;
    
    // Get the posted JSON string    
    $post = pw_get_posted_json( 'pw_layout_post' );
   
   ///// GET SUBKEY /////
    // If it exists
    if( $post !== false )
        // Get the subkey
        $meta = pw_get_obj( $post, 'post_meta.' . pw_postmeta_key . '.layout' );
    else
        // Otherwise return here
        return false;

    ///// SET SUBKEY /////
    // If the subkey value exists
    if( $meta !== false )
        // Save it in postmeta
        pw_set_wp_postmeta(
            array(
                "post_id"   =>  $post_id,
                "meta_key"  =>  pw_postmeta_key,
                "sub_key"   =>  'layout',
                "value"     =>  $meta,
                )
            );

    return $post_id;
}


 

?>