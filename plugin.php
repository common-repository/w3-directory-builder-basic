<?php
/**
Plugin Name: W3 Directory Builder (Basic)
Version: 1.0.8
Author: W3Extensions
Author URI: https://w3extensions.com/
Plugin URI: https://wordpress.org/w3-directory-builder-basic/
Description: Turn WordPress into a directory/classifieds/bookmarking site.
Contributors: bookbinder
Tags: directory,bookmarking,links,classifieds,front end post
Requires at least: 4.7
Tested up to: 4.9.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

require_once("inc/create-edit-link.php");
require_once('w3ff.php');
require_once('settings/autoloader.php');

# ACTIVATION
register_activation_hook(__FILE__, 'w3db_activation');

function w3db_activation() {
	// DEFAULT POST/EDIT PAGE
	$settings = json_decode(get_option("w3dbff_settings"), true);
	
	// Create a default page upon activation (making sure not to create additional pages when the plugin is deactivated and then reactivated).
	// If w3db settings do not exist, we can assume that a default page has not been created.
	if(empty($settings)){
		$args = array(
			"post_type" => "page",
			"post_title" => "Post/Edit Form",
			"post_content" => "[w3db-post-form]",
			"post_status" => "publish"
		);
		
		$page_id = wp_insert_post($args);
		
		$array = array();
		
		$array["w3dbff-settings"]["general_tab"]["form_page"] = $page_id;
		
		update_option("w3dbff_settings", json_encode($array));
	}	
}




function w3dbff_get_settings_value($tab, $key, $default = null){
	global $w3dbff_FORMS;
	
	$dbValues = get_option("w3dbff_settings");// mega value contaning w3dbff wp-admin settings
	
	if(isset($dbValues)){
		$dbValues = json_decode($dbValues, true);
		
		// REMEMBER: repeaters are an array of values stored like regular fields. Repeater values will need to be further processes (as needed) once return via this function.
		return isset($dbValues["w3dbff-settings"][$tab][$key]) ? $dbValues["w3dbff-settings"][$tab][$key] : null;
	}
	
	if(empty($formValues)){ // if saved values are not found, return form default
		foreach($w3dbff_FORMS["w3dbff-settings"]["fields"]["settings_tab"] as $k => $v){
			if($key == $k){
				return $v;
			}
		}		
	}
	
	// if no other returns found, return $default (user/developer-defined fallback value)
	return $default;	
}


function w3dbff_get_plugin_directory(){
	$directory = array();
	
	$directory['path'] = trailingslashit( plugin_dir_path( __FILE__ ) );
	$directory['url'] = plugin_dir_url( __FILE__ );
	return $directory;
}

function w3db_get_plugin_directory(){
	$directory = array();
	
	$directory['path'] = trailingslashit( plugin_dir_path( __FILE__ ) );
	$directory['url'] = trailingslashit( plugin_dir_url( __FILE__ ) );
	return $directory;
}

function w3db_scripts() {
	$pluginDirectory = w3db_get_plugin_directory();

	# CSS
	wp_register_style( 'w3db',  $pluginDirectory['url'].'assets/css/app.css', array(), 1 );

	wp_enqueue_style('w3db');
	

	# JS
	wp_register_script( "w3db", $pluginDirectory['url'].'assets/js/app.js', array("jquery"), null, true );
	
	wp_enqueue_script('jquery');
	wp_enqueue_script('w3db');
	
	wp_localize_script( 'w3db', 'w3db_ajax', array(
		'ajaxurl' => site_url() . '/wp-admin/admin-ajax.php',
	) );	
}

add_action ('wp_enqueue_scripts', 'w3db_scripts', 1000);


function w3db_post_form_shortcode() {
	return w3db_get_post_form();
}
add_shortcode('w3db-post-form', 'w3db_post_form_shortcode');

function w3db_get_post_form(){
	include_once(w3db_get_plugin_directory()["path"]."inc/post-form.php");	
}


function w3db_modify_main_query($query) {
	$post_types = (array) w3dbff_get_settings_value("general_tab", "sortbar_post_types", "post");
	$default_order = w3dbff_get_settings_value("general_tab", "default_order", "date");

	
	// If Votify is active, we can safely order by "meta_value_num" (which is really the custom field "w3vx_rank").
	if(!function_exists("w3vx_update_vote")) return $query;
	
	// When the post type is "post"... is_post_type_archive() won't detect the the post index as at the post archive page. 
	// So we need to create some special rules.
	
	if(
		(
			!isset($query->query_vars["orderby"]) &&		
			$default_order == "meta_value_num" &&
			$query->is_post_type_archive($post_types) &&
			$query->is_main_query()
			
			||

			!isset($query->query_vars["orderby"]) &&
			$default_order == "meta_value_num" &&			
			in_array("post", $post_types) &&
			$query->is_front_page() &&
			$query->is_main_query()

			||
			
			// in this condition, we won't restrict by post type since post can be sorted non post_type-related archives (e.g. author archive)
			isset($query->query_vars["orderby"]) &&
			$query->query_vars["orderby"] == "meta_value_num" &&
			$query->is_main_query()
		)
		
		&&
		
		!$query->is_admin()		
	){
		$query->query_vars["orderby"] = "meta_value_num";
		$query->query_vars["meta_key"] = "w3vx_rank";
	}
}
add_action("pre_get_posts", "w3db_modify_main_query");


function w3db_get_sortbar($currentOrderBy, $currentOrder){
	$options = w3dbff_get_settings_value("general_tab", "order_options", array("meta_value_num", "date_published", "date_modified", "title"));


	$orderByList = array(
		"meta_value_num" => "Trending",
		"date_published" => "Date Published",
		"date_modified" => "Date Modified",
		"title" => "Title"
	);
	
	$orderByHTML = "";
	foreach($orderByList as $value => $label){
		if(!in_array($value, $options)){
			continue;
		}
		
		//once we find the appropriate value, stop checking
		if($value == $currentOrderBy){
			$selected = " selected='selected'";
		} else {
			$selected = null;
		}
		
		$orderByHTML .= "<option value='".$value."' ".$selected.">".$label."</option>";
	}



	# ORDER HTML
	// wordpress expects key to be uppercase
	$orderList = array(
		"ASC" => "ASC",
		"DESC" => "DESC"
	);
	
	$orderHTML = "";
	
	foreach($orderList as $v => $l){
		//once we find the appropriate value, stop checking
		if($v == $currentOrder){
			$selectedOrder = " selected='selected'";
		} else {
			$selectedOrder = null;
		}
		
		$orderHTML .= "<option value='".$v."' ".$selectedOrder.">".$l."</option>";
	}	
	
	
	$html = "<form method='GET' class='w3db-sortbar'>
				<label>Order By</label>
				<select name='orderby' class='w3db-sortbar-select'>"
					.$orderByHTML.
				"</select>
				
				<label>Order</label>
				<select name='order' class='w3db-sortbar-select'>"
					.$orderHTML.
				"</select>				
			</form>";
					
	return $html;
}


function w3db_prepend_sortbar( $query ){
	$post_types = (array) w3dbff_get_settings_value("general_tab", "sortbar_post_types", "post");
	
	if($query->is_post_type_archive($post_types) ||	in_array("post", $post_types) && $query->is_home() && $query->is_main_query()){

	    if(isset($query->query_vars["orderby"])){
	    	$selectedOrderBy = $query->query_vars["orderby"];
	    } else {
	    	$selectedOrderBy = w3dbff_get_settings_value("general_tab", "default_order", "meta_value_num");
	    }
	    
	    if(isset($query->query_vars["order"])){
	    	$selectedOrder = $query->query_vars["order"];
	    } else {
	    	$selectedOrder = "DESC"; //wp expects upper case value for "order"
	    }	    

	    echo w3db_get_sortbar($selectedOrderBy, $selectedOrder);
	}
}
add_action( 'loop_start', 'w3db_prepend_sortbar' );




function w3db_post_term_links($taxonomy, $pid, $separator = ", ", $terminator = null, $type = "link"){
	// NOTE: $terminator is especially handy since it will only be appened if term results are found.
	
	$html = "";
	$result = wp_get_post_terms($pid, $taxonomy, array("fields" => "all"));
	$counter = 0;
	
	if(!empty($result)){
		foreach($result as $r){
			$counter++;
			
			if($type == "link"){
				$url = get_term_link($r->term_id, $taxonomy);		
				$html .= "<a href='".$url."'>".$r->name."</a>";
			} else if ($type = "tag"){
				$html .= $r->name;
			} else if ($type = "hashtag"){
				$html .= "#".strtolower(str_replace(' ','',$r->name)); 
			}
			
			
			// don't append comma if there is only one item (or if this item is the last one). 
			if(count($result) > 1 && $counter !== count($result)){
				$html .= $separator;
			} 
			
			if(count($result)){
				$html .= $terminator;
			}
		}
	}

	return $html;
}

function w3db_get_submit_button($label = "Submit Link", $class = null){
	$page_id = w3dbff_get_settings_value("general_tab", "form_page");
	
	if(!isset($page_id) || $page_id == 0) return;
	
	$page_url = get_permalink($page_id);
	
	$html = '<a href="'.$page_url.'" class="newLinkButton '.$class.'">'.$label.'</a>';
	
	return $html;
}


function w3db_get_edit_link($post_id){
	if(is_user_logged_in()){
		$page_id = w3dbff_get_settings_value("general_tab", "form_page");	
		$page_url = add_query_arg( 'post', $post_id, get_permalink($page_id) );

		if(!isset($page_id) || $page_id == 0) return null;
	
		$html =  ' <a class="post-edit-link" data-pid="'.$page_id.'" href="'.$page_url.'">[EDIT]</a>';				
	} else {
		$html = null;
	}
	
	return $html;
}

add_filter('wp_nav_menu_items', 'w3db_append_submit_link', 10, 2);
function w3db_append_submit_link($items, $args){
	$menus = (array) w3dbff_get_settings_value("general_tab", "append_submit_link");

    if( in_array($args->theme_location, $menus) ){
        $items .= '<li>'.w3db_get_submit_button().'</li>';
    }
    return $items;
}




function w3db_external_link_filter($url){
	//$post doesn't return an ID for some reason.

	//add_filter('post_type_link',... and add_filter('post_link', would filter both get_permalink and the_permalink. Filtering get_permalink would make it difficult to get the real permalink when needed. So we'll stick to filtering the_permalink even though it provides only the post ID whereas the other options provide the $post object.
	// also note, post_link is for the "post" post type and "post_type_link" is for custom post types. (I wasted an hour before figuring this out. -- Stupid shit like this, is why developers hate WordPress.)

	$url_settings = (boolean) w3dbff_get_settings_value("general_tab", "replace_permalink", true);

	if(!$url_settings) return $url;
	
	$post_types = (array) w3dbff_get_settings_value("general_tab", "post_types");

	$post_id = url_to_postid( $url );
	$type = get_post_type($post_id);
	
	if(!in_array($type, $post_types)) return $url;
	
	$external_url = get_post_meta($post_id, "w3db_url", true);
	$url = (isset($external_url) && !empty($external_url)) ? $external_url : $url;
	
	return $url;
}
add_filter('the_permalink', 'w3db_external_link_filter', 10, 3);