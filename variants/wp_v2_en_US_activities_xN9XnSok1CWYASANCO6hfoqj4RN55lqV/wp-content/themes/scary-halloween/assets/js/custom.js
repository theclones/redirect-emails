// menus 
function scary_halloween_menu_open_nav() {
	window.scary_halloween_responsiveMenu=true;
	jQuery(".sidenav").addClass('show');
}
function scary_halloween_menu_close_nav() {
	window.scary_halloween_responsiveMenu=false;
 	jQuery(".sidenav").removeClass('show');
}
jQuery(function($){
 	"use strict";
 	jQuery('.main-menu > ul').superfish({
		delay: 500,
		animation: {opacity:'show',height:'show'},
		speed: 'fast'
 	});
});

jQuery(document).ready(function () {
	window.scary_halloween_currentfocus=null;
  	scary_halloween_checkfocusdElement();
	var scary_halloween_body = document.querySelector('body');
	scary_halloween_body.addEventListener('keyup', scary_halloween_check_tab_press);
	var scary_halloween_gotoHome = false;
	var scary_halloween_gotoClose = false;
	window.scary_halloween_responsiveMenu=false;
 	function scary_halloween_checkfocusdElement(){
	 	if(window.scary_halloween_currentfocus=document.activeElement.className){
		 	window.scary_halloween_currentfocus=document.activeElement.className;
	 	}
 	}
 	function scary_halloween_check_tab_press(e) {
		"use strict";
		// pick passed event or global event object if passed one is empty
		e = e || event;
		var activeElement;

		if(window.innerWidth < 999){
		if (e.keyCode == 9) {
			if(window.scary_halloween_responsiveMenu){
			if (!e.shiftKey) {
				if(scary_halloween_gotoHome) {
					jQuery( ".main-menu ul:first li:first a:first-child" ).focus();
				}
			}
			if (jQuery("a.closebtn.mobile-menu").is(":focus")) {
				scary_halloween_gotoHome = true;
			} else {
				scary_halloween_gotoHome = false;
			}

		}else{

			if(window.scary_halloween_currentfocus=="responsivetoggle"){
				jQuery( "" ).focus();
			}}}
		}
		if (e.shiftKey && e.keyCode == 9) {
		if(window.innerWidth < 999){
			if(window.scary_halloween_currentfocus=="header-search"){
				jQuery(".responsivetoggle").focus();
			}else{
				if(window.scary_halloween_responsiveMenu){
				if(scary_halloween_gotoClose){
					jQuery("a.closebtn.mobile-menu").focus();
				}
				if (jQuery( ".main-menu ul:first li:first a:first-child" ).is(":focus")) {
					scary_halloween_gotoClose = true;
				} else {
					scary_halloween_gotoClose = false;
				}

			}else{

			if(window.scary_halloween_responsiveMenu){
			}}}}
		}
	 	scary_halloween_checkfocusdElement();
	}
});

jQuery('document').ready(function($){
	// preloader
  setTimeout(function () {
		jQuery("#preloader").fadeOut("slow");
  },1000);

  // Sticky Header
  $(window).scroll(function(){
		var sticky = $('.header-sticky'),
			scroll = $(window).scrollTop();

		if (scroll >= 100) sticky.addClass('header-fixed');
		else sticky.removeClass('header-fixed');
	});
});

// Scroller
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

// search
jQuery(document).ready(function () {
	function scary_halloween_search_loop_focus(element) {
	var scary_halloween_focus = element.find('select, input, textarea, button, a[href]');
	var scary_halloween_firstFocus = scary_halloween_focus[0];  
	var scary_halloween_lastFocus = scary_halloween_focus[scary_halloween_focus.length - 1];
	var KEYCODE_TAB = 9;

	element.on('keydown', function scary_halloween_search_loop_focus(e) {
	  var isTabPressed = (e.key === 'Tab' || e.keyCode === KEYCODE_TAB);

	  if (!isTabPressed) { 
		return; 
	  }

	  if ( e.shiftKey ) /* shift + tab */ {
		if (document.activeElement === scary_halloween_firstFocus) {
		  scary_halloween_lastFocus.focus();
			e.preventDefault();
		  }
		} else /* tab */ {
		if (document.activeElement === scary_halloween_lastFocus) {
		  scary_halloween_firstFocus.focus();
			e.preventDefault();
		  }
		}
	});
  }
  jQuery('.search-box span a').click(function(){
	  jQuery(".serach_outer").slideDown(1000);
	  scary_halloween_search_loop_focus(jQuery('.serach_outer'));
  });

  jQuery('.closepop a').click(function(){
	  jQuery(".serach_outer").slideUp(1000);
  });
});

// Title Color
jQuery( document ).ready(function() {
  jQuery("#slider .carousel-caption h1").each(function() {
    var t = jQuery(this).text();
    var splitT = t.split(" ");
    console.log(splitT);
    var halfIndex = 2;
    var newText = "";
    for(var i = 0; i < splitT.length; i++) {
      if(i == halfIndex) {
        newText += "<span class='title-text'>";
        newText += splitT[i] + " ";
        newText += "</span>";
      }else{
        newText += splitT[i] + " ";
      }     
    }   
    jQuery(this).html(newText);
  });
});
