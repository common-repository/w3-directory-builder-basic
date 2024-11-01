<?php

function w3dbff_display_form($form, $form_name){
	global $w3dbff_FORMS;
	
	$dbValues = get_option("w3dbff_settings");// mega value contaning w3dbff wp-admin settings
	$dbValues = json_decode($dbValues, true);
	
	$html = '<div class="tab-content">';
	
	$nav = "<div role='tabpanel' id='".$form_name."-tabs'><ul class='nav nav-tabs' role='tablist'>";
	
	$active = " show active"; 
	foreach($form as $tab => $fieldsets){
		$nav .= "<li class='nav-item'><a class='nav-link ".$active."' id='home-tab' data-toggle='tab' href='#".$tab."' role='tab' aria-controls='".$tab."' aria-expanded='true'>".$w3dbff_FORMS[$form_name]["tabs"][$tab]["title"]."</a></li>"; 
		$html .= "<div role='tabpanel' class='tab-pane ".$active."' id='".$tab."' aria-labelledby='".$tab."'>";
		$html .= "<form action='".esc_url( admin_url('admin-post.php') )."' method='POST' class='w3dbff w3dbff-wp-settings-page form-horizontal' id='".$form_name."-form' role='form'>";

		foreach($fieldsets as $fkey => $fset){
			$fieldset_repeater = isset($w3dbff_FORMS[$form_name]["fieldsets"][$fkey]["repeater"]) ? $w3dbff_FORMS[$form_name]["fieldsets"][$fkey]["repeater"] : false;
			$fieldset_title = isset($w3dbff_FORMS[$form_name]["fieldsets"][$fkey]["title"]) ? $w3dbff_FORMS[$form_name]["fieldsets"][$fkey]["title"] : null;
			$fieldset = "";
			
			if(!empty($fieldset_title)){
				$html .= "<h3 class='page-title'>".$w3dbff_FORMS[$form_name]["fieldsets"][$fkey]["title"]."</h3>";
			}
			
			if($fieldset_repeater == true){
				if(isset($dbValues[$form_name][$tab][$fkey])){ // remember dbValues doesn't differentiate between fieldset keys and input keys		
					$repeated = $dbValues[$form_name][$tab][$fkey];

					foreach($repeated as $rValues){ // for each repeated fieldset
						$fieldset .= w3dbff_generate_fieldset($form_name, $tab, $fset, $rValues, true);
					}				
				} else {
					$fieldset = w3dbff_generate_fieldset($form_name, $tab, $fset, null, true);				
				}
			} else {
				$fieldset = "<fieldset>";
			
				foreach($fset as $field){
					// I couldn't get this to work inside the fieldset function...which sould really be a repeater function for simplicity.
					$key = $field["name"];
					$val = isset($dbValues[$form_name][$tab][$key]) ? $dbValues[$form_name][$tab][$key] : null;
					
						
					if(!isset($val) || empty($val)){
						$val =  isset($field["default"]) ? $field["default"] : null;
					}
					
					$fieldset .= w3dbff_generate_form_input_field($field, $val);		
				}
				
				$fieldset .= "</fieldset>";				
			}

			
			if($fieldset_repeater == true){
				$html .= "<div class='repeater-custom-show-hide'>
							<div data-repeater-list='".$fkey."' data-accordion-group>";

				$html .= $fieldset;


				$html .= "</div><!-- END REPEATER LIST -->
				
				<div class='form-group'>
					<div class='col-sm-12 text-right'>
						<span data-repeater-create='' class='btn btn-info btn-sm'>
							Add Row
						</span>
					</div>
				</div>
			</div>";
			} else {
				$html .= $fieldset;
			}
		}
		
		$html .= wp_nonce_field( "w3dbff_form_".$form_name, "_wpnonce", true, false );
		$html .= '<input type="hidden" name="action" value="w3dbff_post_handler">';		
		$html .= '<input type="hidden" name="w3dbff_form_name" value="'.$form_name.'">';
		$html .= '<input type="hidden" name="w3dbff_tab" value="'.$tab.'">';
		
		$html .= '<div class="row" id="submit-div"><div class="col-sm-12 text-right"><input id="w3dbff-form-submit" type="submit" value="Save"></div></div>'; // The submit/save button only applies to the relevant tab
		$html .= "</form>";
		$html .= "</div><!-- // END tab-pane -->";
		
		$active = null; // we only need value applied to the first tab.
	}
	
	$nav .= "</ul><!-- END nav-tabs --> </div><!-- END tab-content --> </div><!-- END tabpanel -->";
	$html .= "</div><!-- // END tab-content -->";
	
	$result = $nav.$html;
	return $result;
}



// this function is required so the repeatable fieldset can be generated conditionally, alongside fieldsets that are not repeaters.

function w3dbff_generate_fieldset($form_name, $tab_name, $fset, $values, $repeater) {
	if($repeater == true){
		$html = "<fieldset data-content><div class='repeater-left'>";
			
		foreach($fset as $input) {
			$key = $input["name"];
			
			if(isset($values[$key])){
				$value = $values[$key];
			} else {
				$value =  isset($input["default"]) ? $input["default"] : null;
			}
			
			$html .= w3dbff_generate_form_input_field($input, $value);
		}

		$html .= "</div><div class='repeater-right'><span data-repeater-delete class='repeater-delete-button'>X</span></div></fieldset>";
		
		$html = "<div data-repeater-item='' class='repeater-item accordion' data-accordion>
					<div class='repeater-fieldset-heading' data-control> <span class='fieldset-label'>".$values["label"]."</span> <span class='openclose down-arrow'>&#9650;</span></div>
						".$html."
				</div><!-- END REPEATER ITEM -->";		
	}
			
	return $html;
}