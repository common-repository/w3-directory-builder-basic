<?php
function w3dbff_generate_select_options($options, $value){
	$html = "";
	
	foreach($options as $option => $title){
		if(is_array($value)){
			$selected = (in_array($option, $value)) ? " selected='selected' " : null;
		} else {
			$selected = ($value == $option) ? " selected='selected' "  : null;
		}
		$html .= "<option ".$selected." value='".$option."'>".$title."</option>";
	}
	
	return $html;
}
	
function w3dbff_generate_form_input_field($args, $value){


	
	$name = isset($args["name"]) ? $args["name"]: null;
	$label = isset($args["label"]) ? $args["label"]: null;
	$description = isset($args["description"]) ? $args["description"]: null;	
	$size = isset($args["size"]) ? $args["size"]: null;
	$maxlength = isset($args["maxlength"]) ? $args["maxlength"]: null;
	$options = isset($args["options"]) ? $args["options"]: null;
	$optgroup = isset($args["optgroup"]) ? $args["optgroup"]: null;	
	$type = isset($args["type"]) ? $args["type"]: null;
	$pre_html = isset($args["pre_html"]) ? $args["pre_html"]: null;
	$post_html = isset($args["post_html"]) ? $args["post_html"]: null;

	$formControl = empty($size) ? "form-control" : null; 

	
	$labelHTML = '<label class="control-label">'.$label.'</label> ';



	
	$input = "";
	
	# SELECT
	if($type == "select" || $type == "multiselect"){
		$multiselect = ($type == "multiselect") ? " multiple " : null;
		$brackets = ($type == "multiselect") ? "[]" : null;//brackets are needed for storing an array of values.
		
		$inputHTML = '<select name="'.$name.$brackets.'" class="form-control input-'.$name.'" '.$multiselect.'>';	
		
		if($optgroup !== null){
			foreach($optgroup as $group => $options){
				$inputHTML .= "<optgroup label='".$group."'>";
				$inputHTML .= w3dbff_generate_select_options($options, $value);
				$inputHTML .= "</optgroup>";
			}
		} else {
			$inputHTML .= w3dbff_generate_select_options($options, $value);
		}
		$inputHTML .= "</select>";		
	} else if($type == "text") {
		$inputHTML = "<input type='text' value='".$value."' name='".$name."'  maxlength='".$maxlength."' size='".$size."' class='".$formControl." input-".$name."'>";
	} else if($type == "textarea") {
		$inputHTML = "<textarea name='".$name."' class='form-control input-".$name."'>".$value."</textarea>";
	} else if($type == "hidden") {
		$inputHTML = "<input type='hidden' name='".$name."' class='input-".$name."' value='".$value."'>";
	}	
	
	if($type !== "hidden") {
		$html = "<div class='row w3dbff-input-wrapper'>
					<div class='col-sm-3'>".$labelHTML."</div>
					<div class='col-sm-4'>".$inputHTML."</div>
					<div class='col-sm-5 w3dbff-input-description'>".$description."</div>						
				</div>";
	} else {
		$html = $inputHTML;
	}
	
	return $pre_html.$html.$post_html;




}