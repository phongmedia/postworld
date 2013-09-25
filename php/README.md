Postworld // PHP / MySQL Functions
=========

## Post Meta

**/php/postworld-meta.php**  
Handles getting and setting date in the **post_meta** table.

------

### pw_get_post_meta ( *$post_id* )

#### Description
Used to get Postworld values in the **wp_postworld_post_meta** table

#### Process:
1. Get an Associative Array of all columns in the **wp_postworld_post_meta** table
2. Keys are column names, values are values.

**return** : *Array*
```php
	'post_id' => {{integer}}
	'author_id'	=> {{integer}}
	'post_class' => {{string}}
	'post_format' => {{string}}
	'link_url' => {{string}}
	'post_points' => {{integer}}
	'rank_score' => {{integer}}
```

#### Usage:
```php
$post_meta = pw_get_post_meta($post_id);
```

------

### pw_set_post_meta ( *post_id*, *$post_meta* )

#### Description
Used to set Postworld values in the **wp_postworld_post_meta** table

#### Parameters:
All parameters, except post_id, are optional.

**$post_id** : *integer* (required)

**$post_meta** : *Array*
- post_class
- post_format
- link_url

#### Usage:
```php
$post_meta = array(
     'post_class' => string,
     'post_format' => string,
     'link_url' => string
);
pw_set_post_meta($post_id, $post_meta);
```



## Points
**/php/postworld-points.php**  
Handles getting and setting points data in the **points** and **post_meta** tables.

------

**POST POINTS**

------

### get_post_points( *$post_id* )
Get the total number of points of the given post from the points column in **wp_postworld_post_meta**

**return** : *integer* (number of points)

------

### calculate_post_points ( *$post_id* )
- Adds up the points from the specified post, stored in **wp_postworld_post_points**
- Stores the result in the points column in **wp_postworld_post_meta**
**return** : *integer* (number of points)

------

### cache_post_points ( *$post_id* ) 
- Calculates given post's current points with `calculate_post_points()`
- Stores points it in **wp_postworld_post_meta** table in the **post_points** column

**return** : *integer* (number of points)

------

**USER POINTS**

------

### get_user_posts_points ( *$user_id* )
- Get the number of points voted to posts authored by the given user
- Get cached points of user from **wp_postworld_user_meta** table **post_points** column

**return** : *integer* (number of points)

------

### calculate_user_posts_points ( *$user_id* )
- Adds up the points voted to given user's posts, stored in **wp_postworld_post_points**
- Stores the result in the **post_points** column in **wp_postworld_user_meta**

**return** : *integer* (number of points)

------

### cache_user_posts_points ( *$user_id* )
- Runs calculate_user_post_points() Method
- Caches value in **post_points** column in **wp_postworld_user_meta** table

**return** : *integer* (number of points)

------

**COMMENT POINTS**

------

### get_user_comments_points ( *$user_id* )

- Get the number of points voted to comments authored by the given user
- Get cached points of user from **wp_postworld_user_meta** table **comment_points** column

**return** : *integer* (number of points)

------

### calculate_user_comments_points ( *$user_id* )
- Adds up the points voted to given user's comments, stored in **wp_postworld_comment_points** table
- Stores the result in the **post_points** column in **wp_postworld_user_meta** table

**return** : *integer* (number of points)

------

### cache_user_comments_points ( *$user_id* )
- Runs `calculate_user_comment_points()` Method
- Caches value in **comment_points** column in **wp_postworld_user_meta** table

**return** : *integer* (number of points)

------

**GENERAL POINTS**

### set_post_points( *$post_id, $user_id, $add_points* )

#### Process
1. $add_points is an integer
2. Write row in **wp_postworld_points** table
3. Passing **0** deletes row
4. Check that user role has permission to write that many points <<<< HAIDY
5. Check that user has not voted too many times recently <<<< Concept method <<< PHONG
6. Check is the user has already voted points on that post
7. Also update cached points in **wp_postworld_post_meta** directly
8. Add Unix timestamp to time column in **wp_postworld_post_points**

**return** : *Object*
``` php
     'points_added' => {{integer}} // (points which were successfully added)
     'points_total' => {{integer}} // (from wp_postworld_meta)
```

------

### has_voted_on_post ( *$post_id, $user_id* ) 
- Check **wp_postworld_points** to see if the user has voted on the post
- Return the number of points voted

**return** : *integer* (number of points voted)

------

