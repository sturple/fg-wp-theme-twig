/*!
 ***************** theme.js ***********************
 */

var sizePostfix = 'lg';
if (window.innerWidth <= 1200) {
  sizePostfix = 'md';
}
if (window.innerWidth <= 768) {
  sizePostfix = 'sm';
}

jQuery(function($) {
  /*** Email cloaking ***/
  var lastWindowTopLocation = $(window).scrollTop();
  $('.mailto, a[data-domain]').each(function() {
    $(this).attr('href', 'mailto:' + $(this).attr('data-prefix') + '@' + $(this).attr('data-domain'));
    if ($(this).text().length < 2) {
      $(this).text($(this).attr('data-prefix') + '@' + $(this).attr('data-domain'));
    }
  });

  /*** Updates all background images according to screen size; */
  $('*[data-background-' + sizePostfix + ']').each(function() {
    $(this).css({
      'background-image': 'url(' + $(this).data('background-' + sizePostfix) + ')'
    });
  });

  /*** Updates all  images according to screen size;  */
  //$('img[data-image-'+sizePostfix+']')

  $('img').each(function() {
    $(this).attr('src', $(this).attr('data-image-' + sizePostfix));
    $(this).attr('alt', $(this).attr('data-image-' + sizePostfix));

  });
  $.each(document.getElementsByTagName("img"), function(index, value) {
    if (this.getAttribute('src') === null) {
      this.setAttribute('src', this.getAttribute('data-image'));
    }

  });

  $('.panel-group').on('shown.bs.collapse', function(event) {
    $(this).find('.panel-title *').removeClass('active');

    $(this).find('.panel-title *[href="#' + $(event.target).attr('id') + '"]').addClass('active');

  });

  var imgs=$('#articleContent img');
  if ((imgs.length > 0) && imgs.smoothZoom) {
    imgs.smoothZoom({
      navigationRight: '<i class=\"fa fa-angle-right\"></i>',
      navigationLeft: '<i class=\"fa fa-angle-left\"></i>'
    });

  }

  //adding hover code to bootstrap menus
  /*$('ul.nav li.dropdown').on('hover',
    function() {
      $(this).find('.dropdown-menu').stop(true, true).delay(200).fadeIn(500);
    }, function() {
      $(this).find('.dropdown-menu').stop(true, true).delay(200).fadeOut(500);
  });*/
  
  
  if (($('.script-grid-gallery').length > 0) && ($().fgGallery) && $().isotope && $().imagesLoaded) {
    $('.script-grid-gallery').fgGallery({
      thumbpadding: $(this).data('thumbpadding'),
      thumbsperrow: $(this).data('thumbsperrow'),
      options: {
        feature: false
      },
      debug: false,
      onInit: function($element) {
        var $grid = $element.find('ul').isotope({
          itemSelector: 'li',
          layoutMode: 'fitRows'
        });
        $grid.imagesLoaded().progress(function() {
          $grid.isotope('layout');
          $element.find('.script-feature img').fadeIn();
        });
        $('.gallery-filters').on('click', 'button', function() {
          var filterValue = $(this).data('filter');
          $grid.isotope({
            filter: filterValue
          });
          $('.gallery-filters button').removeClass('active');

          $(this).addClass('active');
        });
      },
      onClickThumb: function(plugin, $thumb, event) {
        return true;
      }
    });
  }
  
  //this is the code for stickycomponent
  if ( ($('.scipt-sidebar-sticky') ) && ( typeof $().fgStickyComponent === 'function') ) {
    $('.script-sidebar-sticky').fgStickyComponent({
        startselector: $('.script-sidebar-sticky').parent(),
        topoffset : 36,
		bottomoffset: 36,
		removesize : 990,
		stopselector : '.footer',
    });
	
	$('.script-sidebar-sticky').on('fg.stickycomponent.active', function(e, globals, wtop){
		var left = $(this).parent().parent().position().left +15;				
		$(this).css({'left': left+'px'});				
	});
	$('.script-sidebar-sticky').on('fg.stickycomponent.moving', function(e, globals, wtop){
		var left = $(this).parent().parent().position().left +15;				
		$(this).css({'left': left+'px'});				
	});
	$('.script-sidebar-sticky').on('fg.stickycomponent.bottom', function(e, globals, wtop){		
		$(this).css({'left': 'inherit'});				
	});
	$('.script-sidebar-sticky').on('fg.stickycomponent.normal', function(e, globals, wtop){
		$(this).css({'left': 'inherit'});				
	});
  }

    

  $(window).scroll(function() {
    didScroll = true;
    // this checks if there is a youtube video
    $('.script-autoplay').each(function() {
      var inview = (($(window).outerHeight() + $(window).scrollTop()) > ($(this).offset().top + $(this).outerHeight()));

      // top of screen passed object above && top + height is greater than item offscreen below
      var outview = (($(window).scrollTop() > $(this).offset().top) || (($(window).scrollTop() + $(window).outerHeight()) < $(this).offset().top));
      //	console.log(($(window).scrollTop() + $(window).outerHeight()) < $(this).offset().top , $(window).scrollTop(), $(this).offset().top)
      if (($(this).data('playstate') === undefined) && (inview) && videoready) {
        $(this).data('playstate', 'play');
        youtube_hp.playVideo();

      } else if ((outview) && ($(this).data('playstate') == 'play')) {
        $(this).data('playstate', 'stop');
        youtube_hp.stopVideo();

      }

    });
    $('.script-parallax').each(function() {
      if (updateParallax($(this))) {}

    });
  });
  
  // this animates to hash
  var hash = window.location.hash;
  if ((hash.length>1) && ($('ul.nav a[href="' + hash + '"]').length > 0)) {
	hash = hash && $('ul.nav a[href="' + hash + '"]').tab('show');
	$('html, body').animate({ scrollTop: $('ul.nav a[href="' + hash + '"]').offset().top -50   }, 2000);
  }
  
  $('header ul li:first-child').each(function (i, e) {
	e=$(e);
	e.click(e.toggleClass.bind(e,'active'));
  });
  
  // this is a fix to add placeholders to form-7 in footer
  $('.footer .form-group > label.sr-only ~ * > input[type="text"], .footer .form-group > label.sr-only ~ * > input[type="email"], .footer .form-group > label.sr-only ~ * > textarea').each(function (i, e) {
    var label=$(e.parentElement.parentElement).children('label.sr-only')[0];
    e.setAttribute('placeholder',label.innerHTML);
  });

});
