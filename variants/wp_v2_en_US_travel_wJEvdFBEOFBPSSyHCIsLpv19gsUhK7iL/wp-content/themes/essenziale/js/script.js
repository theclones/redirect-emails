/*
 * jQuery theme functions file
 * https://www.wpinprogress.com
 *
 * Copyright 2020, wpinprogress
 * Licensed under MIT license
 * https://opensource.org/licenses/mit-license.php
 */

 jQuery.noConflict()(function($){

	"use strict";

	$(document).ready(function(){
		
		$(".essenziale-carousel").owlCarousel({
		
			dots:true,
			margin:10,
			responsive:{
		
				0:{
					items:1
				},
		
				600:{
					items:2
				},
		
				1170:{
					items:4
				},
		
			}
			
		});
		
	});
	
/* ===============================================
   fitVids
   =============================================== */

	function essenziale_embed() {

		$('.post-content').imagesLoaded(function () {
			$('.embed-container').fitVids();
		});
		
	}

	$(window).load(essenziale_embed);
	$(document).ready(essenziale_embed);

});
