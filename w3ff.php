<?php
// https://codex.wordpress.org/Adding_Administration_Menus

add_action( 'admin_menu', 'w3dbff_add_admin_menu' );

/**
 * The function to hook the enqueue function
 * And Add the Menu page
 * We can seperate the enqueue but this is the best practice
 * Use a little of your head and find out why?
 */
function w3dbff_add_admin_menu() {
    /** Add options page and get the reference through a variable */
	$w3dbff = add_options_page( 'W3 Directory Builder', 'W3 Directory Builder', 'manage_options', 'w3dbff', 'w3dbff_options_page' );
 
    /**
     * Now use the reference to conditionally attach the script
     * We will add an action to admin_print_style with conditional tag
     * Also we will execute the w3dbff_admin_css_custom_page function which enqueues the CSS
     */
    add_action('admin_print_styles-' . $w3dbff, 'w3dbff_admin_css_custom_page');
}

function w3dbff_settings(){
	global $wpdb;
	
	# FORM NAME	
	
	$form_name = "w3dbff-settings";
	

	# SETUP
	
	$setup["type"] = "settings";
	w3dbff_register_form($setup, "setup", $form_name);
	


	# TABS
	
	$tabs["general_tab"] = array(
		"title" => "General",						
	);


	w3dbff_register_form($tabs, "tabs", $form_name);	



	# FIELDSETS
	
	$fieldsets["general_fieldset"] = array(
		"title" => "Manage Settings",
		"repeater" => false,
	);
	
	
	
	w3dbff_register_form($fieldsets, "fieldsets", $form_name);	

	
	# FIELDS

	# Order By -- Options
	$orderByOptions = array(
		"date_published" => "Date Published",
		"date_modified" => "Date Modified",
		"title" => "Title"
	);
	
	// Check if W3 Votify exists, if it doesn't modify $orderByOptions accordingly
	if(function_exists("w3vx_update_vote")){
		// I prefer "tending" to be listed at the top (even though the array order doesn't affect the order it appears on the front-end/sortbar)
		$orderByOptions = array("meta_value_num" => "Trending") + $orderByOptions;
	}
	
	# Post Types
	
	foreach(get_post_types() as $cpt){
		$postTypes[$cpt] = $cpt; 
	}
	
	# PAGES
	
	$get_pages = get_posts(array("post_type" => "page", "posts_per_page" => -1));
		
	foreach($get_pages as $p){
		$pages[$p->ID] = $p->post_title;
	}
	
	$fields = array(
		"general_tab" => array(
			"general_fieldset" => array(		
				array(
					"name" => "sortbar_post_types",
					"label" => "Automatically Insert Sortbar",
					"type" => "multiselect",
					"options" => $postTypes,
					"description" => "Prepend W3 Directory Builder sort bar (results found, order, orderby) to the archive pages of defined post types.",
				),
				array(
					"name" => "form_page",
					"label" => "Form Page",
					"type" => "select",
					"options" => $pages,
					"description" => "Select the page that will be used for posting and editing links. (A default page should have been created for you)."
				),				
				array(
					"name" => "append_submit_link",
					"label" => "Append Submit Link",
					"type" => "multiselect",
					"options" => get_registered_nav_menus(),
					"description" => "Select menu(s) where you would like the Submit Link to automatically appear."
				),
				array(
					"name" => "replace_permalink",
					"label" => "Replace Permalink",
					"type" => "select",
					"options" => array(1 => "Enable", 0 => "Disable"),
					"default" => 1,
					"description" => "Automatically replace post permalink with external link (for Enabled Post Types) ."
				),					
				array(
					"name" => "order_options",
					"label" => "Order Options",
					"type" => "multiselect",
					"options" => $orderByOptions,
					"description" => "Define the 'order' options that should be listed in the sort bar.",
				),				
				array(
					"name" => "default_order",
					"label" => "Set Default Order",
					"type" => "select",
					"options" => $orderByOptions,
					"description" => "Select the default results order for archives where the sortbar is automatically inserted.",
				),					
			),
		), // END GENERAL
	);

    w3dbff_register_form($fields, "fields", $form_name);
}

// remember admin_init is for the wp-admin and init is for the front-end
add_action("admin_init", "w3dbff_settings");
add_action("init", "w3dbff_settings");

function w3dbff_options_page() {

	global $w3dbff_FORMS;

	?>

	<?php
	
	echo w3dbff_display_form($w3dbff_FORMS["w3dbff-settings"]["fields"], "w3dbff-settings");
}

/**
 * The enqueue function
 * Registers and enqueue the CSS
 */
function w3dbff_admin_css_custom_page() {

	// https://www.intechgrity.com/how-to-add-your-own-stylesheet-to-your-wordpress-plugin-settings-page-or-all-admin-page/#

	$pluginDirectory = w3dbff_get_plugin_directory();

	# REGISTER STYLES
    wp_register_style( 'sumoselect',  $pluginDirectory['url'].'settings/assets/css/sumoselect.css', array(), 1 );	
    wp_register_style( 'bootstrap',  $pluginDirectory['url'].'settings/assets/css/bootstrap/bootstrap.css', array(), 1 );
    wp_register_style( 'w3dbff-admin',  $pluginDirectory['url'].'assets/css/admin.css', array(), 1 );
    wp_register_style( 'w3dbff-app',  $pluginDirectory['url'].'settings/assets/css/app.css', array(), 1 );


	# REGISTER SCRIPTS
	wp_register_script( "sumoselect", $pluginDirectory["url"].'settings/assets/js/jquery.sumoselect.js', array("jquery"), null, true );
	wp_register_script( "sf-repeater", $pluginDirectory["url"].'settings/assets/js/repeater.js', array("jquery"), null, true );
	wp_register_script( "bootstrap", $pluginDirectory["url"].'settings/assets/js/bootstrap/bootstrap.js', array("jquery"), null, true );
	wp_register_script( "bootbox", $pluginDirectory["url"].'settings/assets/js/bootstrap/bootbox.js', array("bootstrap"), null, true );
 	wp_register_script( "w3dbff-admin", $pluginDirectory["url"].'assets/js/admin.js', array("jquery"), null, true ); // this is code that's meant to augment the W3 Search Fields settings page.
  	wp_register_script( "w3dbff-app", $pluginDirectory["url"].'settings/assets/js/app.js', array("jquery"), null, true );

 
    # ENQUEUE
    // styles
	wp_enqueue_style('sumoselect');   
    wp_enqueue_style('bootstrap');
    wp_enqueue_style('w3dbff-admin');
    wp_enqueue_style('w3dbff-app');
    
    // scripts
    // REMEMBER: the order scripts load matters
	wp_enqueue_script('jquery');
	wp_enqueue_script('sumoselect');
	wp_enqueue_script('sf-repeater');
	wp_enqueue_script('bootstrap');
	wp_enqueue_script('bootbox');	
	wp_enqueue_script('w3dbff-admin');
	wp_enqueue_script('w3dbff-app');

   $script_params = array(
	   /* examples */
	   'ajaxurl' => admin_url('admin-ajax.php'),
   );

   wp_localize_script( 'w3dbff-admin', 'MyAjax', $script_params );
	
}


?>