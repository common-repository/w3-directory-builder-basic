<?php
// Eventually this functionality should be updated to work via AJAX.
function w3dbff_post_handler(){
	global $w3dbff_FORMS;

	$form_name = sanitize_text_field($_POST["w3dbff_form_name"]);
	$tab_name =  sanitize_text_field($_POST["w3dbff_tab"]);
	$redirect =  isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';


	if(check_admin_referer( "w3dbff_form_".$form_name ) && current_user_can("manage_options")){ // this assumes the form uses the default nonce field name, _wpnonce
		$dbValues = get_option("w3dbff_settings");// mega value contaning w3dbff wp-admin settings
		$dbValues = json_decode($dbValues, true);
		
		if(!isset($dbValues)){
			$dbValues = array();
		}
		
		// Even though form values match backend values, we shouldn't blindly trust user input. So let's match form key/values against defined keys.
		// in the db values that belong to a fieldset repeater and are using the key of the fieldset.
		foreach($w3dbff_FORMS[$form_name]["fields"][$tab_name] as $fieldsetKey => $fieldset){
			$keys[] = $fieldsetKey;			
		
			foreach($fieldset as $field) {
				$keys[] = $field["name"];			
			}
		}
		
		foreach($keys as $k){
			$v = isset($_POST[$k]) ? $_POST[$k] : null;		
			$v = is_array($v) ? array_map("sanitize_text_field", $v) : sanitize_text_field($v);
			$dbValues[$form_name][$tab_name][$k] = $v;
		}
	
		$dbValues = json_encode($dbValues);
		update_option("w3dbff_settings", $dbValues);
		
		$redirect  = add_query_arg( array(
			'status' => "success",
		), $redirect );
	
	} else {
		$redirect  = add_query_arg( array(
			'status' => "failed",
		), $redirect );
	}
	
	wp_redirect($redirect);
	
}
add_action( 'admin_post_nopriv_w3dbff_post_handler', 'w3dbff_post_handler' );
add_action( 'admin_post_w3dbff_post_handler', 'w3dbff_post_handler' );
