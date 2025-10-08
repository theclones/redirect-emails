function catering_services_menu_open_nav() {
	window.catering_services_responsiveMenu=true;
	jQuery(".sidenav").addClass('show');
}
function catering_services_menu_close_nav() {
	window.catering_services_responsiveMenu=false;
 	jQuery(".sidenav").removeClass('show');
}

jQuery(function($){
 	"use strict";
 	jQuery('.main-menu > ul').superfish({
		delay: 500,
		animation: {opacity:'show',height:'show'},  
		speed: 'fast'
 	});
 	$(window).scroll(function(){
		var sticky = $('.header-sticky'),
			scroll = $(window).scrollTop();

		if (scroll >= 100) sticky.addClass('header-fixed');
		else sticky.removeClass('header-fixed');
	});
});

jQuery(document).ready(function () {
	window.catering_services_currentfocus=null;
  	catering_services_checkfocusdElement();
	var catering_services_body = document.querySelector('body');
	catering_services_body.addEventListener('keyup', catering_services_check_tab_press);
	var catering_services_gotoHome = false;
	var catering_services_gotoClose = false;
	window.catering_services_responsiveMenu=false;
 	function catering_services_checkfocusdElement(){
	 	if(window.catering_services_currentfocus=document.activeElement.className){
		 	window.catering_services_currentfocus=document.activeElement.className;
	 	}
 	}
 	function catering_services_check_tab_press(e) {
		"use strict";
		// pick passed event or global event object if passed one is empty
		e = e || event;
		var activeElement;

		if(window.innerWidth < 999){
		if (e.keyCode == 9) {
			if(window.catering_services_responsiveMenu){
			if (!e.shiftKey) {
				if(catering_services_gotoHome) {
					jQuery( ".main-menu ul:first li:first a:first-child" ).focus();
				}
			}
			if (jQuery("a.closebtn.mobile-menu").is(":focus")) {
				catering_services_gotoHome = true;
			} else {
				catering_services_gotoHome = false;
			}

		}else{

			if(window.catering_services_currentfocus=="responsivetoggle"){
				jQuery( "" ).focus();
			}}}
		}
		if (e.shiftKey && e.keyCode == 9) {
		if(window.innerWidth < 999){
			if(window.catering_services_currentfocus=="header-search"){
				jQuery(".responsivetoggle").focus();
			}else{
				if(window.catering_services_responsiveMenu){
				if(catering_services_gotoClose){
					jQuery("a.closebtn.mobile-menu").focus();
				}
				if (jQuery( ".main-menu ul:first li:first a:first-child" ).is(":focus")) {
					catering_services_gotoClose = true;
				} else {
					catering_services_gotoClose = false;
				}
			
			}else{

			if(window.catering_services_responsiveMenu){
			}}}}
		}
	 	catering_services_checkfocusdElement();
	}
});

jQuery('document').ready(function($){
  setTimeout(function () {
		jQuery("#preloader").fadeOut("slow");
  },1000);
});

jQuery(document).ready(function () {
	jQuery(window).scroll(function () {
    if (jQuery(this).scrollTop() > 100) {
      jQuery('.scrollup i').fadeIn();
    } else {
      jQuery('.scrollup i').fadeOut();
    }
	});
	jQuery('.scrollup i').click(function () {
    jQuery("html, body").animate({
      scrollTop: 0
    }, 600);
    return false;
	});
});

jQuery(document).ready(function () {
	function catering_services_search_loop_focus(element) {
		var catering_services_focus = element.find('select, input, textarea, button, a[href]');
		var catering_services_firstFocus = catering_services_focus[0];  
		var catering_services_lastFocus = catering_services_focus[catering_services_focus.length - 1];
		var KEYCODE_TAB = 9;

		element.on('keydown', function catering_services_search_loop_focus(e) {
			var isTabPressed = (e.key === 'Tab' || e.keyCode === KEYCODE_TAB);

			if (!isTabPressed) { 
			  return; 
			}

			if ( e.shiftKey ) /* shift + tab */ {
			  if (document.activeElement === catering_services_firstFocus) {
		    catering_services_lastFocus.focus();
		      e.preventDefault();
		    }
		  } else /* tab */ {
		  	if (document.activeElement === catering_services_lastFocus) {
		    	catering_services_firstFocus.focus();
		      e.preventDefault();
		    }
		  }
		});
	}
	
	jQuery('.search-box a').click(function(){
    jQuery(".serach_outer").slideDown(1000);
  	catering_services_search_loop_focus(jQuery('.serach_outer'));
  });

  jQuery('.closepop a').click(function(){
    jQuery(".serach_outer").slideUp(1000);
  });
});