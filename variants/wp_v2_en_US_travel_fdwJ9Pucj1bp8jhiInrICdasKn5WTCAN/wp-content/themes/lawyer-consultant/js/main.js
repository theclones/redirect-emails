// Menu
function lawyer_consultant_openNav() {
  jQuery(".sidenav").addClass('show');
}
function lawyer_consultant_closeNav() {
  jQuery(".sidenav").removeClass('show');
}

( function( window, document ) {
  function lawyer_consultant_keepFocusInMenu() {
    document.addEventListener( 'keydown', function( e ) {
      const lawyer_consultant_nav = document.querySelector( '.sidenav' );

      if ( ! lawyer_consultant_nav || ! lawyer_consultant_nav.classList.contains( 'show' ) ) {
        return;
      }
      const elements = [...lawyer_consultant_nav.querySelectorAll( 'input, a, button' )],
        lawyer_consultant_lastEl = elements[ elements.length - 1 ],
        lawyer_consultant_firstEl = elements[0],
        lawyer_consultant_activeEl = document.activeElement,
        tabKey = e.keyCode === 9,
        shiftKey = e.shiftKey;

      if ( ! shiftKey && tabKey && lawyer_consultant_lastEl === lawyer_consultant_activeEl ) {
        e.preventDefault();
        lawyer_consultant_firstEl.focus();
      }

      if ( shiftKey && tabKey && lawyer_consultant_firstEl === lawyer_consultant_activeEl ) {
        e.preventDefault();
        lawyer_consultant_lastEl.focus();
      }
    } );
  }
  lawyer_consultant_keepFocusInMenu();
} )( window, document );

(function ($) {

    $(window).load(function () {
        $("#pre-loader").delay(500).fadeOut();
        $(".loader-wrapper").delay(1000).fadeOut("slow");

    });

    $(document).ready(function () {

        /*-- tooltip --*/
        $('[data-toggle="tooltip"]').tooltip();

        /*-- Button Up --*/
        var btnUp = $('<div/>', { 'class': 'btntoTop' });
        btnUp.appendTo('body');
        $(document).on('click', '.btntoTop', function (e) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: 0
            }, 700);
        });

        $(window).on('scroll', function () {
            if ($(this).scrollTop() > 200)
                $('.btntoTop').addClass('active');
            else
                $('.btntoTop').removeClass('active');
        });

        /*-- Reload page when width is between 320 and 768px and only from desktop */
        var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ? true : false;
        $(window).on('resize', function () {
            var win = $(this); //this = window
            if (win.width() > 320 && win.width() < 991 && isMobile == false && !$("body").hasClass("elementor-editor-active")) {
                location.reload();
            }
        });
    });

})(this.jQuery);

// custom-header-text
(function( $ ) {
    // Update site title and description color in real-time
    wp.customize( 'header_textcolor', function( value ) {
        value.bind( function( newval ) {
            if ( 'blank' === newval ) {
                $( 'header.style1 .logo h1.site-title a,header.style1 .logo p.site-description' ).css({
                    'clip': 'rect(1px, 1px, 1px, 1px)',
                    'position': 'absolute'
                });
            } else {
                $( 'header.style1 .logo h1.site-title a,header.style1 .logo p.site-description' ).css({
                    'clip': 'auto',
                    'position': 'relative',
                    'color': newval
                });
            }
        });
    });
})( jQuery );
