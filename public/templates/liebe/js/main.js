/*
Template Name: Liebe
Author: Ingrid Kuhn
Author URI: themeforest/user/ingridk
Version: 1.0
*/

"use strict";
$(document).ready(function() {

  //Countdown
  
    $(function(){
			$('#countdown').countdowntimer({
			dateAndTime : "2024/06/19 19:00:00",  // <-- edit yyyy / mm / dd / time
			size : "lg",
			// if the date has over 4 digits in days then add an extra [0-9] in the first field bellow
			regexpMatchFormat: "([0-9][0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})",
			regexpReplaceWith:  " <div class='count'> $1<div class='displayformat'>Days</div></div> <div class='count'> $2<div class='displayformat'>Hours</div></div> <div class='count'> $3<div class='displayformat'>Minutes</div></div> <div class='count'> $4<div class='displayformat'>Seconds</div></div>"
			});
	});
	
    //Smooth Scroll 

    $('.page-scroll a').on('click', function(event) {
        var $anchor = $(this);
        $('html, body').stop().animate({
            scrollTop: $($anchor.attr('href')).offset().top
        }, 2500, 'easeInOutExpo');
        event.preventDefault();
    });
	
    //	Back Top Link

    var offset = 200;
    var duration = 500;
    $(window).scroll(function() {
        if ($(this).scrollTop() > offset) {
            $('.back-to-top').fadeIn(400);
        } else {
            $('.back-to-top').fadeOut(400);
        }
    });

    //Owl-carousels

    $("#story-carousel").owlCarousel({
        dots: true,
		 margin: 50,
        loop:true,
        autoplay: false,
        nav: true,
		  navText: [
            "<i class='fa fa-chevron-left'></i>",
            "<i class='fa fa-chevron-right'></i>"

        ],
        responsive: {
            1: {
                items: 1,
            },
			600: {
                items: 2,
            },
            1000: {
                items: 3,
            },
        }
    });

 
    $("#owl-attendants1,#owl-attendants2").owlCarousel({
        dots: true,
        loop: true,
        autoplay: false,
        nav: true,
        margin: 20,
        navText: [
                          "<i class='fa fa-chevron-left'></i>",
            "<i class='fa fa-chevron-right'></i>"
        ],
        responsive: {
            1: {
                items: 1,
            },
			600: {
                items: 2,
            },
            991: {
                items: 3,
            },
        }
    });
 
}); // end document ready


// Window scroll function

$(window).scroll(function() {
	
	   // Shrink Navbar on Scroll 	

	  if ($(document).scrollTop() > 50) {
		$('nav').addClass('shrink');
	  } else {
		$('nav').removeClass('shrink');
	  }
	  
});

//On Click  function
	$(document).on('click',function(){
		
		//Navbar toggle
		$('.navbar .collapse').collapse('hide');
		
	})	

// Window load function

$(window).load(function() {

    // Page Preloader 	

    $("#preloader").fadeOut("slow");
	
    // Pretty Photo

    $("a[data-gal^='prettyPhoto']").prettyPhoto({
        hook: 'data-gal'
    });
    ({
        animation_speed: 'normal',
        opacity: 1,
        show_title: true,
        allow_resize: true,
        counter_separator_label: '/',
        theme: 'light_square',
        /* light_rounded / dark_rounded / light_square / dark_square / facebook */
    });

    //Isotope 

    var $container = $('#lightbox');
    $container.isotope({
        filter: '*',
        animationOptions: {
            duration: 750,
            easing: 'linear',
            queue: false,
            layoutMode: 'masonry'
        }

    });
    $(window).smartresize(function() {
        $container.isotope({
            columnWidth: '.col-sm-3'
        });
    });

	//initialize skrollr
    skrollr.init({
        smoothScrolling: true,
		 smoothScrollingDuration: 1000,
		forceHeight: false			
    });

    // disable skrollr if using handheld device
    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
        skrollr.init().destroy();
    }
			
    //Isotope Nav Filter
	
    $('.category a').on('click', function() {
        $('.category .active').removeClass('active');
        $(this).addClass('active');

        var selector = $(this).attr('data-filter');
        $container.isotope({
            filter: selector,
            animationOptions: {
                duration: 750,
                easing: 'linear',
                queue: false
            }
        });
        return false;
    });
	


}); // end window load
