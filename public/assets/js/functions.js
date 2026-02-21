/**
	Template Name 	 : MMS - Management System HTML Template
	Author			 : Eddy
	Author Portfolio : https://github.net/eddy/portfolio
	
	Core script to handle the entire theme and core functions
**/

var MMS = (function () {
  "use strict";

  /* Search Bar ============ */
  var screenWidth = $(window).width();
  var screenHeight = $(window).height();

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

  /**
   * Enhanced Modal Handler
   * Supports multiple instances, custom animations, callbacks, and more
   */
  var DzModalOLD = (function () {
    // Default configuration
    const defaults = {
      animation: "fade", // 'fade', 'slide', 'scale'
      speed: 300,
      closeOnBackdrop: true,
      closeOnEsc: true,
      backdrop: true,
      onOpen: null,
      onClose: null,
      beforeOpen: null,
      beforeClose: null,
    };

    let activeModals = [];
    let config = { ...defaults };

    /**
     * Initialize the modal handler
     * @param {Object} options - Configuration options
     */
    function init(options = {}) {
      config = { ...defaults, ...options };
      bindEvents();
      return this;
    }

    /**
     * Bind all modal events
     */
    function bindEvents() {
      // Open modal buttons
      jQuery(document).on("click", ".dz-modal-btn", function (e) {
        e.preventDefault();
        const modalId = jQuery(this).attr("data-dz-modal");
        const customConfig = getElementConfig(jQuery(this));
        open(modalId, customConfig);
      });

      // Close buttons
      jQuery(document).on("click", ".btn-close, .close-btn", function (e) {
        e.preventDefault();
        const modal = jQuery(this).closest(".dz-modal-box");
        close(modal.attr("id"));
      });

      // ESC key to close
      jQuery(document).on("keydown", function (e) {
        if (
          e.key === "Escape" &&
          config.closeOnEsc &&
          activeModals.length > 0
        ) {
          close(activeModals[activeModals.length - 1]);
        }
      });
    }

    /**
     * Get configuration from data attributes
     * @param {jQuery} element - The trigger element
     * 
     * @returns {Object} Configuration object
     */
    function getElementConfig(element) {
      return {
        animation: element.data("animation") || config.animation,
        speed: element.data("speed") || config.speed,
        closeOnBackdrop: element.data("close-backdrop") !== false,
        backdrop: element.data("backdrop") !== false,
      };
    }

    /**
     * Open a modal
     * @param {String} modalId - ID of the modal to open
     * @param {Object} options - Override options for this instance
     */
    function openModal(modalId, options = {}) {
      const modal = jQuery("#" + modalId);
      if (!modal.length) {
        console.warn("Modal not found:", modalId);
        return;
      }

      const modalConfig = { ...config, ...options };

      // Before open callback
      if (
        modalConfig.beforeOpen &&
        typeof modalConfig.beforeOpen === "function"
      ) {
        if (modalConfig.beforeOpen(modal) === false) return;
      }

      // Close other modals if not stacking
      if (!modalConfig.stackModals) {
        closeAll();
      }

      // Add to active modals
      if (!activeModals.includes(modalId)) {
        activeModals.push(modalId);
      }

      // Show backdrop
      if (modalConfig.backdrop) {
        showBackdrop(modalId, modalConfig);
      }

      // Add body class
      jQuery("body").addClass("modal-open");

      // Show modal with animation
      showModal(modal, modalConfig);

      // On open callback
      if (modalConfig.onOpen && typeof modalConfig.onOpen === "function") {
        setTimeout(() => modalConfig.onOpen(modal), modalConfig.speed);
      }
    }

    /**
     * Close a modal
     * @param {String} modalId - ID of the modal to close
     */
    function close(modalId) {
      const modal = jQuery("#" + modalId);
      if (!modal.length) return;

      // Before close callback
      if (config.beforeClose && typeof config.beforeClose === "function") {
        if (config.beforeClose(modal) === false) return;
      }

      const modalConfig = { ...config };

      // Hide modal with animation
      hideModal(modal, modalConfig);

      // Remove from active modals
      activeModals = activeModals.filter((id) => id !== modalId);

      // Remove backdrop
      jQuery(`.modal-backdrop[data-modal="${modalId}"]`).fadeOut(
        modalConfig.speed,
        function () {
          jQuery(this).remove();
        }
      );

      // Remove body class if no active modals
      if (activeModals.length === 0) {
        jQuery("body").removeClass("modal-open");
      }

      // On close callback
      if (config.onClose && typeof config.onClose === "function") {
        setTimeout(() => config.onClose(modal), modalConfig.speed);
      }
    }

    /**
     * Close all active modals
     */
    function closeAll() {
      [...activeModals].forEach((modalId) => close(modalId));
    }

    /**
     * Show backdrop
     */
    function showBackdrop(modalId, modalConfig) {
      const backdrop = jQuery(
        `<div class="modal-backdrop" data-modal="${modalId}"></div>`
      );
      jQuery("body").append(backdrop);
      backdrop.fadeIn(modalConfig.speed);

      // Click backdrop to close
      if (modalConfig.closeOnBackdrop) {
        backdrop.on("click", function () {
          close(modalId);
        });
      }
    }

    /**
     * Show modal with animation
     */
    function showModal(modal, modalConfig) {
      modal.removeClass("model-close");

      switch (modalConfig.animation) {
        case "slide":
          modal.css({
            display: "block",
            opacity: 0,
            transform: "translateY(-50px)",
          });
          modal.animate(
            { opacity: 1, transform: "translateY(0)" },
            modalConfig.speed
          );
          break;

        case "scale":
          modal.css({ display: "block", opacity: 0, transform: "scale(0.7)" });
          modal.animate(
            { opacity: 1, transform: "scale(1)" },
            modalConfig.speed
          );
          break;

        case "fade":
        default:
          modal.fadeIn(modalConfig.speed);
          break;
      }
    }

    /**
     * Hide modal with animation
     */
    function hideModal(modal, modalConfig) {
      switch (modalConfig.animation) {
        case "slide":
          modal.animate(
            { opacity: 0, transform: "translateY(-50px)" },
            modalConfig.speed,
            function () {
              jQuery(this).css("display", "none").addClass("model-close");
            }
          );
          break;

        case "scale":
          modal.animate(
            { opacity: 0, transform: "scale(0.7)" },
            modalConfig.speed,
            function () {
              jQuery(this).css("display", "none").addClass("model-close");
            }
          );
          break;

        case "fade":
        default:
          modal.fadeOut(modalConfig.speed, function () {
            jQuery(this).addClass("model-close");
          });
          break;
      }
    }

    /**
     * Toggle a modal
     */
    function toggle(modalId) {
      if (activeModals.includes(modalId)) {
        close(modalId);
      } else {
        openModal(modalId);
      }
    }

    /**
     * Check if modal is open
     */
    function isOpen(modalId) {
      return activeModals.includes(modalId);
    }

    // Public API
    return {
      init,
      openModal,
      close,
      closeAll,
      toggle,
      isOpen,
      setConfig: (options) => {
        config = { ...config, ...options };
      },
    };
  })();

  /**
   * Enhanced Modal Handler with Advanced Features
   * Supports multiple instances, animations, callbacks, confirm dialogs, loading states, and positioning
   */
  var DzModal = (function () {
    // Default configuration
    const defaults = {
      animation: "fade", // 'fade', 'slide', 'scale'
      speed: 300,
      closeOnBackdrop: true,
      closeOnEsc: true,
      backdrop: true,
      position: "center", // 'center', 'top', 'bottom', 'left', 'right'
      onOpen: null,
      onClose: null,
      beforeOpen: null,
      beforeClose: null,
      autoClose: null, // Auto close after X milliseconds
      stackModals: false,
    };

    let activeModals = [];
    let config = { ...defaults };
    let loadingModal = null;

    /**
     * Initialize the modal handler
     * @param {Object} options - Configuration options
     */
    function init(options = {}) {
      config = { ...defaults, ...options };
      bindEvents();
      createLoadingModal();
      return this;
    }

    /**
     * Bind all modal events
     */
    function bindEvents() {
      // Open modal buttons
      jQuery(document).on("click", ".dz-modal-btn", function (e) {
        e.preventDefault();
        const modalId = jQuery(this).attr("data-dz-modal");
        const customConfig = getElementConfig(jQuery(this));
        open(modalId, customConfig);
      });

      // Close buttons
      jQuery(document).on("click", ".btn-close, .close-btn", function (e) {
        e.preventDefault();
        const modal = jQuery(this).closest(".dz-modal-box");
        close(modal.attr("id"));
      });

      // Confirm buttons
      jQuery(document).on("click", ".dz-confirm-btn", function (e) {
        e.preventDefault();
        const options = {
          title: jQuery(this).data("title") || "Confirm Action",
          message: jQuery(this).data("message") || "Are you sure?",
          confirmText: jQuery(this).data("confirm-text") || "Confirm",
          cancelText: jQuery(this).data("cancel-text") || "Cancel",
          confirmClass: jQuery(this).data("confirm-class") || "btn-primary",
          onConfirm: jQuery(this).data("on-confirm"),
        };
        confirm(options);
      });

      // ESC key to close
      jQuery(document).on("keydown", function (e) {
        if (
          e.key === "Escape" &&
          config.closeOnEsc &&
          activeModals.length > 0
        ) {
          close(activeModals[activeModals.length - 1]);
        }
      });
    }

    /**
     * Get configuration from data attributes
     * @param {jQuery} element - The trigger element
     * @returns {Object} Configuration object
     */
    function getElementConfig(element) {
      return {
        animation: element.data("animation") || config.animation,
        speed: element.data("speed") || config.speed,
        closeOnBackdrop: element.data("close-backdrop") !== false,
        backdrop: element.data("backdrop") !== false,
        position: element.data("position") || config.position,
        autoClose: element.data("auto-close") || config.autoClose,
      };
    }

    /**
     * Open a modal
     * @param {String} modalId - ID of the modal to open
     * @param {Object} options - Override options for this instance
     */
    function open(modalId, options = {}) {
      const modal = jQuery("#" + modalId);
      if (!modal.length) {
        console.warn("Modal not found:", modalId);
        return;
      }

      const modalConfig = { ...config, ...options };

      // Before open callback
      if (
        modalConfig.beforeOpen &&
        typeof modalConfig.beforeOpen === "function"
      ) {
        if (modalConfig.beforeOpen(modal) === false) return;
      }

      // Close other modals if not stacking
      if (!modalConfig.stackModals) {
        closeAll();
      }

      // Add to active modals
      if (!activeModals.includes(modalId)) {
        activeModals.push(modalId);
      }

      // Show backdrop
      if (modalConfig.backdrop) {
        showBackdrop(modalId, modalConfig);
      }

      // Add body class
      jQuery("body").addClass("modal-open");

      // Apply positioning
      applyPosition(modal, modalConfig.position);

      // Show modal with animation
      showModal(modal, modalConfig);

      // Auto close
      if (modalConfig.autoClose) {
        setTimeout(() => close(modalId), modalConfig.autoClose);
      }

      // On open callback
      if (modalConfig.onOpen && typeof modalConfig.onOpen === "function") {
        setTimeout(() => modalConfig.onOpen(modal), modalConfig.speed);
      }
    }

    /**
     * Close a modal
     * @param {String} modalId - ID of the modal to close
     */
    function close(modalId) {
      const modal = jQuery("#" + modalId);
      if (!modal.length) return;

      // Before close callback
      if (config.beforeClose && typeof config.beforeClose === "function") {
        if (config.beforeClose(modal) === false) return;
      }

      const modalConfig = { ...config };

      // Hide modal with animation
      hideModal(modal, modalConfig);

      // Remove from active modals
      activeModals = activeModals.filter((id) => id !== modalId);

      // Remove backdrop
      jQuery(`.modal-backdrop[data-modal="${modalId}"]`).fadeOut(
        modalConfig.speed,
        function () {
          jQuery(this).remove();
        }
      );

      // Remove body class if no active modals
      if (activeModals.length === 0) {
        jQuery("body").removeClass("modal-open");
      }

      // On close callback
      if (config.onClose && typeof config.onClose === "function") {
        setTimeout(() => config.onClose(modal), modalConfig.speed);
      }
    }

    /**
     * Close all active modals
     */
    function closeAll() {
      [...activeModals].forEach((modalId) => close(modalId));
    }

    /**
     * Show backdrop
     */
    function showBackdrop(modalId, modalConfig) {
      const backdrop = jQuery(
        `<div class="modal-backdrop" data-modal="${modalId}"></div>`
      );
      jQuery("body").append(backdrop);
      backdrop.fadeIn(modalConfig.speed);

      // Click backdrop to close
      if (modalConfig.closeOnBackdrop) {
        backdrop.on("click", function () {
          close(modalId);
        });
      }
    }

    /**
     * Apply positioning to modal
     */
    function applyPosition(modal, position) {
      // Remove all position classes
      modal.removeClass(
        "modal-center modal-top modal-bottom modal-left modal-right"
      );

      // Add position class
      modal.addClass(`modal-${position}`);
    }

    /**
     * Show modal with animation
     */
    function showModal(modal, modalConfig) {
      modal.removeClass("model-close");

      switch (modalConfig.animation) {
        case "slide":
          modal.css({
            display: "block",
            opacity: 0,
            transform: "translateY(-50px)",
          });
          modal.animate(
            { opacity: 1, transform: "translateY(0)" },
            modalConfig.speed
          );
          break;

        case "scale":
          modal.css({ display: "block", opacity: 0, transform: "scale(0.7)" });
          modal.animate(
            { opacity: 1, transform: "scale(1)" },
            modalConfig.speed
          );
          break;

        case "fade":
        default:
          modal.fadeIn(modalConfig.speed);
          break;
      }
    }

    /**
     * Hide modal with animation
     */
    function hideModal(modal, modalConfig) {
      switch (modalConfig.animation) {
        case "slide":
          modal.animate(
            { opacity: 0, transform: "translateY(-50px)" },
            modalConfig.speed,
            function () {
              jQuery(this).css("display", "none").addClass("model-close");
            }
          );
          break;

        case "scale":
          modal.animate(
            { opacity: 0, transform: "scale(0.7)" },
            modalConfig.speed,
            function () {
              jQuery(this).css("display", "none").addClass("model-close");
            }
          );
          break;

        case "fade":
        default:
          modal.fadeOut(modalConfig.speed, function () {
            jQuery(this).addClass("model-close");
          });
          break;
      }
    }

    /**
     * Toggle a modal
     */
    function toggle(modalId) {
      if (activeModals.includes(modalId)) {
        close(modalId);
      } else {
        open(modalId);
      }
    }

    /**
     * Check if modal is open
     */
    function isOpen(modalId) {
      return activeModals.includes(modalId);
    }

    /**
     * Create loading modal
     */
    function createLoadingModal() {
      const loadingHTML = `
      <div id="dz-loading-modal" class="dz-modal-box modal-center" style="display: none;">
        <div class="modal-content loading-modal">
          <div class="loading-spinner"></div>
          <div class="loading-text">Loading...</div>
        </div>
      </div>
    `;

      // Add loading modal if not exists
      if (!jQuery("#dz-loading-modal").length) {
        jQuery("body").append(loadingHTML);
      }
    }

    /**
     * Show loading state
     * @param {String} message - Loading message
     * @param {Object} options - Loading options
     */
    function showLoading(message = "Loading...", options = {}) {
      const loadingConfig = {
        backdrop: true,
        closeOnBackdrop: false,
        closeOnEsc: false,
        animation: "fade",
        ...options,
      };

      const modal = jQuery("#dz-loading-modal");
      modal.find(".loading-text").text(message);

      open("dz-loading-modal", loadingConfig);

      return "dz-loading-modal";
    }

    /**
     * Hide loading state
     */
    function hideLoading() {
      close("dz-loading-modal");
    }

    /**
     * Show confirm dialog
     * @param {Object} options - Confirm dialog options
     */
    function confirm(options = {}) {
      const confirmConfig = {
        title: "Confirm Action",
        message: "Are you sure you want to proceed?",
        confirmText: "Confirm",
        cancelText: "Cancel",
        confirmClass: "btn-primary",
        cancelClass: "btn-secondary",
        onConfirm: null,
        onCancel: null,
        ...options,
      };

      const confirmId = "dz-confirm-modal-" + Date.now();

      const confirmHTML = `
      <div id="${confirmId}" class="dz-modal-box modal-center" style="display: none;">
        <div class="modal-content confirm-modal">
          <div class="modal-header">
            <h3 class="modal-title">${confirmConfig.title}</h3>
            <button type="button" class="btn-close"></button>
          </div>
          <div class="modal-body">
            <p>${confirmConfig.message}</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="confirm-cancel ${confirmConfig.cancelClass}">
              ${confirmConfig.cancelText}
            </button>
            <button type="button" class="confirm-ok ${confirmConfig.confirmClass}">
              ${confirmConfig.confirmText}
            </button>
          </div>
        </div>
      </div>
    `;

      // Add confirm modal
      jQuery("body").append(confirmHTML);
      const modal = jQuery("#" + confirmId);

      // Open modal
      open(confirmId, {
        closeOnBackdrop: false,
        closeOnEsc: true,
        animation: "scale",
      });

      // Confirm button
      modal.find(".confirm-ok").on("click", function () {
        if (
          confirmConfig.onConfirm &&
          typeof confirmConfig.onConfirm === "function"
        ) {
          confirmConfig.onConfirm();
        }
        close(confirmId);
        setTimeout(() => modal.remove(), config.speed + 100);
      });

      // Cancel button
      modal.find(".confirm-cancel, .btn-close").on("click", function () {
        if (
          confirmConfig.onCancel &&
          typeof confirmConfig.onCancel === "function"
        ) {
          confirmConfig.onCancel();
        }
        close(confirmId);
        setTimeout(() => modal.remove(), config.speed + 100);
      });
    }

    /**
     * Show alert dialog
     * @param {String} message - Alert message
     * @param {Object} options - Alert options
     */
    function alert(message, options = {}) {
      const alertConfig = {
        title: "Alert",
        type: "info", // 'info', 'success', 'warning', 'error'
        okText: "OK",
        okClass: "btn-primary",
        onOk: null,
        ...options,
      };

      const alertId = "dz-alert-modal-" + Date.now();
      const iconMap = {
        info: "ℹ️",
        success: "✅",
        warning: "⚠️",
        error: "❌",
      };

      const alertHTML = `
      <div id="${alertId}" class="dz-modal-box modal-center" style="display: none;">
        <div class="modal-content alert-modal alert-${alertConfig.type}">
          <div class="modal-header">
            <h3 class="modal-title">
              <span class="alert-icon">${iconMap[alertConfig.type]}</span>
              ${alertConfig.title}
            </h3>
            <button type="button" class="btn-close"></button>
          </div>
          <div class="modal-body">
            <p>${message}</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="alert-ok ${alertConfig.okClass}">
              ${alertConfig.okText}
            </button>
          </div>
        </div>
      </div>
    `;

      // Add alert modal
      jQuery("body").append(alertHTML);
      const modal = jQuery("#" + alertId);

      // Open modal
      open(alertId, {
        closeOnBackdrop: true,
        closeOnEsc: true,
        animation: "scale",
      });

      // OK button
      modal.find(".alert-ok, .btn-close").on("click", function () {
        if (alertConfig.onOk && typeof alertConfig.onOk === "function") {
          alertConfig.onOk();
        }
        close(alertId);
        setTimeout(() => modal.remove(), config.speed + 100);
      });
    }

    /**
     * Update modal content dynamically
     * @param {String} modalId - Modal ID
     * @param {String} content - New content
     * @param {String} selector - Optional selector within modal
     */
    function updateContent(modalId, content, selector = ".modal-body") {
      const modal = jQuery("#" + modalId);
      if (modal.length) {
        modal.find(selector).html(content);
      }
    }

    /**
     * Set modal size
     * @param {String} modalId - Modal ID
     * @param {String} size - Size ('small', 'medium', 'large', 'full')
     */
    function setSize(modalId, size) {
      const modal = jQuery("#" + modalId);
      modal.removeClass("modal-small modal-medium modal-large modal-full");
      modal.addClass(`modal-${size}`);
    }

    // Public API
    return {
      init,
      open,
      close,
      closeAll,
      toggle,
      isOpen,
      confirm,
      alert,
      showLoading,
      hideLoading,
      updateContent,
      setSize,
      setConfig: (options) => {
        config = { ...config, ...options };
      },
      getConfig: () => ({ ...config }),
      getActiveModals: () => [...activeModals],
    };
  })();


  return {
    init: function () {
      // Initialization code here
      DzModal.init({
        animation: "fade",
        speed: 300,
        closeOnBackdrop: true,
        closeOnEsc: true,
      });
      //   handlePreloader();
    },
    handleModal: function () {
      DzModal.init();
    },
    load: function () {
      // Load handling code here
    },
    resize: function () {
      // Resize handling code here
    },
    handleMenuPosition: function () {
      // Menu position handling code here
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
  MMS.init();
});

/* Window Load START */
jQuery(window).on("load", function () {
  "use strict";
  //   MMS.load();
  //   setTimeout(function () {
  //     MMS.handleMenuPosition();
  //   }, 1000);
});

/* Window Resize START */
jQuery(window).on("resize", function () {
  "use strict";
  //   MMS.resize();
  //   setTimeout(function () {
  //     MMS.handleMenuPosition();
  //   }, 1000);
});


