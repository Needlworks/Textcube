  jQuery( document ).ready( function( $ ) {
    // Show Body
    $( '#LN-content' ).fadeIn( 1000 );
  } );
  
  
  
  jQuery(document).ready(function(){
  	
  	var show_menu = false;
  	
  	//tab click
  	
  	jQuery('#back_top').click(function(){
  		jQuery('html, body').animate({scrollTop:0}, 'normal');
  		return false;
  	});
  	
  	jQuery(window).scroll(function() {
  		if(jQuery(this).scrollTop() !== 0) {
  			jQuery('#back_top').fadeIn();	
  		} else {
  			jQuery('#back_top').fadeOut();
  		}
  	});
  	
  	if(jQuery(window).scrollTop() !== 0) {
  		jQuery('#back_top').show();	
  	} else {
  		jQuery('#back_top').hide();
  	}
  	
  
  
  });