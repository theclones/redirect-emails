;(function( $ ){

/**
* Setting up functionality for slick navigation
*/
function bosa_yoga_slickNavHeight (){
  var headerHeight = $( '.site-header-primary .main-header' ).outerHeight();
  var headerHeight = $( '.site-header-two' ).outerHeight();
  $('.slicknav_nav').css( 'top', headerHeight );
}

/**
* Setting up functionality for alternative menu
*/
function bosa_yoga_wpMenuAccordion( selector ){

  var $ele = selector + ' .header-navigation .menu-item-has-children > a';
  $( $ele ).each( function(){
    var text = $( this ).text();
    text = text + '<button class="fas fa-plus triangle"></button>';
    $( this ).html( text );
  });

  jQuery( document ).on( 'click', $ele + ' .triangle', function( e ){
    e.preventDefault();
    e.stopPropagation();

    $parentLi = $( this ).parent().parent( 'li' );
    $childLi = $parentLi.find( 'li' );

    if( $parentLi.hasClass( 'open' ) ){
      /**
      * Closing all the ul inside and 
      * removing open class for all the li's
      */
      $parentLi.removeClass( 'open' );
      $childLi.removeClass( 'open' );

      $( this ).parent( 'a' ).next().slideUp();
      $( this ).parent( 'a' ).next().find( 'ul' ).slideUp();
    }else{

      $parentLi.addClass( 'open' );
      $( this ).parent( 'a' ).next().slideDown();
    }
  });
};

/**
* Setting up functionality for fixed header
*/

$mastheadHeight = $( '#masthead.site-header' ).height();
$stickymastheadHeight = $( '#masthead .overlay-header' ).height();

function bosa_yoga_fixed_header(){
  $notificationHight = $( '.notification-bar' ).height();
  $logo_selector = document.getElementById( 'headerLogo' );
  var width = $( window ).width();

  if ( $logo_selector && BOSAYOGA.fixed_nav && BOSAYOGA.fixed_header_logo ) { 
    if ( $mastheadHeight < $(window).scrollTop()){
      if ( BOSAYOGA.separate_logo == '' ){
        if ( BOSAYOGA.the_custom_logo !== '' ){
          $logo_selector.src = BOSAYOGA.the_custom_logo;
        }
      }else{
        $( '.site-header .site-branding img' ).css( 'display' , 'block' );
        if( !BOSAYOGA.mobile_fixed_nav_off || width >= 782 ){
          $logo_selector.src = BOSAYOGA.separate_logo;
        }
      }
    }else{
      if ( BOSAYOGA.header_two_logo !== '' && ( BOSAYOGA.is_front_page || BOSAYOGA.overlay_post || BOSAYOGA.overlay_page ) && BOSAYOGA.is_header_two ){
         $logo_selector.src = BOSAYOGA.header_two_logo;
      }else if ( BOSAYOGA.the_custom_logo !== '' ) {
          $logo_selector.src = BOSAYOGA.the_custom_logo;
      }else if ( BOSAYOGA.separate_logo !== '' ){
        $( '.site-header .site-branding img' ).css( 'display' , 'none' );
      }
    }
  }
  if ( $mastheadHeight > $( window ).scrollTop() || $( window ).scrollTop() == 0 ) {
      if ( BOSAYOGA.fixed_nav && $( '#masthead.site-header' ).hasClass( 'sticky-header' )){
          $( '#masthead.site-header' ).removeClass( 'sticky-header' );
          // Fixed header in admin bar
          if( BOSAYOGA.is_admin_bar_showing && width >= 782 ){
            $( '.fixed-header' ).css( 'marginTop', 0 );
          }
          if( BOSAYOGA.is_admin_bar_showing && width <= 781 ){
            $( '.fixed-header' ).css( 'marginTop', 0 );
          }
      }
  }else if ( BOSAYOGA.fixed_nav && !$( '#masthead.site-header' ).hasClass( 'sticky-header' )){
    if( !BOSAYOGA.mobile_fixed_nav_off || width >= 782 ){
      $( '#masthead.site-header' ).addClass( 'sticky-header' ).fadeIn();
    }
      // Fixed header in admin bar
      if( BOSAYOGA.is_admin_bar_showing && width >= 782 ){
        $( '.fixed-header' ).css( 'marginTop', 32 );
      }
      if( BOSAYOGA.is_admin_bar_showing && width <= 781 ){
        $( '.fixed-header' ).css( 'marginTop', 46 );
      }
      if( BOSAYOGA.is_admin_bar_showing && width <= 600 ){
        $( '.fixed-header' ).css( 'marginTop', 0 );
      }
      if( BOSAYOGA.mobile_fixed_nav_off && width <= 781 ){
        $( '.fixed-header' ).css( 'marginTop', 0 );
    }
  }
}

/**
* Setting up functionality for header two - transparent header
*/
function bosa_yoga_header_two_postion() {
  var width = $( window ).width();

  if ( BOSAYOGA.is_header_two ) {
    if( BOSAYOGA.is_admin_bar_showing && width >= 782 ){
      $( '.overlay-header' ).css( 'top' , 32 );
    }
    if( BOSAYOGA.is_admin_bar_showing && width <= 781 ){
      $( '.overlay-header' ).css( 'top' , 46 );
    }
  }
}

/**
* Setting up call functions
*/
// Document ready
jQuery( document ).ready( function() {
  bosa_yoga_slickNavHeight();
  bosa_yoga_wpMenuAccordion( '#offcanvas-menu' );
  bosa_yoga_header_two_postion();

  /**
  * Offcanvas Menu
  */
  $( document ).on( 'click', '.offcanvas-menu-toggler, .close-offcanvas-menu button, .offcanvas-overlay', function( e ){
    e.preventDefault();
    $( 'body' ).toggleClass( 'offcanvas-slide-open' );
    setTimeout(function(){
      $( '.close-offcanvas-menu button' ).focus();
    }, 40);
  });
  $( '.close-offcanvas-menu button' ).click( function(){
    setTimeout(function(){
      $( '.offcanvas-menu-toggler' ).focus();
    }, 50);
  });

  jQuery( 'body' ).append( '<div class="offcanvas-overlay"></div>' );

  /**
  * Desktop Hamburger Nav on focus out event
  */
   jQuery( '.offcanvas-menu-wrap .offcanvas-menu-inner' ).on( 'focusout', function () {
     var $elem = jQuery( this );
     // let the browser set focus on the newly clicked elem before check
     setTimeout(function () {
       if ( ! $elem.find( ':focus' ).length ) {
         jQuery( '.offcanvas-menu-toggler' ).trigger( 'click' );
         $( '.offcanvas-menu-toggler' ).focus();
       }
     }, 0);
   });

  /**
   * Header Search from
  */
  jQuery( document ).on( 'click','.search-icon, .close-button', function(){
    $( '.header-search' ).toggleClass("search-in");
    $( '.header-search input' ).focus();
  });

  // search toggle on focus out event
  jQuery( '.header-search form' ).on( 'focusout', function () {
    var $elem = jQuery(this);
      // let the browser set focus on the newly clicked elem before check
      setTimeout(function () {
          if ( ! $elem.find( ':focus' ).length ) {
            jQuery( '.search-icon' ).trigger( 'click' );
            $( '.search-icon' ).focus();
          }
      }, 0);
  });

  /**
  * Header image slider
  */
  $( '.header-image-slider' ).slick({
      dots: true,
      arrows: true,
      adaptiveHeight: false,
      fade: BOSAYOGA.header_image_slider.fade,
      speed: parseInt( BOSAYOGA.header_image_slider.fadeControl ),
      cssEase: 'linear',
      autoplay: BOSAYOGA.header_image_slider.autoplay,
      autoplaySpeed: BOSAYOGA.header_image_slider.autoplaySpeed,
      infinite: true,
      prevArrow: $( '.header-slider-prev' ),
      nextArrow: $( '.header-slider-next' ),
      rows: 0,
      appendDots: $( '.header-slider-dots' ),
    });
  $( '.header-image-slider' ).attr( 'dir', 'ltr' );

  /**
  * Slick navigation
  */
  $( '#primary-menu' ).slicknav({
      duration: 500,
      closedSymbol: '<i class="fa fa-plus"></i>',
      openedSymbol: '<i class="fa fa-minus"></i>',
      appendTo: '.mobile-menu-container',
      allowParentLinks: true,
      nestedParentLinks : false,
      label: BOSAYOGA.responsive_header_menu_text,
      closeOnClick: true, // Close menu when a link is clicked.
  });
  
  /**
  * Slick navigation mobile on focus out event
  */
  jQuery( '.slicknav_menu .slicknav_nav' ).on( 'focusout', function () {
    var $elem = jQuery(this);
    // let the browser set focus on the newly clicked elem before check
    setTimeout(function () {
      if ( ! $elem.find( ':focus' ).length ) {
        jQuery( '.slicknav_open' ).trigger( 'click' );
      }
    }, 0);
  });

  /**
  * Header two & header ten banner content
  */
  if ( $( '.site-header' ).hasClass( 'header-two' ) ) {
    $( '.home .section-banner .banner-content' ).css( 'marginTop' , $stickymastheadHeight );
  }

  /**
  * Main posts slider
  */
  $( '.main-slider' ).slick({
      dots: true,
      arrows: true,
      adaptiveHeight: false,
      fade: BOSAYOGA.main_slider.fade,
      speed: parseInt( BOSAYOGA.main_slider.fadeControl ),
      cssEase: 'linear',
      autoplay: BOSAYOGA.main_slider.autoplay,
      autoplaySpeed: BOSAYOGA.main_slider.autoplaySpeed,
      infinite: true,
      prevArrow: $( '.main-slider-prev' ),
      nextArrow: $( '.main-slider-next' ),
      rows: 0,
      appendDots: $( '.main-slider-dots' ),
    });
  $( '.main-slider' ).attr( 'dir', 'ltr' );

  /**
   * Highlight posts slider
   */
  $( '.highlight-post-slider' ).slick({
      arrows: true,
      dots: true,
      slidesToShow: BOSAYOGA.home_highlight_posts.slidesToShow,
      slidesToScroll: 1,
      adaptiveHeight: false,
      autoplay: BOSAYOGA.home_highlight_posts.autoplay,
      autoplaySpeed: BOSAYOGA.home_highlight_posts.autoplaySpeed,
      infinite: false,
      rows: 0,
      prevArrow: $( '.highlight-posts-prev' ),
      nextArrow: $( '.highlight-posts-next' ),
      appendDots: $( '.highlight-posts-dots' ),
      responsive: [
        {
          breakpoint: 1023,
          settings: {
            slidesToShow: 3,
            slidesToScroll: 1,
          }
        },
        {
          breakpoint: 991,
          settings: {
            slidesToShow: 2,
            slidesToScroll: 1
          }
        },
        {
          breakpoint: 480,
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1
          }
        }
      ]
    });
  $( '.highlight-post-slider' ).attr( 'dir', 'ltr' );

  /**
  * Sticky sidebar
  */
  if( BOSAYOGA.sticky_sidebar ){
    $( '.content-area, .left-sidebar, .right-sidebar' ).theiaStickySidebar({
      // Settings
      additionalMarginTop: 30,
    });
  }

  /**
  * Back to top
  */
  jQuery( document ).on( 'click', '#back-to-top a', function() {
      $( 'html, body' ).animate({ scrollTop: 0 }, 800);
      return false;
  });

}); // closing document ready

// Window resize
jQuery( window ).on( 'resize', function(){
  bosa_yoga_slickNavHeight();
  bosa_yoga_fixed_header();
  bosa_yoga_header_two_postion();
});

// Window load
jQuery( window ).on( 'load', function(){

  /**
  * Site Preloader
  */
  $( '#site-preloader' ).fadeOut( 500 );

  /**
  * Back to top
  */
  if( BOSAYOGA.enable_scroll_top == true && $(window).scrollTop() > 200 ){
    $( '#back-to-top' ).fadeIn( 200 );
  } else{
    $( '#back-to-top' ).fadeOut( 200 );
  }

  /**
  * Masonry wrapper
  */
  if( jQuery( '.masonry-wrapper' ).length > 0 ){
    $grid = jQuery( '.masonry-wrapper' ).masonry({
      itemSelector: '.grid-post',
      // percentPosition: true,
      isAnimated: true,
    }); 
  }

  /**
  * Jetpack's infinite scroll on masonry layout
  */
  infinite_count = 0;
    $( document.body ).on( 'post-load', function () {

    infinite_count = infinite_count + 1;
    var container = '#infinite-view-' + infinite_count;
    $( container ).hide();

    $( $( container + ' .grid-post' ) ).each( function(){
      $items = $( this );
        $grid.append( $items ).masonry( 'appended', $items );
    });

    setTimeout( function(){
      $grid.masonry( 'layout' );
    },500);
    });

}); // closing window load

// Window scroll
jQuery( window ).on( 'scroll', function(){
  bosa_yoga_fixed_header();

  /**
  * Back to top
  */
  if( BOSAYOGA.disable_scroll_top == false && $(window).scrollTop() > 200 ){
    $( '#back-to-top' ).fadeIn( 200 );
  } else{
    $( '#back-to-top' ).fadeOut( 200 );
  }
}); // closing window scroll

})( jQuery );