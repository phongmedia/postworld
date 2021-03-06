<?php
/******************************************
Plugin Name: Postworld
Plugin URI: https://phong.com
Description: Wordpress API extension, with AngularJS client-side framework, LESS support, and standard libraries for developers to display posts in creative ways.
Version: 1.75
Author: phong
Author URI: http://phong.com
License: GPL2
******************************************/
/**
 * Action hook, fires right before Postworld is loaded
 * @since Postworld 1.602
 */
do_action('postworld_init');

/**
 * @todo Rename from 'pw' to 'postworld' for less chance of collisions
 * 			as some scripts use $pw as a local variable for 'password'.
 */
$GLOBALS['pw'] = array();

/**
 * Setup definitions required for loading API.
 */
define( 'POSTWORLD', true );
define( 'POSTWORLD_DIR', dirname(__FILE__) );
define( 'POSTWORLD_PATH', POSTWORLD_DIR );
define( 'POSTWORLD_CONFIG', 'postworld_config' );
$GLOBALS[ POSTWORLD_CONFIG ] = array();

/**
 * Load core Postworld API functions and classes
 */
include 'php/core/utilities.php';
include 'php/core/config.php';
include 'php/core/config-defaults.php';
include 'php/core/api.php';
include 'php/core/variables.php';
include 'php/core/definitions.php';
include 'php/core/modules.php';
include 'php/core/theme.php';

/**
 * Action hook to register the current theme.
 *
 * @since Postworld 1.602
 */
do_action( 'postworld_register_theme' );

/**
 * A good hook to configure everything.
 * This is where the theme can populate the Postworld Config.
 *
 * @since Postworld 1.602
 */
do_action( 'postworld_config' );

/**
 * PW GLOBALS
 * @todo Move this into the config file
 * This must come after the API functions
 * And before the rest of the Postworld includes
 */
$pw['info']['modules'] = pw_enabled_modules();

include 'php/core/filters.php';

define( 'POSTWORLD_URI', pw_get_postworld_uri() );

/**
 * H2O templating Engine
 * @todo Enable / Disable in postworld-config
 */
require_once 'lib/h2o/h2o.php';

/**
 * Add support for LESS CSS pre-processing.
 * @todo Enable / Disable in postworld-config
 */
require_once( POSTWORLD_PATH.'/lib/wp-less/wp-less.php' );

/**
 * Install Postworld
 */
include 'php/core/install.php';
include 'php/core/install_queries.php';

/**
 * Load Postworld Core
 */
//include 'php/core/meta.php';
include 'php/core/points.php';
include 'php/core/rank.php';
include 'php/core/templates.php';
include 'php/core/template_partials.php';
include 'php/core/feeds.php';
include 'php/core/feed_options.php';
include 'php/core/cron.php';
include 'php/core/user_meta.php';
include 'php/core/users.php';
include 'php/core/taxonomies.php';
include 'php/core/taxonomy_operations.php';
include 'php/core/cache.php';
include 'php/core/related.php';
include 'php/core/fields.php';
include 'php/core/images.php';
include 'php/core/posts.php';
include 'php/core/query.php';
include 'php/core/query_posts.php';
include 'php/core/widgets.php';
include 'php/core/archives.php';
include 'php/core/social.php';
include 'php/core/wizard.php';
include 'php/core/options.php';
include 'php/core/options-helpers.php';
include 'php/core/progress.php';
include 'php/core/view.php';
include 'php/core/embed.php';
include 'php/core/html.php';
include 'php/core/buddypress.php';
include 'php/core/events.php';
include 'php/core/styles.php';
include 'php/core/menus.php';
include 'php/core/dev.php';
include 'php/core/customize.php';
include 'php/core/scripts.php';
include 'php/core/database.php';
include 'admin/postworld_admin.php';
include 'admin/php/admin.php';

/**
 * Load Postworld Modules
 */
include 'php/modules/site/postworld-site.php';
include 'php/modules/backgrounds/postworld-backgrounds.php';
include 'php/modules/sidebars/postworld-sidebars.php';
include 'php/modules/layouts/postworld-layouts.php';
include 'php/modules/iconsets/postworld-iconsets.php';
include 'php/modules/taxonomy-meta/postworld-taxonomy-meta.php';
include 'php/modules/shortcodes/postworld-shortcodes.php';
include 'php/modules/slider/postworld-slider.php';
include 'php/modules/term-feed/postworld-term-feed.php';
include 'php/modules/user-feed/postworld-user-feed.php';
include 'php/modules/gallery/postworld-gallery.php';
include 'php/modules/visual-composer/postworld-visual-composer.php';
//include 'php/modules/security-ip/security-ip.php';

/**
 * Add support for JSON REST API
 * If running WordPress 4.4 or higher
 */
if( $GLOBALS['wp_version'] >= 4.4 )
	include 'php/modules/rest-api/postworld-rest-api.php';

if( pw_module_enabled( 'devices' ) )
	include 'php/modules/devices/postworld-devices.php';

if( pw_module_enabled( 'colors' ) )
	include 'php/modules/colors/postworld-colors.php';

if( pw_module_enabled( 'comments' ) )
	include 'php/modules/comments/postworld-comments.php';
 

/**
 * Load Low Priority Postworld Core
 */
include 'php/core/ajax.php';
include 'php/core/comments.php';
include 'php/core/share.php';
include 'php/core/meta.php';
include 'php/core/includes.php';
include 'php/core/update.php';

/**
 * Add Header Code
 * @todo Move this into a Postworld Actions class
 */
add_action('wp_head','pw_add_header_code');
function pw_add_header_code() {
	$output = get_option( PW_OPTIONS_HEADER_CODE, '' );
	echo $output;
}

/**
 * Enable WPDB errors if in Development Mode
 */
global $wpdb;
if( pw_dev_mode() )
	$wpdb->show_errors();

/**
 * Enable standard features
 */
add_theme_support( 'menus' );
add_theme_support( 'post-thumbnails' );

/**
 * Action hook, firing right after Postworld is loaded
 * @since Postworld 1.602
 */
do_action('postworld_loaded');