### has_voted_on_comment ( *$comment_id, $user_id* ) 
- Check **wp_postworld_comment_points** to see if the user has voted on the comment
- Return the number of points voted

**return** : *integer*

------

### get_user_points_voted_to_posts ( *$user_id* )
- Get total points voted to posts authored by the given user
- Get points of each post from **wp_postworld_post_meta**
- Add all the points up

**return** : *integer* (number of points)

------

### get_user_votes_on_posts ( *$user_id* )
- Get all posts which user has voted on from **wp_postworld_post_points** table 

**return** : *Object*
``` php
	#for_each
	'post_id' => {{integer}}
	'votes' => {{integer}}
	'time' => {{timestamp}}
```

------

### get_user_votes_report ( *$user_id* )
#### Description
- Returns the 'recent/active' points activity of the user

#### Process
1. Get all posts which user has recently voted on from `wp_postworld_post_points ( total_posts )`
2. Add up all points cast (total_points)
3. Generate average (total_points/total_posts) 

**return** : *Object*
``` php
     'total_posts' => {{integer}} //(number of posts voted on)
     'total_points' => {{integer}} //(number of points cast by up/down votes)
     'average_points' => {{decimal}} //(average number of points per post)
```

------

### get_user_vote_power ( *$user_id* )
- Checks to see user's WP roles `with get_user_role()`
- Checks how many points the user's role can cast, from **wp_postworld_user_roles** table, under **vote_points** column

**return** : *integer* (the number of points the user can cast)

------

## Rank Scores

**php/postworld-rank.php**  
Contains functions which are used to read and write Rank Scores for posts.  
Rank Scores, in brief, are calculated by an equation using the number of points and comments divided by the age of the post.  
The Rank Score equation also involves several other curves and 'Currents' which help sort posts based on popularity, similar to Reddit.

------

### get_rank_score ( *$post_id, [$method]* )
- Gets the Rank Score of the given post, using `calculate_rank_score()`
- Retrieves from the **rank_score** column in **wp_postworld_meta**

**return** : *integer* (Rank Score)

------

### calculate_rank_score ( *$post_id* )
- Calculates Rank Score based on rank equation
- Returns the Rank Score 

**return** : *integer* (Rank Score)

------

### cache_rank_score ( *$post_id* )
- Calculate rank_score with `calculate_rank_score()` method
- Cache the result in **wp_postworld_meta** in the **rank_score** column

**return** :  *integer* (Rank Score) 

------

## Caching & Cron Tasks


### cache_all_points ()
- Runs cache_user_points() and cache_post_points()

**return** : *cron_logs Object* (add to table wp_postworld_cron_logs)

------

### cache_all_user_points()
- Cycles through all users with cache_user_points() method

**return** : *cron_logs* Object (add to table wp_postworld_cron_logs)

------

### cache_all_post_points()
- Cycles through each post in each post_type with points enabled
- Calculates and caches each post's current points with cache_post_points() method

**return** : *cron_logs* Object (add to table wp_postworld_cron_logs)

------

### cache_all_comment_points()
- Cycles through all columns
- Calculates and caches each comment's current points with cache_comment_points() method

**return** : *cron_logs* Object (add to table wp_postworld_cron_logs)

------

### cache_all_rank_scores ()
- Cycles through each post in each post_type scheduled for Rank Score caching
- Calculates and caches each post's current rank with cache_rank_score() method
**return** : *cron_logs* Object (add to table wp_postworld_cron_logs)

------

### cache_all_feeds ()
- Run pw_cache_feed() method for each feed registered for feed caching in WP Options
**return** : *cron_logs* Object (store in table wp_postworld_cron_logs)

------

### clear_cron_logs ( *$timestamp* )
- Count number of rows in wp_postworld_cron_logs (rows_before)
- Deletes all rows which are before the specified timestamp (rows_removed)
- Count number of rows after clearing (rows_after)

**return** : *Object*
``` php
	'rows_before' => {{integer}}
	'rows_removed' => {{integer}}
	'rows_after' => {{integer}}
```

------

### Objects Anatomy : 

#### cron_logs *Object*
``` php
	'type' => {{feed/post_type}}
	'query_id' => {{feed id / post_type slug}}
	'time_start' => {{timestamp}}
	'time_end' => {{timestamp}}
	'timer' => {{milliseconds}}
	'posts' => {{number of posts}}
	'timer_average' => {{milliseconds}}
	'query_vars' => {{ query_vars Object }}
```

