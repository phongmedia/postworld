<?php


///// SAVE BUTTON /////
function i_save_option_button( $option_name, $option_model ){
	$vars = array(
		'option_name'	=>	$option_name,
		'option_model'	=>	$option_model,
		);
	echo pw_ob_admin_template( 'button-save-option', $vars );
}

///// SELECT IMAGE ID /////
function pw_select_image_id( $vars = array() ){
	$defaultVars = array(
		'ng_model'		=>	null,
		'slug'			=>	pw_random_hash( $length = 4 ),	// [string] The unique slug where the image object is kept temporatily for display
		'label'			=>	'Image',						// [string] The label to put on the button and title
		'width'			=>	'400px',						// [string] The width to display the image
		'remove'		=>	true,							// [bool] 	Whether or not to include a button to un-set the background image
		'attributes'	=>	'',								// [string]	Arbitrary attributes to be added to the image object
		'display'		=>	true,							// [bool] 	Whether or not to display the selected image in-line
		);
	$vars = array_replace_recursive( $defaultVars, $vars );

	return pw_ob_admin_template( 'select-image-id', $vars );
}

///// SELECT SITE LOGO /////
function i_select_image_logo( $vars = array() ){
	$defaultVars = array(
		'option_var'	=>	'iOptions',
		'option_subkey'	=>	'images.logo',
		);
	$vars = array_replace_recursive( $defaultVars, $vars );
	return pw_ob_admin_template( 'select-image-logo', $vars );
}

///// SELECT SITE FAVICON /////
function i_select_image_favicon( $vars = array() ){
	$defaultVars = array(
		'option_var'	=>	'iOptions',
		'option_subkey'	=>	'images.favicon',
		);
	$vars = array_replace_recursive( $defaultVars, $vars );
	return pw_ob_admin_template( 'select-image-favicon', $vars );
}

///// SELECT A MENU /////
function i_select_menus( $vars ){
	/*
	$vars = array(
		'options_model' => 	[string] // Angular expression, where to store the results
		'ng_model' => 		[string] // Angular expression, where to set the option
		'null_option' =>	[string] // What to label the null option
		);
	*/
	return pw_ob_admin_template( 'select-menu', $vars );
}

///// DOWNLOAD IMAGE /////
function i_download_image_option( $vars = array( "context" => "quickEdit" ) ){
	/*
		$vars = array(
			'context'	=>	"quickEdit"
		);
	*/
	switch( $vars['context'] ){
		///// SITE-WIDE SETTINGS /////
		case 'siteAdmin': 
				$vars['options_model'] = "options.general.doubleSwitch";
				$vars['ng_model'] = "iOptions.posts.post.post_meta.".PW_POSTMETA_KEY.".image.download";
			break;

		///// PER-POST ADMIN SETTINGS /////
		case 'postAdmin':
		default:
				$vars['options_model'] = "options.general.tripleSwitch";
				$vars['ng_model'] = "pwMeta.image.download";
			break;

		///// QUICK EDIT SETTINGS /////
		case 'quickEdit':
		default:
				$vars['options_model'] = "options.general.tripleSwitch";
				$vars['ng_model'] = "post.post_meta.".PW_POSTMETA_KEY.".image.download";
			break;
	}

	return pw_ob_admin_template( 'meta-image-download', $vars );
}

///// POST CONTENT COLUMNS /////
function i_content_columns_option( $vars = array( "context" => "quickEdit" ) ){

	$vars['options_model'] = "options.post_content.columns";

	switch($vars['context']){
		///// SITE-WIDE SETTINGS /////
		case 'siteAdmin': 
				$vars['ng_model'] = "iOptions.posts.post.post_meta.".PW_POSTMETA_KEY.".post_content.columns";
			break;
		///// PER-POST ADMIN SETTINGS /////
		case 'postAdmin':
		default:
				$vars['ng_model'] = "pwMeta.post_content.columns";
			break;
		///// QUICK EDIT SETTINGS /////
		case 'quickEdit':
		default:
				$vars['ng_model'] = "post.post_meta.".PW_POSTMETA_KEY.".post_content.columns";
			break;
	}

	return pw_ob_admin_template( 'meta-content-columns', $vars );

}

