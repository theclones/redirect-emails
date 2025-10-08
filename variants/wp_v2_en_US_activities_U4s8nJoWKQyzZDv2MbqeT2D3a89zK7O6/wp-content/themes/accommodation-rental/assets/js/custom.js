function accommodation_rental_menu_open_nav() {
	window.accommodation_rental_responsiveMenu=true;
	jQuery(".sidenav").addClass('show');
}
function accommodation_rental_menu_close_nav() {
	window.accommodation_rental_responsiveMenu=false;
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
	window.accommodation_rental_currentfocus=null;
  	accommodation_rental_checkfocusdElement();
	var accommodation_rental_body = document.querySelector('body');
	accommodation_rental_body.addEventListener('keyup', accommodation_rental_check_tab_press);
	var accommodation_rental_gotoHome = false;
	var accommodation_rental_gotoClose = false;
	window.accommodation_rental_responsiveMenu=false;
 	function accommodation_rental_checkfocusdElement(){
	 	if(window.accommodation_rental_currentfocus=document.activeElement.className){
		 	window.accommodation_rental_currentfocus=document.activeElement.className;
	 	}
 	}
 	function accommodation_rental_check_tab_press(e) {
		"use strict";
		// pick passed event or global event object if passed one is empty
		e = e || event;
		var activeElement;

		if(window.innerWidth < 999){
		if (e.keyCode == 9) {
			if(window.accommodation_rental_responsiveMenu){
			if (!e.shiftKey) {
				if(accommodation_rental_gotoHome) {
					jQuery( ".main-menu ul:first li:first a:first-child" ).focus();
				}
			}
			if (jQuery("a.closebtn.mobile-menu").is(":focus")) {
				accommodation_rental_gotoHome = true;
			} else {
				accommodation_rental_gotoHome = false;
			}

		}else{

			if(window.accommodation_rental_currentfocus=="responsivetoggle"){
				jQuery( "" ).focus();
			}}}
		}
		if (e.shiftKey && e.keyCode == 9) {
		if(window.innerWidth < 999){
			if(window.accommodation_rental_currentfocus=="header-search"){
				jQuery(".responsivetoggle").focus();
			}else{
				if(window.accommodation_rental_responsiveMenu){
				if(accommodation_rental_gotoClose){
					jQuery("a.closebtn.mobile-menu").focus();
				}
				if (jQuery( ".main-menu ul:first li:first a:first-child" ).is(":focus")) {
					accommodation_rental_gotoClose = true;
				} else {
					accommodation_rental_gotoClose = false;
				}

			}else{

			if(window.accommodation_rental_responsiveMenu){
			}}}}
		}
	 	accommodation_rental_checkfocusdElement();
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
	  function accommodation_rental_search_loop_focus(element) {
	  var accommodation_rental_focus = element.find('select, input, textarea, button, a[href]');
	  var accommodation_rental_firstFocus = accommodation_rental_focus[0];
	  var accommodation_rental_lastFocus = accommodation_rental_focus[accommodation_rental_focus.length - 1];
	  var KEYCODE_TAB = 9;

	  element.on('keydown', function accommodation_rental_search_loop_focus(e) {
	    var isTabPressed = (e.key === 'Tab' || e.keyCode === KEYCODE_TAB);

	    if (!isTabPressed) {
	      return;
	    }

	    if ( e.shiftKey ) /* shift + tab */ {
	      if (document.activeElement === accommodation_rental_firstFocus) {
	        accommodation_rental_lastFocus.focus();
	          e.preventDefault();
	        }
	      } else /* tab */ {
	      if (document.activeElement === accommodation_rental_lastFocus) {
	        accommodation_rental_firstFocus.focus();
	          e.preventDefault();
	        }
	      }
	  });
	}
	jQuery('.search-box span a').click(function(){
    jQuery(".serach_outer").slideDown(1000);
  	accommodation_rental_search_loop_focus(jQuery('.serach_outer'));
  });
  jQuery('.closepop a').click(function(){
    jQuery(".serach_outer").slideUp(1000);
  });
});

jQuery('document').ready(function(){
  var owl = jQuery('#slider-sec .owl-carousel');
    owl.owlCarousel({
		stagePadding: 150,
    margin:20,
    nav: true,
    autoplay : true,
    lazyLoad: true,
    autoplayTimeout: 3000,
    loop: false,
    dots:true,
    navText : ['<i class="fas fa-chevron-left"></i>','<i class="fas fa-chevron-right"></i>'],
    responsive: {
      0: {
        items: 1,
        stagePadding: 0
      },
      600: {
        items: 1,
        stagePadding: 0
      },
      1000: {
        items: 1,
        stagePadding: 100
      }
    },
    autoplayHoverPause : true,
    mouseDrag: true
  });
});
