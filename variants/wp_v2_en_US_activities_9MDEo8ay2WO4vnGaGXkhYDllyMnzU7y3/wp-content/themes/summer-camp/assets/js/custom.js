function summer_camp_menu_open_nav() {
	window.summer_camp_responsiveMenu=true;
	jQuery(".sidenav").addClass('show');
}
function summer_camp_menu_close_nav() {
	window.summer_camp_responsiveMenu=false;
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
	window.summer_camp_currentfocus=null;
  	summer_camp_checkfocusdElement();
	var summer_camp_body = document.querySelector('body');
	summer_camp_body.addEventListener('keyup', summer_camp_check_tab_press);
	var summer_camp_gotoHome = false;
	var summer_camp_gotoClose = false;
	window.summer_camp_responsiveMenu=false;
 	function summer_camp_checkfocusdElement(){
	 	if(window.summer_camp_currentfocus=document.activeElement.className){
		 	window.summer_camp_currentfocus=document.activeElement.className;
	 	}
 	}
 	function summer_camp_check_tab_press(e) {
		"use strict";
		// pick passed event or global event object if passed one is empty
		e = e || event;
		var activeElement;

		if(window.innerWidth < 999){
		if (e.keyCode == 9) {
			if(window.summer_camp_responsiveMenu){
			if (!e.shiftKey) {
				if(summer_camp_gotoHome) {
					jQuery( ".main-menu ul:first li:first a:first-child" ).focus();
				}
			}
			if (jQuery("a.closebtn.mobile-menu").is(":focus")) {
				summer_camp_gotoHome = true;
			} else {
				summer_camp_gotoHome = false;
			}

		}else{

			if(window.summer_camp_currentfocus=="responsivetoggle"){
				jQuery( "" ).focus();
			}}}
		}
		if (e.shiftKey && e.keyCode == 9) {
		if(window.innerWidth < 999){
			if(window.summer_camp_currentfocus=="header-search"){
				jQuery(".responsivetoggle").focus();
			}else{
				if(window.summer_camp_responsiveMenu){
				if(summer_camp_gotoClose){
					jQuery("a.closebtn.mobile-menu").focus();
				}
				if (jQuery( ".main-menu ul:first li:first a:first-child" ).is(":focus")) {
					summer_camp_gotoClose = true;
				} else {
					summer_camp_gotoClose = false;
				}

			}else{

			if(window.summer_camp_responsiveMenu){
			}}}}
		}
	 	summer_camp_checkfocusdElement();
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

// search
jQuery(document).ready(function () {
	  function summer_camp_search_loop_focus(element) {
	  var summer_camp_focus = element.find('select, input, textarea, button, a[href]');
	  var summer_camp_firstFocus = summer_camp_focus[0];  
	  var summer_camp_lastFocus = summer_camp_focus[summer_camp_focus.length - 1];
	  var KEYCODE_TAB = 9;

	  element.on('keydown', function summer_camp_search_loop_focus(e) {
	    var isTabPressed = (e.key === 'Tab' || e.keyCode === KEYCODE_TAB);

	    if (!isTabPressed) { 
	      return; 
	    }

	    if ( e.shiftKey ) /* shift + tab */ {
	      if (document.activeElement === summer_camp_firstFocus) {
	        summer_camp_lastFocus.focus();
	          e.preventDefault();
	        }
	      } else /* tab */ {
	      if (document.activeElement === summer_camp_lastFocus) {
	        summer_camp_firstFocus.focus();
	          e.preventDefault();
	        }
	      }
	  });
	}
	jQuery('.search-box a').click(function(){
    jQuery(".serach_outer").slideDown(1000);
  	summer_camp_search_loop_focus(jQuery('.serach_outer'));
  });

  jQuery('.closepop a').click(function(){
    jQuery(".serach_outer").slideUp(1000);
  });
});

// owl

jQuery('document').ready(function(){
  var owl = jQuery('#slider .owl-carousel');
    owl.owlCarousel({
    nav: true,
    autoplay : true,
    lazyLoad: true,
    autoplayTimeout: 9000,
    loop: true,
    dots:true,
    navText : ['<i class="fas fa-angle-left"></i>','<i class="fas fa-angle-right"></i>'],
    responsive: {
      0: {
        items: 1
      },
      600: {
        items: 1
      },
      1000: {
        items: 1
      }
    },
    autoplayHoverPause : true,
    mouseDrag: true
  });
});

// classes section
jQuery('document').ready(function(){
  var owl = jQuery('#about-sec .owl-carousel');
    owl.owlCarousel({
    margin:20,
    autoplay : true,
    autoplayTimeout: 4000,
    loop: false,
    dots:false,
    responsive: {
      0: {
        items: 1
      },
      600: {
        items: 2
      },
      1000: {
        items: 3
      }
    },
    autoplayHoverPause : true,
    mouseDrag: true
  });
});