#### query_vars *Object*
``` php
	'post_type' => {{string}}
	'class' => {{string}}
	'format' => {{string}}
	etc...
```

------

## Query
**php/postworld_query.php**

Here are custom querying functions. These greatly expand upon the WP_Query functions, and gives us access to all the wp_postworld database tables.   
Each function effectively also populates a Wordpress query session, so can be used like WP_query, when 

------

### pw_query( *$args, [$return_format]* ) 

####Description:

- Similar to the functionality of WP_Query : http://codex.wordpress.org/Class_Reference/WP_Query 
- Queries by Postworld data fields
- Additionally sort by **post_points** & **rank_score**
- Define which fields are returned using `pw_get_posts()` method
- Can determine the **return_format** as **JSON, PHP Associative Array or WP Post Object**


#### Process:
1. After querying and ordering is finished, if more than IDs are required to return, use `pw_get_posts()` method to return specified fields

**return** : *ARRAY_A / JSON / WP_Query*

#### Parameters: *$args*

------

**QUERYING :**

------

**post_type** : *string / Array*
- post_type column in wp_posts
  - **string** - Return posts with that post_type
  - **Array** - Return posts in either post_type (IN/OR operator)

**post_format** : *string / Array*
- post_format column in wp_postworld_post_meta 
  - **string** - Return posts with that post_type
  - **Array** - Return posts in either post_type (IN/OR operator) 

**post_class** : *string / Array*
- post_class column in wp_postworld_post_meta
  - **string** - Return posts with that post_type
  - **Array* - Return posts in either post_type (IN/OR operator) 

**author** : *integer / Array* 
- Use author ID
  - **integer** - Return posts only written by that author
  - **Array** - Return posts written by any of the authors (IN/OR operator) 

**author_name** : *string / Array*
- Use 'user_nicename' in wp_users (NOT 'name')
  - **string** - Return posts only written by that author
  - **Array** - Return posts written by any of the authors (IN/OR operator) 

**year** : *integer*
- 4 digit year (e.g. 2011)
- Return posts within that year

**month** : *integer*
- Month number (from 1 to 12)
- Return posts within that month

**tax_query** : *array*
- Just like : http://codex.wordpress.org/Class_Reference/WP_Query#Taxonomy_Parameters

**s** : *string*
- Search terms
- Query **post_content** & **post_title** columns in **wp_posts** table

------

**ORDERING :**

------

**orderby** : *string*
- Options
  - date (default)
  - rank_score
  - post_points
  - modified
  - rand
  - comment_count

**order** : *string*
- Options
  - DESC (default)
  - ASC

------

**RETURNING :**

------

**offset** : *integer*
- Number of post to displace or pass over. 

**post_count** : *integer*
- Maximum number of posts to return.
  - 0 (default) - Return all

**fields** : *string / Array*
- Set return values. Uses pw_get_posts( $post_ids, $fields ) method
- Pass this directly to `wp_get_posts()` method unless the value is 'id'
  - **ids** (default) - Return an Array of post IDs
  - **all** - Return all fields
  - **preview** - Return basic fields
  - `array( 'post_title', 'post_content', … )` - Array of fields which to return

