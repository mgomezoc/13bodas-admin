(function ($) {
  "use strict";

  /* ==========================================================================
   *  Helpers (fail-safe)
   * ========================================================================== */
  function safeCall(fn, args) {
    try {
      if (typeof fn === "function") return fn.apply(null, args || []);
    } catch (e) {
      // no-op: evitamos que truene toda la app por una dependencia opcional
    }
  }

  function hasJqPlugin($el, pluginName) {
    return !!($el && $el.length && typeof $el[pluginName] === "function");
  }

  /* ==========================================================================
   *  Navbar shrink + scrollspy (Bootstrap 5)
   * ========================================================================== */
  var navbarShrink = function () {
    var navbarCollapsible = document.body.querySelector("#mainNav");
    if (!navbarCollapsible) return;
    if (window.scrollY === 0) navbarCollapsible.classList.remove("navbar-shrink");
    else navbarCollapsible.classList.add("navbar-shrink");
  };

  navbarShrink();
  document.addEventListener("scroll", navbarShrink);

  var mainNav = document.body.querySelector("#mainNav");
  if (mainNav && window.bootstrap && typeof bootstrap.ScrollSpy === "function") {
    new bootstrap.ScrollSpy(document.body, {
      target: "#mainNav",
      rootMargin: "0px 0px -40%",
    });
  }

  // Collapse responsive navbar on item click
  var navbarToggler = document.body.querySelector(".navbar-toggler");
  var responsiveNavItems = [].slice.call(document.querySelectorAll("#navbarResponsive .nav-link"));
  responsiveNavItems.forEach(function (item) {
    item.addEventListener("click", function () {
      if (!navbarToggler) return;
      if (window.getComputedStyle(navbarToggler).display !== "none") navbarToggler.click();
    });
  });

  /* ==========================================================================
   *  Parallax background
   * ========================================================================== */
  function bgParallax() {
    if (!$(".parallax").length) return;

    // En móviles, reduce trabajo
    var isMobile = window.innerWidth < 768;

    $(".parallax").each(function () {
      var $section = $(this);
      var img = $section.data("bg-image");
      if (img) {
        $section.css({
          backgroundImage: "url(" + img + ")",
          backgroundSize: "cover",
        });
      }

      if (isMobile) {
        $section.css({ backgroundPosition: "center center" });
        return;
      }

      var top = $section.position().top;
      var resize = top - $(window).scrollTop();
      var speed = parseFloat($section.data("speed")) || 7;
      var doParallax = -(resize / speed);

      $section.css({
        backgroundPosition: "50% " + doParallax + "px",
      });
    });
  }

  /* ==========================================================================
   *  Hero slider background + Slick
   * ========================================================================== */
  function sliderBgSetting() {
    if (!$(".hero-slider .slide-item").length) return;

    $(".hero-slider .slide-item").each(function () {
      var $this = $(this);
      var img = $this.find(".slider-bg").attr("src");
      if (!img) return;

      $this.css({
        backgroundImage: "url(" + img + ")",
        backgroundSize: "cover",
        backgroundPosition: "center center",
      });
    });
  }

  function heroSlider() {
    if (!$(".hero-slider").length) return;
    if (!hasJqPlugin($(".hero-slider"), "slick")) return;

    $(".hero-slider").slick({
      arrows: true,
      prevArrow: '<button type="button" class="slick-prev">Previous</button>',
      nextArrow: '<button type="button" class="slick-next">Next</button>',
      dots: true,
      fade: true,
      cssEase: "linear",
    });
  }

  /* ==========================================================================
   *  Equal height couple section
   * ========================================================================== */
  function setTwoColEqHeight($col1, $col2) {
    if (!$col1.length || !$col2.length) return;

    var h1 = $col1.innerHeight();
    var h2 = $col2.innerHeight();

    if (h1 > h2) $col2.css({ height: h1 + 1 + "px" });
    else $col1.css({ height: h2 + 1 + "px" });
  }

  function popupSaveTheDateCircle() {
    var $el = $(".save-the-date");
    if ($el.length) $el.addClass("popup-save-the-date");
  }

  /* ==========================================================================
   *  WOW
   * ========================================================================== */
  var wow = (window.WOW)
    ? new WOW({ boxClass: "wow", animateClass: "animated", offset: 0, mobile: true, live: true })
    : null;

  /* ==========================================================================
   *  Preloader
   * ========================================================================== */
  function preloader() {
    if (!$(".preloader").length) return;

    $(".preloader").delay(100).fadeOut(500, function () {
      if (wow) wow.init();
      popupSaveTheDateCircle();
      heroSlider();
    });
  }

  /* ==========================================================================
   *  Fancybox / Magnific
   * ========================================================================== */
  if ($(".gallery-fancybox").length && hasJqPlugin($(".fancybox"), "fancybox")) {
    $(".fancybox").fancybox({
      openEffect: "elastic",
      closeEffect: "elastic",
      wrapCSS: "project-fancybox-title-style",
    });
  }

  if ($(".video-play-btn").length && $.fancybox) {
    $(".video-play-btn").on("click", function () {
      $.fancybox({
        href: this.href,
        type: $(this).data("type"),
        title: this.title,
        helpers: { title: { type: "inside" }, media: {} },
        beforeShow: function () {
          $(".fancybox-wrap").addClass("gallery-fancybox");
        },
      });
      return false;
    });
  }

  if (hasJqPlugin($(".popup-youtube, .popup-vimeo, .popup-gmaps"), "magnificPopup")) {
    $(".popup-youtube, .popup-vimeo, .popup-gmaps").magnificPopup({
      type: "iframe",
      mainClass: "mfp-fade",
      removalDelay: 160,
      preloader: false,
      fixedContentPos: false,
    });
  }

  if ($(".popup-gallery").length && hasJqPlugin($(".popup-gallery"), "magnificPopup")) {
    $(".popup-gallery").magnificPopup({
      delegate: "a",
      type: "image",
      gallery: { enabled: true },
      zoom: {
        enabled: true,
        duration: 300,
        easing: "ease-in-out",
        opener: function (openerElement) {
          return openerElement.is("img") ? openerElement : openerElement.find("img");
        },
      },
    });
  }

  if ($(".popup-image").length && hasJqPlugin($(".popup-image"), "magnificPopup")) {
    $(".popup-image").magnificPopup({
      type: "image",
      zoom: {
        enabled: true,
        duration: 300,
        easing: "ease-in-out",
        opener: function (openerElement) {
          return openerElement.is("img") ? openerElement : openerElement.find("img");
        },
      },
    });
  }

  /* ==========================================================================
   *  Isotope filtering
   * ========================================================================== */
  function sortingGallery() {
    if (!$(".sortable-gallery .gallery-filters").length) return;
    var $container = $(".gallery-container");
    if (!hasJqPlugin($container, "isotope")) return;

    $container.isotope({
      filter: "*",
      animationOptions: { duration: 750, easing: "linear", queue: false },
    });

    $(".gallery-filters li a").on("click", function () {
      $(".gallery-filters li .current").removeClass("current");
      $(this).addClass("current");
      var selector = $(this).attr("data-filter");

      $container.isotope({
        filter: selector,
        animationOptions: { duration: 750, easing: "linear", queue: false },
      });

      return false;
    });
  }

  /* ==========================================================================
   *  Masonry layout
   * ========================================================================== */
  function masonryGridSetting() {
    if (!$(".masonry-gallery").length) return;
    var $grid = $(".masonry-gallery");

    if (!hasJqPlugin($grid, "masonry")) return;

    var msnry = $grid.masonry({
      itemSelector: ".grid",
      columnWidth: ".grid",
      percentPosition: true,
    });

    // Re-layout cuando carguen imágenes si imagesLoaded existe
    if (hasJqPlugin($grid, "imagesLoaded")) {
      $grid.imagesLoaded().progress(function () {
        msnry.masonry("layout");
      });
    }
  }

  // Helper opcional por si renderizas galería vía AJAX luego:
  window.__13BODAS__ = window.__13BODAS__ || {};
  window.__13BODAS__.relayoutGallery = function () {
    safeCall(sortingGallery);
    safeCall(masonryGridSetting);
  };

  /* ==========================================================================
   *  Countdown dinámico (13Bodas)
   * ========================================================================== */
  function initCountdown() {
    if (!$("#clock").length) return;
    if (!hasJqPlugin($("#clock"), "countdown")) return;

    // Prioridad: window.__INVITATION__.eventDateISO
    var dateIso = window.__INVITATION__ && window.__INVITATION__.eventDateISO;
    var dt = dateIso ? new Date(dateIso) : null;

    // Fallback (si no hay fecha válida)
    if (!dt || isNaN(dt.getTime())) {
      // Si no hay fecha, oculta o deja un placeholder
      $("#clock").html("");
      return;
    }

    // Formato que entiende jquery.countdown: YYYY/MM/DD HH:MM:SS
    function pad(n) { return (n < 10 ? "0" : "") + n; }
    var target =
      dt.getFullYear() + "/" +
      pad(dt.getMonth() + 1) + "/" +
      pad(dt.getDate()) + " " +
      pad(dt.getHours()) + ":" +
      pad(dt.getMinutes()) + ":" +
      pad(dt.getSeconds());

    $("#clock").countdown(target, function (event) {
      $(this).html(
        event.strftime(
          ""
          + '<div class="box"><div>%D</div> <span>Días</span> </div>'
          + '<div class="box"><div>%H</div> <span>Horas</span> </div>'
          + '<div class="box"><div>%M</div> <span>Min</span> </div>'
          + '<div class="box"><div>%S</div> <span>Seg</span> </div>'
        )
      );
    });
  }

  /* ==========================================================================
   *  Carousels
   * ========================================================================== */
  function initCarousels() {
    if ($(".story-slider").length && hasJqPlugin($(".story-slider"), "owlCarousel")) {
      $(".story-slider").owlCarousel({
        items: 1,
        dots: false,
        autoplay: true,
        autoplayTimeout: 3000,
        smartSpeed: 1000,
        loop: true,
      });
    }

    if ($(".gif-registration-slider").length && hasJqPlugin($(".gif-registration-slider"), "owlCarousel")) {
      $(".gif-registration-slider").owlCarousel({
        items: 3,
        dots: false,
        autoplay: true,
        autoplayTimeout: 3000,
        smartSpeed: 1000,
        loop: true,
        margin: 20,
        stagePadding: 10,
        responsive: { 0: { items: 1 }, 480: { items: 2 }, 768: { items: 3 } },
      });
    }

    if ($(".media-carousel").length && hasJqPlugin($(".media-carousel"), "owlCarousel")) {
      $(".media-carousel").owlCarousel({
        items: 1,
        smartSpeed: 500,
        nav: true,
        navText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"],
        dots: false,
      });
    }
  }

  /* ==========================================================================
   *  RSVP (13Bodas)
   *  NOTA: Se deja deshabilitado aquí porque ya lo manejas en tu index.php con AJAX CI4.
   * ========================================================================== */
  function disableLegacyRsvpHandler() {
    // Si el template original intentaba validar/mandar a mail.php, lo evitamos.
    // Tu vista ya bindeará el submit con su lógica CI4.
    return;
  }

  /* ==========================================================================
   *  Music box, back-to-top, extras (opcionales)
   * ========================================================================== */
  function initMisc() {
    if ($(".music-box").length) {
      var musicBtn = $(".music-box-toggle-btn");
      var musicBox = $(".music-holder");
      musicBtn.on("click", function () {
        musicBox.toggleClass("toggle-music-box");
        return false;
      });
    }

    if ($(".back-to-top-btn").length) {
      $(".back-to-top-btn").on("click", function () {
        $("html,body").animate({ scrollTop: 0 }, 600);
        return false;
      });
    }

    // Ripples / particleground / YTPlayer sólo si existen
    if ($(".ripple").length && hasJqPlugin($(".ripple"), "ripples")) {
      $(".ripple").ripples({ resolution: 512, dropRadius: 20, perturbance: 0.04 });
    }

    if ($(".particleground").length && hasJqPlugin($(".particleground"), "particleground")) {
      $(".particleground").particleground({
        dotColor: "#78c1b3",
        lineColor: "#5e9a8e",
        lineWidth: 0.7,
        particleRadius: 6,
      });
    }

    if ($("#video-background").length && hasJqPlugin($("#video-background"), "YTPlayer")) {
      $("#video-background").YTPlayer({
        showControls: false,
        playerVars: {
          modestbranding: 0,
          autoplay: 1,
          controls: 1,
          showinfo: 0,
          wmode: "transparent",
          branding: 0,
          rel: 0,
          autohide: 0,
          origin: window.location.origin,
        },
      });
    }
  }

  /* ==========================================================================
   *  Init lifecycle
   * ========================================================================== */
  $(window).on("load", function () {
    preloader();
    sliderBgSetting();
    bgParallax();

    // Igualar columnas de "Nosotros"
    if ($(".wedding-couple-section").length) {
      setTwoColEqHeight(
        $(".wedding-couple-section .gb .img-holder"),
        $(".wedding-couple-section .gb .details")
      );
    }

    sortingGallery();
    masonryGridSetting();
    initCountdown();
    initCarousels();
    disableLegacyRsvpHandler();
    initMisc();

    // Legacy funcs (si existen en jquery-plugin-collection.js)
    safeCall(window.toggleMobileNavigation);
    safeCall(window.smallNavFunctionality);

    // smoothScrolling: si existe en tu colección
    safeCall(window.smoothScrolling, [
      $("#navbar > ul > li > a[href^='#']"),
      $(".header-style-1 .navigation").innerHeight(),
    ]);
  });

  $(window).on("scroll", function () {
    bgParallax();

    // Legacy sticky/menu: sólo si existe
    safeCall(window.activeMenuItem, [$(".navigation-holder")]);
  });

  $(window).on("resize", function () {
    safeCall(window.toggleClassForSmallNav);

    clearTimeout($.data(this, "resizeTimer"));
    $.data(
      this,
      "resizeTimer",
      setTimeout(function () {
        safeCall(window.smallNavFunctionality);
      }, 200)
    );
  });
})(window.jQuery);
