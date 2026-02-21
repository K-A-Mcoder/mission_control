/**
	Template Name 	 : W3CRM
	Author			 : DexignZone
	Author Portfolio : https://themeforest.net/user/dexignzone/portfolio
	
	Core script to handle the entire theme and core functions
**/

var W3Crm = (function () {
  "use strict";

  /* Search Bar ============ */
  var screenWidth = $(window).width();
  var screenHeight = $(window).height();

  /* NiceSelect Function*/
  var handleNiceSelect = function () {
    if (jQuery("select.nice-select").length > 0) {
      jQuery("select.nice-select").niceSelect();
      var elementArry = document.querySelectorAll("select.nice-select");
      elementArry.forEach((item, index) => {
        var tempArry = [];
        jQuery(item)
          .parents()
          .map((data, _) => {
            if (
              jQuery(jQuery(item).parents()[data]).css("overflow-x") ===
                "hidden" ||
              jQuery(jQuery(item).parents()[data]).css("overflow-x") ===
                "auto" ||
              jQuery(jQuery(item).parents()[data]).css("overflow") === "auto" ||
              jQuery(jQuery(item).parents()[data]).css("overflow") ===
                "hidden" ||
              jQuery(jQuery(item).parents()[data]).css("overflow-y") ===
                "hidden" ||
              jQuery(jQuery(item).parents()[data]).css("overflow-y") === "auto"
            ) {
              tempArry.push(jQuery(item).parents()[data]);
            }
          });
        if (
          jQuery(tempArry[0]).height() <
          jQuery(item).offset().top -
            jQuery(tempArry[0]).offset()?.top +
            jQuery(item).next().height() +
            10 +
            jQuery(item).next().find("ul.list").height()
        ) {
          jQuery(item).addClass("dropUp");
        }
      });

      $(".status-select").on("change", function () {
        $(this).removeClass(
          "status-complete status-pending status-testing status-progress status-high status-low status-medium"
        );
        var inputVal = $(this).val();
        if (inputVal === "pending") {
          $(this).addClass("status-pending");
        } else if (inputVal === "complete") {
          $(this).addClass("status-complete");
        } else if (inputVal === "testing") {
          $(this).addClass("status-testing");
        } else if (inputVal === "progress") {
          $(this).addClass("status-progress");
        } else if (inputVal === "medium") {
          $(this).addClass("status-medium");
        } else if (inputVal === "high") {
          $(this).addClass("status-high");
        } else if (inputVal === "low") {
          $(this).addClass("status-low");
        }
        $(this).niceSelect("update");
      });
    }
  };
  /* Datetimepicker Function*/
  var handleDatetimepicker = function () {
    if (jQuery(".calendar-container").length > 0) {
      $(".calendar-container").calendar({
        prevButton: '<i class="fa-solid fa-angle-left"></i>',
        nextButton: '<i class="fa-solid fa-angle-right"></i>',
      });
    }
  };

  var handlePreloader = function () {
    setTimeout(function () {
      jQuery("#preloader").remove();
      $("#main-wrapper").addClass("show");
    }, 800);
  };

  var handleMetisMenu = function () {
    if (jQuery("#menu").length > 0) {
      $("#menu").metisMenu();
    }
    jQuery(".metismenu > .mm-active ").each(function () {
      if (!jQuery(this).children("ul").length > 0) {
        jQuery(this).addClass("active-no-child");
      }
    });
  };

  var handlesvgmodal = function () {
    function convertString(who, deep) {
      if (!who || !who.tagName) return "";
      var txt,
        ax,
        el = document.createElement("div");

      el.appendChild(who.cloneNode(false));
      txt = el.innerHTML;
      if (deep) {
        ax = txt.indexOf(">") + 1;
        txt = txt.substring(0, ax) + who.innerHTML + txt.substring(ax);
      }
      el = null;
      return txt;
    }

    $(".svg-btn").on("click", function () {
      var name = $(this).closest(".svg-icons-ov").find(".svg-classname").text();
      $("#svg_img_Brassieresvg").find(".modal-title").text(name);

      var value = '<img src="images/iconly/light/' + name + '"/>';
      $(".iconValue").text(value);
    });

    $(".img-btn").on("click", function () {
      var name = $(this).closest(".svg-icons-ov").find(".svg-classname").text();

      $("#svg_img_Brassieresvg").find(".modal-title").text(name);

      function escapeHtml(html) {
        var text = document.createTextNode(html);
        var div = document.createElement("div");
        div.appendChild(text);
        return div.innerHTML;
      }
      var svgHtml = $(this).closest(".svg-icons-ov").find(".svg-icons-prev")[0];

      var elementCode = svgHtml.innerHTML;
      var escapedElementCode = escapeHtml(elementCode);

      $(".iconValue").html(escapedElementCode);
    });
  };

  var handleAllChecked = function () {
    $("#checkAll").on("change", function () {
      $("td input, .email-list .custom-checkbox input").prop(
        "checked",
        $(this).prop("checked")
      );
    });
    $(".checkAll").on("click", function () {
      jQuery(this)
        .closest(".ItemsCheckboxSec")
        .find('input[type="checkbox"]')
        .prop("checked", $(this).prop("checked"));
    });
    $(".checkAllInput").on("click", function () {
      jQuery(this)
        .closest(".ItemsCheckboxSec")
        .find('input[type="checkbox"]')
        .prop("checked", true);
    });
    $(".unCheckAllInput").on("click", function () {
      jQuery(this)
        .closest(".ItemsCheckboxSec")
        .find('input[type="checkbox"]')
        .prop("checked", false);
    });
  };

  var handleNavigation = function () {
    $(".nav-control").on("click", function () {
      $("#main-wrapper").toggleClass("menu-toggle");
      $(".hamburger").toggleClass("is-active");
      handleMinHeight();
    });
  };

  var handleCurrentActive = function () {
    for (
      var nk = window.location,
        o = $("ul#menu a")
          .filter(function () {
            return this.href == nk;
          })
          .addClass("mm-active")
          .parent()
          .addClass("mm-active");
      ;

    ) {
      if (!o.is("li")) break;

      o = o.parent().addClass("mm-show").parent().addClass("mm-active");
    }
  };

  var handleMiniSidebar = function () {
    $("ul#menu>li").on("click", function () {
      const sidebarStyle = $("body").attr("data-sidebar-style");
      if (sidebarStyle === "mini") {
        console.log($(this).find("ul"));
        $(this).find("ul").stop();
      }
    });
  };

  var handleMinHeight = function () {
    var win_h = window.innerHeight;
    var win_h = window.innerHeight;
    if (win_h > 0 ? win_h : screen.height) {
      $(".content-body").css("min-height", window.innerHeight - 70 + "px");
      setTimeout(() => {
        if ($("body").attr("data-layout") === "vertical") {
          if (
            ($("body").attr("data-sidebar-style") === "mini" &&
              $(".deznav .metismenu").height() > window.innerHeight - 60) ||
            ($("body").attr("data-sidebar-style") === "modern" &&
              $(".deznav .metismenu").height() > window.innerHeight - 60) ||
            ($("body").attr("data-sidebar-style") === "full" &&
              $("#main-wrapper").hasClass("menu-toggle") &&
              $(".deznav .metismenu").height() > window.innerHeight - 60)
          ) {
            $(".content-body").css(
              "min-height",
              $(".deznav .metismenu").height() + 110 + "px"
            );
          }
        }
      }, 500);
    }

    setTimeout(() => {
      if (
        $("body").attr("data-header-position") === "fixed" &&
        $("body").attr("data-layout") === "horizontal" &&
        $("body").attr("data-sidebar-position") === "fixed"
      ) {
        $(".content-body").css(
          "padding-top",
          $(".deznav").height() + $(".header").height() + "px"
        );
      } else if (
        $("body").attr("data-header-position") === "fixed" &&
        $("body").attr("data-layout") === "horizontal" &&
        $("body").attr("data-sidebar-position") === "static"
      ) {
        $(".content-body").css("padding-top", $(".header").height() + "px");
      } else if (
        $("body").attr("data-header-position") === "static" &&
        $("body").attr("data-layout") === "horizontal" &&
        $("body").attr("data-sidebar-position") === "fixed"
      ) {
        $(".content-body").css("padding-top", "0px");
      } else {
        $(".content-body").css("padding-top", "");
      }
    }, 400);
  };

  var handleDataAction = function () {
    $('a[data-action="collapse"]').on("click", function (i) {
      i.preventDefault(),
        $(this)
          .closest(".card")
          .find('[data-action="collapse"] i')
          .toggleClass("mdi-arrow-down mdi-arrow-up"),
        $(this).closest(".card").children(".card-body").collapse("toggle");
    });

    $('a[data-action="expand"]').on("click", function (i) {
      i.preventDefault(),
        $(this)
          .closest(".card")
          .find('[data-action="expand"] i')
          .toggleClass("icon-size-actual icon-size-fullscreen"),
        $(this).closest(".card").toggleClass("card-fullscreen");
    });

    $('[data-action="close"]').on("click", function () {
      $(this).closest(".card").removeClass().slideUp("fast");
    });

    $('[data-action="reload"]').on("click", function () {
      var e = $(this);
      e.parents(".card").addClass("card-load"),
        e
          .parents(".card")
          .append(
            '<div class="card-loader"><i class=" ti-reload rotate-refresh"></div>'
          ),
        setTimeout(function () {
          e.parents(".card").children(".card-loader").remove(),
            e.parents(".card").removeClass("card-load");
        }, 2000);
    });
  };

  var handleHeaderHight = function () {
    const headerHight = $(".header").innerHeight();
    $(window).scroll(function () {
      if (
        $("body").attr("data-layout") === "horizontal" &&
        $("body").attr("data-header-position") === "static" &&
        $("body").attr("data-sidebar-position") === "fixed"
      )
        $(this.window).scrollTop() >= headerHight
          ? $(".deznav").addClass("fixed")
          : $(".deznav").removeClass("fixed");
    });
  };

  var handleMenuTabs = function () {
    if (screenWidth <= 991) {
      jQuery(".menu-tabs .nav-link").on("click", function () {
        if (jQuery(this).hasClass("open")) {
          jQuery(this).removeClass("open");
          jQuery(".fixed-content-box").removeClass("active");
          jQuery(".hamburger").show();
        } else {
          jQuery(".menu-tabs .nav-link").removeClass("open");
          jQuery(this).addClass("open");
          jQuery(".fixed-content-box").addClass("active");
          jQuery(".hamburger").hide();
        }
      });
      jQuery(".close-fixed-content").on("click", function () {
        jQuery(".fixed-content-box").removeClass("active");
        jQuery(".hamburger").removeClass("is-active");
        jQuery("#main-wrapper").removeClass("menu-toggle");
        jQuery(".hamburger").show();
      });
    }
  };
  /* Header Fixed ============ */
  var headerFix = function () {
    "use strict";
    /* Main navigation fixed on top  when scroll down function custom */
    jQuery(window).on("scroll", function () {
      if (jQuery(".header").length > 0) {
        var menu = jQuery(".header");
        $(window).scroll(function () {
          var sticky = $(".header"),
            scroll = $(window).scrollTop();

          if (scroll >= 100) {
            sticky.addClass("is-fixed");
          } else {
            sticky.removeClass("is-fixed");
          }
        });
      }
    });
    /* Main navigation fixed on top  when scroll down function custom end*/
  };

  var handleChatbox = function () {
    jQuery(".bell-link").on("click", function () {
      jQuery(".chatbox").addClass("active");
    });
    jQuery(".chatbox-close").on("click", function () {
      jQuery(".chatbox").removeClass("active");
    });
  };

  var handlePerfectScrollbar = function () {
    if (jQuery(".deznav-scroll").length > 0) {
      //const qs = new PerfectScrollbar('.deznav-scroll');
      /* const qs = new PerfectScrollbar('.deznav-scroll');
			
			qs.isRtl = false; */
    }
  };

  var handleBtnNumber = function () {
    $(".btn-number").on("click", function (e) {
      e.preventDefault();

      fieldName = $(this).attr("data-field");
      type = $(this).attr("data-type");
      var input = $("input[name='" + fieldName + "']");
      var currentVal = parseInt(input.val());
      if (!isNaN(currentVal)) {
        if (type == "minus") input.val(currentVal - 1);
        else if (type == "plus") input.val(currentVal + 1);
      } else {
        input.val(0);
      }
    });
  };

  var handleDzChatUser = function () {
    jQuery(".dz-chat-user-box .dz-chat-user").on("click", function () {
      jQuery(".dz-chat-user-box").addClass("hidden");
      jQuery(".dz-chat-history-box").removeClass("hidden");
    });

    jQuery(".dz-chat-history-back").on("click", function () {
      jQuery(".dz-chat-user-box").removeClass("hidden");
      jQuery(".dz-chat-history-box").addClass("hidden");
    });

    jQuery(".dz-fullscreen").on("click", function () {
      jQuery(".dz-fullscreen").toggleClass("active");
    });
  };

  var handleDzFullScreen = function () {
    jQuery(".dz-fullscreen").on("click", function (e) {
      if (
        document.fullscreenElement ||
        document.webkitFullscreenElement ||
        document.mozFullScreenElement ||
        document.msFullscreenElement
      ) {
        /* Enter fullscreen */
        if (document.exitFullscreen) {
          document.exitFullscreen();
        } else if (document.msExitFullscreen) {
          document.msExitFullscreen(); /* IE/Edge */
        } else if (document.mozCancelFullScreen) {
          document.mozCancelFullScreen(); /* Firefox */
        } else if (document.webkitExitFullscreen) {
          document.webkitExitFullscreen(); /* Chrome, Safari & Opera */
        }
      } else {
        /* exit fullscreen */
        if (document.documentElement.requestFullscreen) {
          document.documentElement.requestFullscreen();
        } else if (document.documentElement.webkitRequestFullscreen) {
          document.documentElement.webkitRequestFullscreen();
        } else if (document.documentElement.mozRequestFullScreen) {
          document.documentElement.mozRequestFullScreen();
        } else if (document.documentElement.msRequestFullscreen) {
          document.documentElement.msRequestFullscreen();
        }
      }
    });
  };

  var handleshowPass = function () {
    jQuery(".show-pass").on("click", function () {
      jQuery(this).toggleClass("active");
      if (jQuery("#dz-password").attr("type") == "password") {
        jQuery("#dz-password").attr("type", "text");
      } else if (jQuery("#dz-password").attr("type") == "text") {
        jQuery("#dz-password").attr("type", "password");
      }
    });
  };

  var heartBlast = function () {
    $(".heart").on("click", function () {
      $(this).toggleClass("heart-blast");
    });
  };
  var removeDiv = function () {
    $(".remove-btn").on("click", function () {
      $(this).parent().remove();
    });
  };

  var imgSelect = function () {
    // Get the background image of the selected option
    var backgroundImage = $(".img-select")
      .find("option:selected")
      .css("background-image");

    // Apply the same background image to the currently selected option
    $(".img-select").find(".current").css("background-image", backgroundImage);
  };

  var handleDzLoadMore = function () {
    $(".dz-load-more").on("click", function (e) {
      e.preventDefault(); //STOP default action
      $(this).append(' <i class="fas fa-sync"></i>');

      var dzLoadMoreUrl = $(this).attr("rel");
      var dzLoadMoreId = $(this).attr("id");

      $.ajax({
        method: "POST",
        url: dzLoadMoreUrl,
        dataType: "html",
        success: function (data) {
          $("#" + dzLoadMoreId + "Content").append(data);
          $(".dz-load-more i").remove();
        },
      });
    });
  };

  var handleLightgallery = function () {
    if (jQuery("#lightgallery").length > 0) {
      $("#lightgallery").lightGallery({
        loop: true,
        thumbnail: true,
        exThumbImage: "data-exthumbimage",
      });
    }
  };
  var handleCustomFileInput = function () {
    $(".custom-file-input").on("change", function () {
      var fileName = $(this).val().split("\\").pop();
      $(this)
        .siblings(".custom-file-label")
        .addClass("selected")
        .html(fileName);
    });
  };

  var vHeight = function () {
    var ch = $(window).height() - 206;
    $(".chatbox .msg_card_body").css("height", ch);
  };

  var handleCkEditor = function () {
    if (jQuery("#ckeditor").length > 0) {
      ClassicEditor.create(document.querySelector("#ckeditor"), {
        simpleUpload: {
          uploadUrl: "ckeditor-upload.php",
        },
      })
        .then((editor) => {
          window.editor = editor;
        })
        .catch((err) => {
          console.error(err.stack);
        });
    }
  };

  var handleMenuPosition = function () {
    if (screenWidth > 1024) {
      $(".metismenu  li")
        .unbind()
        .each(function (e) {
          if ($("ul", this).length > 0) {
            var elm = $("ul:first", this).css("display", "block");
            var off = elm.offset();
            var l = off.left;
            var w = elm.width();
            var elm = $("ul:first", this).removeAttr("style");
            var docH = $("body").height();
            var docW = $("body").width();

            if (jQuery("html").hasClass("rtl")) {
              var isEntirelyVisible = l + w <= docW;
            } else {
              var isEntirelyVisible = l > 0 ? true : false;
            }

            if (!isEntirelyVisible) {
              $(this).find("ul:first").addClass("left");
            } else {
              $(this).find("ul:first").removeClass("left");
            }
          }
        });
    }
  };

  var handleChartSidebar = function () {
    $(".chat-rightarea-btn").on("click", function () {
      $(this).toggleClass("active");
      $(".chat-right-area").toggleClass("active");
    });
    $(".chat-hamburger").on("click", function () {
      $(".chat-left-area").toggleClass("active");
    });
  };

  var MagnificPopup = function () {
    "use strict";
    if ($(".popup-youtube, .popup-vimeo, .popup-gmaps").length > 0) {
      /* magnificPopup for paly video function end*/
      $(".popup-youtube, .popup-vimeo, .popup-gmaps").magnificPopup({
        disableOn: 700,
        type: "iframe",
        mainClass: "mfp-fade",
        removalDelay: 160,
        preloader: false,

        fixedContentPos: false,
      });
    }
  };

  var handleDraggableCard = function () {
    var dzCardDraggable = (function () {
      return {
        //main function to initiate the module
        init: function () {
          var containers = document.querySelectorAll(".draggable-zone");

          if (containers.length === 0) {
            return false;
          }

          var swappable = new Sortable.default(containers, {
            draggable: ".draggable",
            handle: ".draggable.draggable-handle",
            mirror: {
              appendTo: "body",
              constrainDimensions: true,
            },
          });
          swappable.on("drag:stop", () => {
            setTimeout(function () {
              setBoxCount();
            }, 200);
          });
        },
      };
    })();

    jQuery(document).ready(function () {
      dzCardDraggable.init();
    });

    function setBoxCount() {
      var cardCount = 0;
      jQuery(".dropzoneContainer").each(function () {
        cardCount = jQuery(this).find(".draggable-handle").length;
        jQuery(this).find(".totalCount").html(cardCount);
      });
    }
  };

  /* Masonry Box ============ */
  var masonryBox = function () {
    "use strict";
    /* masonry by  = bootstrap-select.min.js */
    if (jQuery("#masonry, .masonry").length > 0) {
      setTimeout(function () {
        jQuery(".filters li").removeClass("active");
        jQuery(".filters li:first").addClass("active");
        var self = jQuery("#masonry, .masonry");
        var filterValue = $(".filters li:first").attr("data-filter");

        function handleIsotope(filterValue) {
          self.isotope({
            filter: filterValue,
          });
        }

        if (jQuery(".filters").length) {
          jQuery(".filters li:first").addClass("active");

          filterValue = $(".filters li:first").attr("data-filter");

          handleIsotope(filterValue);

          jQuery(".filters").on("click", "li", function () {
            jQuery(".filters li").removeClass("active");
            jQuery(this).addClass("active");

            filterValue = $(this).attr("data-filter");
            handleIsotope(filterValue);
          });
        }
      }, 500);
    }
    /* masonry by = bootstrap-select.min.js end */
  };

  var handleConverterTheme = function () {
    if ($(".btc-converts").length > 0) {
      setTimeout(() => {
        if ($("body").attr("data-theme-version") === "dark") {
          $(".btc-converts").attr("dark-mode", true);
        }
      }, 1000);
      $("#theme_version").on("change", function () {
        if ($("body").attr("data-theme-version") === "dark") {
          $(".btc-converts").attr("dark-mode", true);
        } else {
          $(".btc-converts").attr("dark-mode", false);
        }
      });
    }
  };
  var handleThemeMode = function () {
    if (jQuery(".dz-theme-mode").length > 0) {
      jQuery(".dz-theme-mode").on("click", function () {
        jQuery(this).toggleClass("active");

        if (jQuery(this).hasClass("active")) {
          jQuery("body").attr("data-theme-version", "dark");
          setCookie("version", "dark");
        } else {
          jQuery("body").attr("data-theme-version", "light");
          setCookie("version", "light");
        }
      });
      var version = getCookie("version");
      if (version != null) {
        jQuery("body").attr("data-theme-version", version);
      }
      jQuery(".dz-theme-mode").removeClass("active");

      jQuery(window).on("resize", function () {
        var version = getCookie("version");
        if (version != null) {
          jQuery("body").attr("data-theme-version", version);
        }
      });

      setTimeout(function () {
        if (jQuery("body").attr("data-theme-version") === "dark") {
          jQuery(".dz-theme-mode").addClass("active");
        }
      }, 1600);
    }
  };
  /* Handle Page On Scroll ============ */
  var handlePageOnScroll = function (event) {
    "use strict";
    var headerHeight = parseInt($(".header").css("height"), 10);

    $(".navbar-nav .scroll").on("click", function (event) {
      event.preventDefault();

      jQuery(".navbar-nav .scroll").parent().removeClass("active");
      jQuery(this).parent().addClass("active");

      if (this.hash !== "") {
        var hash = this.hash;
        var seactionPosition = parseInt($(hash).offset().top, 10);
        var headerHeight = parseInt($(".header").css("height"), 10);

        var scrollTopPosition = seactionPosition - headerHeight;
        $("html, body").animate(
          {
            scrollTop: scrollTopPosition,
          },
          800,
          function () {}
        );
      }
    });

    pageOnScroll();
  };

  /* Page On Scroll ============ */
  var pageOnScroll = function (event) {
    if (jQuery(".navbar-nav").length > 0) {
      var headerHeight = parseInt(jQuery(".header").height(), 10);

      jQuery(document).on("scroll", function () {
        var scrollPos = jQuery(this).scrollTop();
        jQuery(".navbar-nav .scroll").each(function () {
          var elementLink = jQuery(this);

          var refElement = jQuery(elementLink.attr("href"));

          if (jQuery(this.hash).offset() != undefined) {
            var seactionPosition = parseInt(jQuery(this.hash).offset().top, 10);
          } else {
            var seactionPosition = 0;
          }
          var scrollTopPosition = seactionPosition - headerHeight;

          if (scrollTopPosition <= scrollPos) {
            elementLink.parent().addClass("active");
            elementLink.parent().siblings().removeClass("active");
          }
        });
      });
    }
  };

  var tagify = function () {
    if (jQuery("input[name=tagify]").length > 0) {
      // The DOM element you wish to replace with Tagify
      var input = document.querySelector("input[name=tagify]");

      // initialize Tagify on the above input node reference
      new Tagify(input);
    }
  };

  var handleDropDownMenu = function () {
    jQuery(".dz-dropdown").on("click", function () {
      if (
        jQuery("#" + jQuery(this).attr("data-dz-dropdown")).hasClass("hidden")
      ) {
        jQuery(".dz-dropdown-menu").addClass("hidden");
        jQuery("#" + jQuery(this).attr("data-dz-dropdown")).removeClass(
          "hidden"
        );
      } else {
        jQuery("#" + jQuery(this).attr("data-dz-dropdown")).addClass("hidden");
      }
    });
    $(".dropdown-item").click(function () {
      $(".dz-dropdown-menu").addClass("hidden");
    });
    $(document).on("click", function (e) {
      if (
        !(
          $(e.target).closest(".dz-dropdown-menu").length > 0 ||
          $(e.target).closest(".dz-dropdown").length > 0
        )
      ) {
        $(".dz-dropdown-menu").addClass("hidden");
      }
    });
  };

  var handlepopovers = function () {
    jQuery(".dz-popover").on("click", function () {
      if (jQuery("#" + jQuery(this).attr("data-dz-popover")).hasClass("hide")) {
        jQuery(".dz-popover-content").fadeOut().addClass("hide");
        jQuery("#" + jQuery(this).attr("data-dz-popover"))
          .fadeIn()
          .removeClass("hide");
      } else {
        jQuery("#" + jQuery(this).attr("data-dz-popover"))
          .fadeOut()
          .addClass("hide");
      }
    });
    $(document).on("click", function (e) {
      if (
        !(
          $(e.target).closest(".dz-popover-content").length > 0 ||
          $(e.target).closest(".dz-popover").length > 0
        )
      ) {
        $(".dz-popover-content").fadeOut().addClass("hide");
      }
    });
  };

  var handleModalOLD = function () {
    jQuery(".dz-modal-btn").on("click", function () {
      if (
        jQuery("#" + jQuery(this).attr("data-dz-modal")).hasClass("model-close")
      ) {
        jQuery(".dz-modal-box").fadeOut("fast").addClass("model-close");
        jQuery("#" + jQuery(this).attr("data-dz-modal")).fadeIn(
          "fast",
          function () {
            $(this).removeClass("model-close");
          }
        );
      } else {
        jQuery("#" + jQuery(this).attr("data-dz-modal"))
          .fadeOut("fast")
          .addClass("model-close");
      }

      // $(document.body).append('<div class="modal-backdrop"></div>');
      $(".dz-modal-box").fadeOut();

      jQuery(".modal-backdrop").on("click", function () {
        $(".dz-modal-box").fadeOut("fast").addClass("model-close");
        $(this).remove();
      });
    });

    // Btn Close
    jQuery(".btn-close , .close-btn ,.save-btn").on("click", function () {
      $(".dz-modal-box").fadeOut().addClass("model-close");
      $(".modal-backdrop").remove();
    });
  };

  var handleModal = function () {
    // OPEN MODAL
    jQuery(".dz-modal-btn").on("click", function () {
      const modalId = jQuery(this).attr("data-dz-modal");
      const modal = jQuery("#" + modalId);

      // Close all other modals first
      jQuery(".dz-modal-box").fadeOut("fast").addClass("model-close");

      // Open selected modal
      modal.fadeIn("fast").removeClass("model-close");

      // Remove existing backdrop (prevent duplicates)
      jQuery(".modal-backdrop").remove();

      // Add new backdrop
      jQuery("body").append('<div class="modal-backdrop"></div>');

      // CLICK BACKDROP TO CLOSE
      jQuery(".modal-backdrop").on("click", function () {
        modal.fadeOut("fast").addClass("model-close");
        jQuery(this).remove();
      });
    });

    // CLOSE BUTTONS
    jQuery(".btn-close, .close-btn").on("click", function () {
      const modal = jQuery(this).closest(".dz-modal-box");

      modal.fadeOut("fast").addClass("model-close");
      jQuery(".modal-backdrop").remove();
    });
  };

  var handleOffcanvas = function () {
    $(".offcanvas-toggle").click(function () {
      if ($("#" + $(this).attr("data-dz-offcanvas")).hasClass("is-closed")) {
        $(".offcanvas").addClass("is-closed");
        $("#" + $(this).attr("data-dz-offcanvas")).removeClass("is-closed");
      } else {
        $("#" + $(this).attr("data-dz-offcanvas")).addClass("is-closed");
      }
      $(document.body).append('<div class="offcanvas-backdrop"></div>');
      $(".offcanvas-backdrop").on("click", function () {
        $(this).remove();
        $(".offcanvas").addClass("is-closed");
      });
    });

    $(".offcanvas-close").click(function () {
      $(".offcanvas").addClass("is-closed");
      $(".offcanvas-backdrop").remove();
    });
  };
  
  var handleCardCollapse = function () {
    $(".card-toggle-btn").click(function () {
      $(this).toggleClass("active");
      $(this).parent().find(".arrow").toggleClass("arrow-animate");
      $(this).parent().find(".content").slideToggle(280);
    });
  };

  var handleAccordion = function () {
    $(".accordion-btn").on("click", function () {
      $(this)
        .closest(".accordion-wrapper")
        .find(".accordion-content")
        .slideUp("hide");
      if ($(this).hasClass("active")) {
        $(this)
          .closest(".accordion-wrapper")
          .find(".accordion-btn")
          .removeClass("active");
        $(this).removeClass("active");
        var contentSelector = $(this).attr("data-dz-item");
        $("#" + contentSelector).slideUp("hide");
      } else {
        $(this)
          .closest(".accordion-wrapper")
          .find(".accordion-btn")
          .removeClass("active");
        $(this).addClass("active");
        var contentSelector = $(this).attr("data-dz-item");
        $("#" + contentSelector).slideDown("hide");
      }
    });
  };

  var handleTab = function () {
    $(".tab-btn").on("click", function () {
      var tab_id = $(this).attr("data-tab");
      $(this)
        .closest(".dz-tab-area")
        .closest_descendent(".nav-tabs,.nav-pills")
        .find(".tab-btn")
        .removeClass("active");
      $(this)
        .closest(".dz-tab-area")
        .closest_descendent(".tab-content-area")
        .children()
        .removeClass("show");
      $(this).addClass("active");
      $(this)
        .closest(".dz-tab-area")
        .find("#" + tab_id)
        .addClass("show");
    });
  };
  var handleHorizontalDropDown = function () {
    $(".metismenu li a").hover(
      function (event) {
        var $submenu = $(this).parent().children("ul");

        var $windowEdge = $("#main-wrapper").width();
        var $leftOffset = ($(window).width() - $("#main-wrapper").width()) / 2;
        var $menuRightEdge =
          $(this).offset().left +
          $(this).outerWidth() +
          $submenu.outerWidth() -
          $leftOffset;
        if ($menuRightEdge > $windowEdge) {
          if ($(this).parent().parent("ul.metismenu").length > 0) {
            $submenu.css({ left: "auto", right: "0" });
          } else {
            $submenu.css({ left: "auto", right: "100%" });
          }
        }
      },
      function () {
        $(this).children("ul").removeAttr("style");
      }
    );
  };

  var handleAlert = function () {
    jQuery(".alert .close-btn").on("click", function () {
      $(this).parent(".alert").remove();
    });
  };

  /* Function ============ */
  return {
    init: function () {
      handleMetisMenu();
      handleNiceSelect();
      handleAllChecked();
      handlesvgmodal();
      handleNavigation();
      handleCurrentActive();
      handleMiniSidebar();
      handleMinHeight();
      handleDataAction();
      handleHeaderHight();
      handleMenuTabs();
      handleChatbox();
      handlePerfectScrollbar();
      handleBtnNumber();
      handleDzChatUser();
      handleDzFullScreen();
      handleshowPass();
      heartBlast();
      removeDiv();
      imgSelect();
      handleDzLoadMore();
      handleLightgallery();
      handleCustomFileInput();
      vHeight();
      handleDatetimepicker();
      handleCkEditor();
      headerFix();
      handleChartSidebar();
      MagnificPopup();
      handleDraggableCard();
      handleConverterTheme();
      handleThemeMode();
      handlePageOnScroll();
      tagify();
      handleModal();
      handleDropDownMenu();
      handlepopovers();
      handleCardCollapse();
      handleOffcanvas();
      handleAccordion();
      handleTab();
      handleAlert();
      setTimeout(function () {
        handleHorizontalDropDown();
      }, 500);
    },

    load: function () {
      handlePreloader();
      masonryBox();
    },

    resize: function () {
      vHeight();
      handleMinHeight();
    },

    handleMenuPosition: function () {
      handleMenuPosition();
    },
  };
})();

(function ($) {
  $.fn.closest_descendent = function (filter) {
    var $found = $(),
      $currentSet = this; // Current place
    while ($currentSet.length) {
      $found = $currentSet.filter(filter);
      if ($found.length) break; // At least one match: break loop
      // Get all children of the current set
      $currentSet = $currentSet.children();
    }
    return $found.first(); // Return first match of the collection
  };
})(jQuery);

/* Document.ready Start */
jQuery(document).ready(function () {
  "use strict";
  W3Crm.init();
});
/* Document.ready END */

/* Window Load START */
jQuery(window).on("load", function () {
  "use strict";
  W3Crm.load();
  setTimeout(function () {
    W3Crm.handleMenuPosition();
  }, 1000);
});
/*  Window Load END */
/* Window Resize START */
jQuery(window).on("resize", function () {
  "use strict";
  W3Crm.resize();
  setTimeout(function () {
    W3Crm.handleMenuPosition();
  }, 1000);
});
/*  Window Resize END */
