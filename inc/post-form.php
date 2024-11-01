<?php
$pid = isset($_GET["post"]) ? intval($_GET["post"]) : null;

// get_the_title(), etc. will return values for the page that contains this form, if value is null.
// So we need to check if $pid isset.
$url = get_post_meta($pid, "w3db_url", true);
$url = isset($pid) && isset($url) ? $url : null;


$title = get_the_title($pid);
$title =  isset($pid) && isset($title) ? $title : null;

//https://wordpress.stackexchange.com/a/150005
//$description = apply_filters('the_excerpt', get_post_field('post_excerpt', $pid));
$description = get_post_field('post_content', $pid);
$description =  isset($pid) && isset($description) ? $description : null;

?>

<form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST" id="linkCreateEditForm" class="w3db-modal-body">
		<div class="w3db-row">
			<div class="w3db-col-md-9">
				<div class="w3db-form-group">
					<label for="w3dbInputURL">URL*</label>
					<input type="url" name="url" id="w3dbInputURL" class="w3db-form-control" value="<?php echo $url; ?>">
				</div>
					
				<div class="w3db-form-group">
					<label for="w3dbInputTitle">Title*</label>
					<input type="text" name="title" id="w3dbInputTitle" id="w3dbInputKind" class="w3db-form-control" value="<?php echo $title; ?>">
				</div>
		
				<div class="w3db-form-group">
					<label for="w3dbInputDescription">Description*</label>
					<textarea name="content" id="w3dbInputDescription" class="w3db-form-control"><?php echo $description; ?></textarea>
				</div>
				
				<div class="w3db-form-group">
					<label for="w3dbInputTags">Tags</label>
					<input type="text" name="post_tag" id="w3dbInputTags" class="w3db-form-control" value="<?php echo  w3db_post_term_links("post_tag", $pid, ", ", null, "tag"); ?>">
				</div>
			</div>

			<div class="w3db-col-md-3">				
				<div class="w3db-form-group">
					<label for="w3dbInputCategory">Category*</label>
					
					<select name="category" id="w3dbInputCategory" class="w3db-form-control">
						<?php
						
							$current_cat = wp_get_post_terms($pid, "category", array("fields" => "ids"));
							$current_cat = isset($current_cat) && !empty($current_cat) ? $current_cat[0] : null;

							$category_terms = get_terms( array(
								'taxonomy' => 'category',
								'hide_empty' => false,
							) );					
				
							if ( ! empty( $category_terms ) ) {
								if ( ! is_wp_error( $category_terms ) ) {
									foreach( $category_terms as $category ) {
										if($category->term_id == $current_cat){
											$cat_selected = ' selected="selected"';
										} else {
											$cat_selected = null;
										}
										echo '<option value="'.$category->term_id.'" '.$cat_selected.' >'. $category->name . ' </option> '; 
									}
								}
							}
		
						?>
					</select>
				</div>
						
				<div class="w3db-form-group text-right" style="margin-top:60px;">
					<button type="submit" class="w3db-btn w3db-btn-primary" id="submitLinkForm">Save</button>
				</div>								
			</div>		
		</div>				  
	
		<input type="hidden" name="action" value="w3db_link_form_handler">
		<input type="hidden" name="pid" value="<?php echo $pid; ?>">
</form>