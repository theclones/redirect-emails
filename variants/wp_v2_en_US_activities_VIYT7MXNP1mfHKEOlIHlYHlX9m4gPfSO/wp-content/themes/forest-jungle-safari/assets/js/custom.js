jQuery(function($){
 	"use strict";
 	jQuery('.main-menu > ul').superfish({
		delay: 500,
		animation: {opacity:'show',height:'show'},
		speed: 'fast'
 	});
});

function forest_jungle_safari_menu_open_nav() {
	window.forest_jungle_safari_responsiveMenu=true;
	jQuery(".sidenav").addClass('show');
}
function forest_jungle_safari_menu_close_nav() {
	window.forest_jungle_safari_responsiveMenu=false;
 	jQuery(".sidenav").removeClass('show');
}

jQuery(document).ready(function () {
	window.forest_jungle_safari_currentfocus=null;
  forest_jungle_safari_checkfocusdElement();
	var forest_jungle_safari_body = document.querySelector('body');
	forest_jungle_safari_body.addEventListener('keyup', forest_jungle_safari_check_tab_press);
	var forest_jungle_safari_gotoHome = false;
	var forest_jungle_safari_gotoClose = false;
	window.forest_jungle_safari_responsiveMenu=false;
 	function forest_jungle_safari_checkfocusdElement(){
	 	if(window.forest_jungle_safari_currentfocus=document.activeElement.className){
		 	window.forest_jungle_safari_currentfocus=document.activeElement.className;
	 	}
 	}
 	function forest_jungle_safari_check_tab_press(e) {
		"use strict";
		// pick passed event or global event object if passed one is empty
		e = e || event;
		var activeElement;

		if(window.innerWidth < 999){
		if (e.keyCode == 9) {
			if(window.forest_jungle_safari_responsiveMenu){
			if (!e.shiftKey) {
				if(forest_jungle_safari_gotoHome) {
					jQuery( ".main-menu ul:first li:first a:first-child" ).focus();
				}
			}
			if (jQuery("a.closebtn.mobile-menu").is(":focus")) {
				forest_jungle_safari_gotoHome = true;
			} else {
				forest_jungle_safari_gotoHome = false;
			}

		}else{

			if(window.forest_jungle_safari_currentfocus=="responsivetoggle"){
				jQuery( "" ).focus();
			}}}
		}
		if (e.shiftKey && e.keyCode == 9) {
		if(window.innerWidth < 999){
			if(window.forest_jungle_safari_currentfocus=="header-search"){
				jQuery(".responsivetoggle").focus();
			}else{
				if(window.forest_jungle_safari_responsiveMenu){
				if(forest_jungle_safari_gotoClose){
					jQuery("a.closebtn.mobile-menu").focus();
				}
				if (jQuery( ".main-menu ul:first li:first a:first-child" ).is(":focus")) {
					forest_jungle_safari_gotoClose = true;
				} else {
					forest_jungle_safari_gotoClose = false;
				}

			}else{

			if(window.forest_jungle_safari_responsiveMenu){
			}}}}
		}
	 	forest_jungle_safari_checkfocusdElement();
	}
});

jQuery('document').ready(function($){
  setTimeout(function () {
		jQuery("#preloader").fadeOut("slow");
  },1000);

	$(window).scroll(function(){
	var sticky = $('.header-sticky'),
		scroll = $(window).scrollTop();

	if (scroll >= 100) sticky.addClass('header-fixed');
	else sticky.removeClass('header-fixed');
});
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

document.addEventListener('DOMContentLoaded', function() {
  var carousel = document.querySelector('#carouselExampleCaptions');
  var currentPageNum = document.querySelector('.current-page-num');
  if (carousel) {
    carousel.addEventListener('slid.bs.carousel', function(event) {
      var activeIndex = event.to + 1; // Bootstrap carousel indices are 0-based
      currentPageNum.textContent = activeIndex + "/";
    });
  } 
});