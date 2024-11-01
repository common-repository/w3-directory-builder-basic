<?php

function w3db_create_edit_link($data){
	global $current_user;
	$userID = intval($current_user->ID);
	

	$result = array();

	// if user not logged in (or URL empty), end function.
	if ( is_user_logged_in() ) {

		if (isset($data["url"]) && !empty($data["url"])) {
			// if pid is found, user is trying to edit post
			if(isset($data["pid"]) && !empty($data["pid"])){
				$data["pid"] = intval($data["pid"]);
			}
		}
		
		// content shouldn't be empty, but if for some reason it is...
		if(empty($data["content"])) {
			$data["content"] = "nt";
		}
		
		
		$args = array();
		
		if(isset($data["pid"]) && !empty($data["pid"])) $args["ID"] = intval($data["pid"]);

		$args["post_type"] = "post";
		$args["post_title"] = wp_strip_all_tags( $data["title"] );
		$args["post_content"] = $data["content"];
		$args["tags_input"]	= $data["post_tag"];
		$args["post_category"] = (array) $data["category"];
		$args["post_status"] = "publish";
		
		
			
		// If $data["pid"] is set, edit the relevant post
		if(isset($args["ID"])){ // UPDATE POST
	
			// Technically wp_insert_post can be used to update posts, but really it re-inserts the post with a new timestamp.
			wp_update_post($args);

			$post_id = intval($data["pid"]);
			
			
		} else { // CREATE POST
		
			$post_id = wp_insert_post($args);

			# SET URL
			// update_post_meta will create field if it doesn't already exist.
			update_post_meta($post_id, "w3db_url", $data["url"]);

			# SET INITIAL VOTE VALUES
			// REMEMBER: An initial vote and rank is generated for new posts, but this is not stored in the user vote table (just for the same of keeping data tidy and meaningful).  
		
			if(function_exists("w3vx_rankify")){
				update_post_meta( $post_id, 'w3vx_count', 1 );
				update_post_meta( $post_id, 'w3vx_upvotes', 1 );
				update_post_meta( $post_id, 'w3vx_rating', 100 );				
				
				w3vx_rankify($post_id, "post");
			}
		}
		

		wp_redirect( get_permalink($post_id) );
		exit;
	}
}


add_action( 'admin_post_w3db_link_form_handler', 'w3db_link_form_handler' );

function w3db_link_form_handler(){
	$post = $_POST;
	$data = array();
	
	$data["pid"] = isset($post["pid"]) ? intval($post["pid"]) : null;
	$data["url"] = isset($post["url"]) ? esc_url_raw($post["url"]) : null;
	$data["title"] = isset($post["title"]) ? sanitize_text_field($post["title"]) : null;
	$data["content"] = isset($post["content"]) ? sanitize_textarea_field($post["content"]) : null;
	$data["category"] = isset($post["category"]) ? intval($post["category"]) : null;
	$data["post_tag"] = isset($post["post_tag"]) ? sanitize_text_field($post["post_tag"]) : null;

	w3db_create_edit_link($data);
	exit;
}