**$return_format** : *string*
- Options
  - **WP_QUERY** (default) - Return a [WP_Post Object](http://codex.wordpress.org/Class_Reference/WP_Post )
  - **JSON** - Return a JSON Object
  - **ARRAY_A** - Return an Associative Array


####Usage:
``` php
$args = array(
	'post_type' => array('post'),
	'year' => '2013',
	'month' => '12',
	'post_format' => 'standard',	// post_format column in wp_postworld_meta 
	'post_class' => 'editorial',  	// post_class column in wp_postworld_meta

	'author' => '1',

	'tax_query' => array(
		array(
			'taxonomy' => 'category',
			'field' => 'slug',
			'terms' => 'books'
		)
	),
	's' => 'search string',

	'orderby' => 'rank_score',
	'order' => 'ASC'

	'posts_per_page' : '20',

	fields : array('ID','post_title','post_content','post_date'), // See pw_get_post() $fields method
);

$posts = pw_query( $args, 'JSON' );

```

------

pw_user_query( $args );

Description:

• Similar to WP_User_Query : http://codex.wordpress.org/Class_Reference/WP_User_Query 
• Queries users in wp_users table

return : PHP Associative Array / JSON Object




Parameters:

role : string
• Use 'User Role'


s : string
• Query : table : wp_users, columns: user_login, user_nicename, user_email, user_url, display_name


location_country : string
• Query wp_postworld_user_meta table column location_country

location_region : string
• Query wp_postworld_user_meta table column location_region 

location_city : string
• Query wp_postworld_user_meta table column location_city

location : string
• Query location_country, location_city, and location_region






orderby : string
     • 'post_points' - Points to the user's posts
     • 'comment_points' - Points to user's comments
     • 'display_name' - Use Display Name, alphabetical
     • 'date' - Date joined

     • 'location_country' - Alphabetical by Country




order : string
     • ASC (default)
     • DESC



Usage:



$args = array(
     location_country : {{search_terms}}
     location_region : {{search_terms}}
     location_city : {{search_terms}}
     location : {{search_terms}}


     role : {{string}}


     s : {{search_terms}}


     orderby : {{string}}
     order : {{string}}


     fields : array(id) // default ids only // use get_user_data() method
     return_format : {{ARRAY / JSON}}
     
);







PHP / USER FUNCTIONS
Here we have a series of functions which are used to read and write custom user meta data.





get_user_data ( $user_id, $fields )

• Gets meta data from the wp_postworld_user_meta table

Parameters:
$user_id : integer


$fields : Array

• Possible values:
     • viewed
     • favorites
     • location_country
     • location_region
     • location_city


return : Array (keys: fields, values: values)



set_user_data ( $user_id, $field, $value )
• Adds data to the wp_postworld_user_meta table, under column named '$meta_key'
return : boolean
     • true - If successful
     • false - If user, or column doesn't exist or if value is wrong content type



set_favorite ( $post_id, $user_id, $add_remove )
• Add or remove the given post id, from the array in favourites column in wp_postworld_user_meta of the given user
• Add or remove row in pw_postworld_favorites, with user_id and post_id
• If it was added or removed, add 1 or subtract 1 from table wp_postworld_post_meta  in column favorites



Parameters:

$add_remove
     •  1 - add it to favourites
     • -1 - remove it from favorites


return :
     1  - added successfully
     -1 - removed successfully
     0  - nothing happened



get_favorites ( $user_id )
• Return array from the favourites column in wp_postworld_user_meta of the given user
return : array (of post ids)



is_favorite ( $post_id, $user_id )
• Checks the favorites column in wp_postworld_user_meta of the given user to see if the user has set the post as a favorite
return : boolean




set_viewed ( $user_id, $post_id, $viewed )
• Adds to removes to the array in has_viewed in wp_postworld_user_meta 


Parameters:

$viewed
     • true - check if the post_id is already in the array. If not, add it.
     • false - check if the post_id is already in the array. If so, remove it.
return : boolean (true)




get_viewed ( $user_id )
• Gets list of posts by id which the user has viewed
return : array




has_viewed ( $user_id, $post_id )
• Checks to see if user has viewed a given post
• Values stored in array in has_viewed in wp_postworld_user_meta
return : boolean 




has_shared ( $user_id, $post_id )
...




get_user_location ( $user_id )
• From 'location_' columns in wp_postworld_user_meta
return : Object
     city : {{city}}
     region : {{country}}
     country : {{country}}


get_client_ip ()
return : IP address of the client


get_user_role ( $user_id, [$return_array] )
• Returns user role(s) for the specified user


Parameters:
$return_array : boolean
     • false (default) - Returns a string, with the first listed role
     • true - Returns an Array with all listed roles


return : string / Array (set by $return_array)






PHP / FEEDS




pw_live_feed ( $args ) << REQUIRES pw_query


Description:

• Used for custom search querying, etc.
• Does not access wp_postworld_feeds caches at all
• Helper function for the pw_live_feed() JS method


Parameters:



feed_id : string


preload : integer
• Number of posts to fetch data and return as post_data


feed_query : Array
• pw_query() Arguments


Process:
• Generate return feed_outline, with pw_feed_outline( $args[feed_query] ) method
• Generate return post data by running the defined preload number of the first posts through pw_get_posts( feed_outline, $args['feed_query']['fields'] )


Usage:
$args = array (

     'feed_id' => string,
     'preload'  => integer
     'feed_query' => array(
          pw_query args    
     )
)



return : Object
     feed_outline : [ … ]
     post_data : { … }




pw_register_feed ( $args )


Description:

• Registers the feed in wp_postworld_feeds table


Process:
• If the feed_id doesn't appear in the wp_postworld_feeds table :
     • Create a new row
     • Enable write_cache


• Store $args['feed_query'] in the feed_query column in wp_postworld_feeds table as a JSON Object


• If write_cache is true, run pw_cache_feed(feed_id)

return : $args Object


Parameters : $args


feed_id : string

feed_query : object
default : none
The query object which is stored in feed_query in wp_postworld_feeds, which is input directly into postworld_query

write_cache : boolean
• If the feed_id is new to wp_postworld_feeds table, set write_cache = true


     • true - Cache the feed with method : run pw_cache_feed( $feed_id )
     • false (default) - Wait for cron job to update feed outline later, just update feed_query


Usage :


$args = array (
     'feed_id' => 'front_page_feed',
     'write_cache'  => boolean
     'feed_query' => array(
          postworld_query args    
     )
)





pw_feed_outline ( $pw_query_args ) << REQUIRES pw_query
• Uses pw_query() method to generate an array of post_ids based on the supplied $pw_query_args


Parameters:



$pw_query_args : Array

• pw_query() Arguments




Process:



• Over-ride feed_query['fields'] variable to 'id'
• Flatten pw_query to Array of post_ids

return : Array (of post IDs)



pw_cache_feed ( $feed_id ) << REQUIRES pw_query
• Generates a new feed outline for a registered feed_id and caches it

Process:
• Run pw_feed_outline( $args ) on the args in feed_query column in the row of the given $feed_id
• Store as comma delineated list of IDs in the feed_outline column of wp_postworld_feeds table


return : Array (of post IDs) 




pw_get_feed ( $feed_id, [$preload] ) << REQUIRES pw_query

Parameters:

$feed_id : string

$preload : integer (optional, default : 0)
• The number of posts to pre-load with post_data


Process:
• Return an object containing all the columns from the wp_postworld_feeds table
• If $preload (integer) is provided, then use pw_get_posts on that number of the first posts in the feed_outline, return in post_data Object
• Use fields value from feed_query column under key fields 

return : Array
     feed_id
     feed_query
     time_start
     time_end
     timer
     feed_outline
     post_data





pw_get_posts ( $post_ids, $fields )
• Run pw_get_post on each of the $post_ids, and return the given fields in an Array


Parameters:

$post_ids : array

• An array of post IDs


$fields : array
• Corresponds to pw_get_post() fields parameter


Process:

• Get the feed outline
• Select which posts to preload
• Preload selected posts


return : Array
     {"1":{"post_title":"Post Title","post_type":"post","post_format":"standard"},"3":{...}}


Requirements:
• pw_get_post() PHP Method
• pw_query() PHP Method
• pw_feed_outline() PHP Method




pw_get_post ( $post_id, $fields, [$user_id] ) <<< PHONG
• Gets data fields for the specified post, including post world meta data and custom sized thumbnails

Parameters:
$post_id : integer


$user_id : integer
• ID of the currently logged in user, for user-view specific data (points voted, etc)




$fields : string / Array


     • 'all' (default) 
     • 'preview' - Returns a basic set of commonly usable fields
          • ID, post_title, post_excerpt, post_permalink, post_path, post_type, post_date, post_time_ago, comment_count, link_url, 


     • Array - Use any of the following values in an Array :


WP GET_POST METHOD : http://codex.wordpress.org/Function_Reference/get_post 


     • ID (default always)
     • post_author

     • post_date
     • post_date_gmt

     • post_title 
     • post_content
     • post_excerpt
     • post_path
     • post_name
     • post_type
     • post_status
     • … ( all get_post return values )


WORDPRESS


     • post_permalink


POSTWORLD


     • post_points
     • rank_score
     • post_format
     • post_class
     • link_url


TAXONOMIES

     • taxonomy( tax_slug ) - Returns taxonomy terms array for the post
          • return : array - returns an Associative Array with each :
               term =>  {{string}}
               url => {{string}}



VIEWER SPECIFIC


     • user_vote - number of points the user has voted for this post
     • 



IMAGE


     Registered Image Sizes
     • image( registered_image_size_name[ thumbnail / medium / large / full ]  )
          example : image( medium ) - returns the registered 'medium' image size
         
     Custom Image Sizes
     • image( handle, width, height, hard_crop )
          example : image( banner, 700, 100, 1 )


AVATAR



     Avatar Images
     • avatar( handle, size )
          example : avatar( small, 48 )




AUTHOR



     • post_author_name
     • post_author_link
     • post_author_website
     • post_author_description
     • post_author_social
     • post_author_nicename
     • edit_post_link


DATE & TIME



     • post_time_ago : http://www.devnetwork.net/viewtopic.php?f=50&t=113253 


Process:

• Mixed Methods for retrieving data


return : OBJ / JSON






pw_get_templates ( $templates_object )
• Gets an Object of template paths based on the provided object

Parameters:


$templates_object : Object (optional)

     • post_types : Array (optional) - Array of post_types which to return template paths for
          default : Get all registered post types with get_post_types() WP Method :
         get_post_types( array( array( 'public' => true, '_builtin' => false ) ), 'names' )

     • post_views : Array (optional) - Array of 'feed views' which to retrieve templates for
          default : ( list, detail, grid, full )


Process:


POST TEMPLATES


• Default posts template path :
     /plugins/postworld/templates/posts



• Over-ride post template path:
     /theme_name/postworld/templates/posts

• Generate list of template names
     {{post_type}}-{{post_view}}.html
     post-list.html
     post-detail.html
     etc…


• For each template name, check over-rides path for templates with file_exists() PHP Method

• If template post_type doesn't exist, fallback:
     post_type >> post
     link-list.html >> post-list.html

• If template post_view fallback doesn't exist, fallback to default templates path
     post_view >> defaults
     /theme_name/postworld/templates/post-list.html >> /plugins/postworld/templates/post-list.html


• Gather all the template files into an object




PANEL TEMPLATES



• Default panels template path :
     /plugins/postworld/templates/panels



• Over-ride panels template path:
     /theme_name/postworld/templates/panels

• Generate an Associative Array of all the .HTML files in both the Default and Over-ride template folders
     • Key is file_name without the HTML extension, value is the path relative to base domain
   
• Merge the arrays, so that the Over-ride paths overwrites the Default paths




return : Array (with all template paths)

Usage:
     
pw_get_templates ($args);


$args = array(
     'post_types' => array( 'post', 'link' ),
     'post_views' => array( 'grid', 'list', 'detail', 'full' )
);




Return:

Array - With post_views nested within post_types


Example : 
{
posts : {
     'post' : {
          'list' : '/wp-content/plugins/postworld/templates/posts/post-list.html',
          'detail' : '/wp-content/plugins/postworld/templates/posts/post-detail.html',
          'full' : '/wp-content/theme_name/postworld/templates/posts/post-full.html',
          },
     },
panels : {
     'feed_top': '/wp-content/plugins/postworld/templates/panels/feed_top.html',
     'front_page': '/wp-content/theme_name /postworld/templates/panels/front_page.html',
     },
};










PHP / USER KARMA FUNCTIONS


get_user_view_karma ( $user_id )
• Gets current View Karma for given user
• Calculate based on builtin view_karma equation
return : integer


get_user_share_karma ( $user_id )
• Gets current Share Karma for given user
• Calculate based on builtin share_karma equation
return : integer




set_shared ( $user_id, $post_id, $ip_address )
• Check URL query_vars for user id
• See if user id exists
• Get selected post id
• Check IP address against list of IPs stored in recent_ips in wp_postworld_user_shares
• If the IP is not in the list, add to the list and add 1+ to total_views in wp_postworld_user_shares
• If the IP is in the list, do nothing
• If the array length of IPs is over {{100}}, remove old IPs
return : boolean (true if added karma, false if not)




user_share_report ( $user_id )
• Lookup all post shared by user id
• In wp_postworld_user_shares, return numerically ordered list of post IDs 

• Run get_post_data method on each post ID, for (title, permalink)
return : Object
     id : {{post_id}}
     title : {{post_title}}
     permalink : {{post_permalink}}
     views : {{integer}} (number of views user has lead to this post)


post_share_report ( $post_id )
…



PHP / POST CREATION FUNCTIONS

pw_insert_post ( $post )
• Extends wp_insert_post : http://codex.wordpress.org/Function_Reference/wp_insert_post 
• Include additional Postworld fields as inputs


Parameters : $post Object (In addition to wp_insert_post )
• post_class
• post_format
• link_url
• external_image

return : post_id (if added to the database, otherwise return WP_Error Object)


pw_update_post ( $post )
• Extends wp_update_post : http://codex.wordpress.org/Function_Reference/wp_update_post
• Include additional Postworld fields as inputs





