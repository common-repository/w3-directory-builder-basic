<?php

// Version 0.3

function w3dbff_register_form($args, $type, $form_name) {
    global $w3dbff_FORMS;

	//$type = tabs or fieldsets or fields  
    if (!is_array($args)) return;
    $w3dbff_FORMS[$form_name][$type] = $args;
}


function w3dbff_get_all_acf_fields() {
	$groupIDs = get_posts( 
		 array(
			'posts_per_page'   => -1,
			'offset'           => 0,
			'post_type'        => 'acf',
			'post_status'      => 'publish',
			'suppress_filters' => true 
		)
	);

	$acf_fields = array();
	
	foreach($groupIDs as $group ){
		$acf_meta = get_post_custom( $group->ID );
		foreach ( $acf_meta as $key => $val ) {
			if ( preg_match( "/^field_/", $key ) ) {
				$item = unserialize($val[0]);
				$acf_fields[$item["key"]] = "[".$group->post_title."] ".$item["name"];
			}
		}
	}

	return $acf_fields;
}




include("inc/post-handler.php");
include("inc/display-form.php");
include("inc/generate-input-field.php");