function i_share_social_options(){
	return pw_share_social_options();
}
function pw_share_social_options(){
	$vars = array();
	$vars['options_model'] = "options.share.meta";
	$vars['model_var'] = "iSocial";
	$vars['model_key'] = "share.networks";
	$vars['ng_model'] = $vars['model_var'] . '.' . $vars['model_key'];

	return pw_ob_admin_template( 'share-social', $vars );
}


function pw_select_modules(){
	$vars = array();
	$vars['options_model'] = "options.modules";
	$vars['model_var'] = "pwModules";
	//$vars['model_key'] = "share.networks";
	//$vars['ng_model'] = $vars['model_var'] . '.' . $vars['model_key'];
	return pw_ob_admin_template( 'select-modules', $vars );
}


function i_gallery_options( $vars = array( "context" => "quickEdit" ) ){

	//$vars['options_model'] = "options.post_content.columns";

	switch( $vars['context'] ){
		///// SITE-WIDE SETTINGS /////
		case 'siteAdmin': 
				//$vars['ng_model'] = "iOptions.posts.post.post_meta.".PW_POSTMETA_KEY.".post_content.columns";
			break;
		///// PER-POST ADMIN SETTINGS /////
		case 'postAdmin':
		default:
				$vars['ng_model'] = "pwMeta.gallery";
			break;
		///// QUICK EDIT SETTINGS /////
		case 'quickEdit':
		default:
				$vars['ng_model'] = "post.post_meta.".PW_POSTMETA_KEY.".gallery";
			break;
	}

	return pw_ob_admin_template( 'meta-gallery-options', $vars );
}

function i_link_url_options( $vars = array( "context" => "quickEdit" ) ){

	if( !isset($vars['options_model']) )
		$vars['options_model'] = array();

	switch( $vars['context'] ){
		///// SITE-WIDE SETTINGS /////
		case 'siteAdmin': 
				$vars['ng_model'] = "iOptions.posts.post.post_meta.".PW_POSTMETA_KEY.".link_url";
				$vars['options_model']['show'] = "options.general.customSwitch";
				$vars['options_model']['tooltip_show'] = "options.general.none";
				$vars['options_model']['highlight'] = "options.general.doubleSwitch";
				$vars['options_model']['new_target'] = "options.general.doubleSwitch";
			break;
		///// PER-POST ADMIN SETTINGS /////
		case 'postAdmin':
		default:
				$vars['ng_model'] = "pwMeta.link_url";
				$vars['options_model']['show'] = "options.general.defaultAndCustomDoubleSwitch";
				$vars['options_model']['tooltip_show'] = "options.general.defaultCustomSwitch";
				$vars['options_model']['highlight'] = "options.general.tripleSwitch";
				$vars['options_model']['new_target'] = "options.general.tripleSwitch";
			break;
		///// QUICK EDIT SETTINGS /////
		case 'quickEdit':
		default:
				$vars['ng_model'] = "post.post_meta.".PW_POSTMETA_KEY.".link_url";
				$vars['options_model']['show'] = "options.general.defaultAndCustomDoubleSwitch";
				$vars['options_model']['tooltip_show'] = "options.general.defaultCustomSwitch";
				$vars['options_model']['highlight'] = "options.general.tripleSwitch";
				$vars['options_model']['new_target'] = "options.general.tripleSwitch";
			break;
	}

	return pw_ob_admin_template( 'meta-link-url-options', $vars );

}

function option_check( $option, $test ){
	if( $option == $test )
		return " checked ";
	else
		return null;
}

function radio_select( $option_name, $options, $attributes = '' ){
	foreach( $options as $option ){
		$checked = '';
		$checked = option_check( get_option($option_name), $option['slug'] );
		echo '<label><input name="'. $option_name .'" type="radio" value="'. $option['slug'] .'" ' . $checked . ' ' . $attributes . '/>' . $option['label'] . '</label>';
	}
}

