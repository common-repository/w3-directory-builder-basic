jQuery( document ).ready(function($) {

//https://stackoverflow.com/a/1909508/7433563
	var delay = (function(){
	  var timer = 0;
	  return function(callback, ms){
		clearTimeout (timer);
		timer = setTimeout(callback, ms);
	  };
	})();


  $('.repeater-default').repeater();

	$(document).on('keyup', '.input-label', function (e) {
		if($(this).val().trim() !== ""){
			var $fieldLabelValue = $(this).val().trim();

			var nameField = $(this).closest("fieldset").find(".input-name").first();
			
			if(nameField.val() == ""){// if name field is  empty
				// https://stackoverflow.com/a/13297540/7433563
				// https://stackoverflow.com/questions/1909441/how-to-delay-the-keyup-handler-until-the-user-stops-typing
				var alphanumeric = $fieldLabelValue.replace(/\s+/g, '-').replace(/[^a-zA-Z-]/g, '').toLowerCase();
				
				delay(function(){
					nameField.val(alphanumeric);
				}, 1000 );				
			}
		} else {
			var $fieldLabelValue = "New Filter";
		}		
	
		/* This may be more indirect than necessay. (Is there a way to find aunt/uncle elements? I don't know.) */
		var $cF = $(this).closest("fieldset"); //find parent fieldset
		var $pH = $cF.prevAll(".repeater-fieldset-heading"); // find fieldset sibling header
		$pH.children(".fieldset-label").first().text($fieldLabelValue); // find label within header and update value

	});
	
$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
	tab = e.target // newly activated tab
	$(this).children("fieldset").first().show(); 
})


$("body").find(".repeater-custom-show-hide").each(function(index) {
  repeaterGroup = $(this);
	
  $(this).repeater({
    show: function () {
      $(this).slideDown();
	 /* THIS IS A BUG */ repeaterGroup.children("fieldset").hide(); // only hide fieldsets that belong to current repeater group ("repeater-custom-show-hide")
      $(this).children("fieldset").first().show();
      $(this).children(".repeater-fieldset-heading").children(".fieldset-label").first().text("New Filter"); // This should have found the fieldset-label child directly (without find the ".repeater-fieldset-heading"), but for some reason that apporach didn't work (or was unreliable).
    },
    hide: function (remove) {
      if(confirm('Are you sure you want to remove this item?')) {
        $(this).slideUp(remove);
      }
    },
	initEmpty: true,	
  });
});  
  	//find first fieldset and open/reveal it
	$(".accordion").first().addClass("open");
	$(".accordion fieldset").first().show();	
	$(".openclose").first().addClass("up-arrow");
	$(".openclose").first().removeClass("down-arrow");	


	
	$( 'form' ).on( 'click', '.repeater-fieldset-heading', function () { // where form is a static element in which dynamic elements are inserted.
		var repeaterItem = $(this).closest('.accordion'); // find parent wrapper for repeater-item
		if(repeaterItem.hasClass("open")){ // close -- better to use hasClass rather than "attr("class") == open" since the latter approach would require further code to handle multiple class values.
			repeaterItem.children(".openclose").first().removeClass("up-arrow"); // even though 1 result is expected the selector would return an array. first(). seems like the more straight-forward approach.
			repeaterItem.children(".openclose").first().addClass("down-arrow");

			repeaterItem.children("fieldset").first().hide();
			repeaterItem.removeClass("open");
			
		} else { // open
		
			// find all fielsets and remove the open class if present
			$(".accordion fieldset").hide();		
			$(".accordion").removeClass("open");
			
			$(".openclose").removeClass("up-arrow");
			$(".openclose").addClass("down-arrow");

			repeaterItem.children("fieldset").first().show();
			repeaterItem.children(".openclose").removeClass("down-arrow");			
			repeaterItem.children(".openclose").addClass("up-arrow");			
			repeaterItem.addClass("open");
			
		}
	});
	
	$(".w3dbff-wp-settings-page select").SumoSelect({ csvDispCount: 1, search: true, placeholder: 'Select Option(s)' });
	
});