jQuery( document ).ready(function($) {

	// get vote buttons everytime w3sf loads posts (initial load, pagination, search, etc.).
	$( document ).on( "w3sf-posts-loaded", w3db_vote_buttons_helper);
	
	function w3db_vote_buttons_helper(){
		$ids = [];
		console.log('.vote-buttons-wrapper.post-vote');
		$(document).find('.vote-buttons-wrapper').each(function(){
			$ids.push($(this).data("id"));
		});
		console.log($ids);
		
		if(typeof generate_w3vx_vote_buttons == "function"){
			generate_w3vx_vote_buttons($ids, "post");
		}						
	}
		
	// Sort Bar
	// On change, submit form
	$('.w3db-sortbar-select').on('change', function(e){
		console.log("log");
		this.form.submit();
	});	
});