function radio_image_select( $option_name, $options, $attributes = '' ){
	$html = "";
	$vars = array(
		'option_name'	=>	$option_name,
		'options'		=>	$options,
		'attributes'	=>	$attributes,
		);

	foreach( $options as $option ){
		$vars['option'] = $option;
		$vars['checked'] = '';
		$vars['checked'] = option_check( get_option( $vars['option_name'] ), $vars['option']['slug'] );
		$html .= i_ob_include_template( 'admin/modules/radio-image.php', $vars );
	}

	return $html;

}




function i_select_featured_image_options( $vars ){
	/*
	 *	$vars = array(
	 * 		'ng_model'	=> 	[string]	// Angular expression ie. 'iOptions.home.slider',
	 *	)
	 */
	return pw_ob_admin_template( 'select-header-image-options', $vars );
}

///// SLIDER OPTIONS : META FUNCTION /////
function i_admin_slider_options( $vars = array() ){
	// TODO:
	// - Create a random ID for the controller instance
	// - Pass in unique model prefix, to allow for multiple instances
	return pw_ob_admin_template( 'options-slider', $vars );
}

///// SINGLE LAYOUT OPTIONS /////
function i_layout_single_options( $vars = array( "context" => "quickEdit" ) ){
	//$vars['options_model'] = "options.post_content.columns";
	switch($vars['context']){
		///// SITE-WIDE SETTINGS /////
		case 'siteAdmin': 
				$vars['ng_model'] = "iLayouts[context.name]";
			break;
		///// EDIT POST SETTINGS /////
		case 'postAdmin':
		case 'quickEdit':
		default:
				$vars['ng_model'] = "pw_layout_post.post_meta." . pw_postmeta_key . ".layout";
			break;
	}
	return pw_ob_admin_template( 'layout-single', $vars );

}

///// SINGLE SELECT BACKGROUND OPTIONS /////
function pw_background_select( $vars = array( "context" => "quickEdit" ) ){
	//$vars['options_model'] = "options.post_content.columns";
	switch($vars['context']){
		///// SITE-WIDE SETTINGS /////
		case 'siteAdmin': 
				$vars['ng_model'] = "pwBackgroundContexts[ context.name ]";
			break;
		///// EDIT POST SETTINGS /////
		case 'postAdmin':
		case 'quickEdit':
		default:
				$vars['ng_model'] = "pw_background_post.post_meta." . pw_postmeta_key . ".background";
			break;
	}
	return pw_ob_admin_template( 'background-select', $vars );

}

///// SINGLE CUSTOMIZE BACKGROUND OPTIONS /////
// For all the controls of customizing a background
function pw_background_single_options( $vars = array( "context" => "quickEdit" ) ){
	//$vars['options_model'] = "options.post_content.columns";
	switch($vars['context']){
		///// SITE-WIDE SETTINGS /////
		case 'siteAdmin': 
				$vars['ng_model'] = "selectedItem";
			break;
		///// EDIT POST SETTINGS /////
		case 'postAdmin':
		case 'quickEdit':
		default:
				$vars['ng_model'] = "post.post_meta." . pw_postmeta_key . ".background";
			break;
	}
	return pw_ob_admin_template( 'background-single', $vars );

}


///// SELECT ICON /////
function i_select_icon_options( $vars = array( "ng_model" => "pwMeta.icon.class" ) ){
	return pw_ob_admin_template( 'select-icon', $vars );
}


function i_select_slider_settings( $vars ){
	/*
	 *	$vars = array(
	 * 		'ng_model'	=> 	[string]	// Angular expression ie. 'iOptions.home.slider',
	 *		'show'		=>	[ARRAY]		// Array of options to show : array( 'height', 'interval', 'max_slides', 'transition', 'no_pause' )
	 *	)
	 */
	return pw_ob_admin_template( 'select-slider-settings', $vars );
}


function i_select_blocks_settings( $vars ){
	/*
	 *	$vars = array(
	 *		'option_var'	=> [string]
	 *		'option_key'	=> [string]
	 *		'show'			=> [ARRAY]	// Array of options to show : array( 'height', 'interval', 'max_slides', 'transition', 'no_pause' )
	 *	)
	 */
	return pw_ob_admin_template( 'select-blocks-settings', $vars );
}


?>