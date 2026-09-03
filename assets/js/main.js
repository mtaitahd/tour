/**
 * Afro Vertex Tours - Admin JS
 * POS-style sidebar toggle + responsive behavior
 */
(function() {
  "use strict";

  /**
   * Sidebar toggle — POS-style (.sidebar.toggled + overlay)
   */
  var sidebar = document.getElementById('accordionSidebar') || document.querySelector('.sidebar');
  var overlay = document.getElementById('sidebarOverlay');
  var toggleBtn = document.getElementById('sidebarToggleTop');
  var wrapper = document.getElementById('wrapper');

  function syncSidebarState() {
    if (overlay) {
      if (sidebar.classList.contains('toggled')) {
        overlay.classList.add('active');
      } else {
        overlay.classList.remove('active');
      }
    }
    if (wrapper) {
      if (sidebar.classList.contains('toggled')) {
        wrapper.classList.add('sidebar-collapsed');
      } else {
        wrapper.classList.remove('sidebar-collapsed');
      }
    }
  }

  if (sidebar && toggleBtn) {
    toggleBtn.addEventListener('click', function(e) {
      e.preventDefault();
      sidebar.classList.toggle('toggled');
      syncSidebarState();
    });
  }

  // Also support NiceAdmin toggle-sidebar-btn class
  var niceAdminToggle = document.querySelector('.toggle-sidebar-btn');
  if (niceAdminToggle && niceAdminToggle !== toggleBtn) {
    niceAdminToggle.addEventListener('click', function(e) {
      e.preventDefault();
      if (sidebar) sidebar.classList.toggle('toggled');
      syncSidebarState();
    });
  }

  // Overlay click closes sidebar (mobile)
  if (overlay) {
    overlay.addEventListener('click', function() {
      if (sidebar) sidebar.classList.remove('toggled');
      syncSidebarState();
    });
  }

  // Close sidebar on mobile when clicking outside
  document.addEventListener('click', function(e) {
    if (window.innerWidth < 768 && sidebar && sidebar.classList.contains('toggled')) {
      if (!sidebar.contains(e.target) && (!toggleBtn || !toggleBtn.contains(e.target))) {
        sidebar.classList.remove('toggled');
        syncSidebarState();
      }
    }
  });

  // Auto-close sidebar after clicking a link on mobile
  document.querySelectorAll('.sidebar .nav-link').forEach(function(link) {
    link.addEventListener('click', function() {
      if (window.innerWidth < 768 && sidebar) {
        sidebar.classList.remove('toggled');
        syncSidebarState();
      }
    });
  });

  /**
   * Format money helper
   */
  window.formatMoney = function(num) {
    return parseInt(num).toLocaleString('en-US');
  };

  /**
   * Back to top button
   */
  var backtotop = document.querySelector('.back-to-top');
  if (backtotop) {
    window.addEventListener('scroll', function() {
      if (window.scrollY > 100) {
        backtotop.classList.add('active');
      } else {
        backtotop.classList.remove('active');
      }
    });
  }

  /**
   * Header scrolled class
   */
  var selectHeader = document.getElementById('topbar');
  if (selectHeader) {
    window.addEventListener('scroll', function() {
      if (window.scrollY > 100) {
        selectHeader.classList.add('header-scrolled');
      } else {
        selectHeader.classList.remove('header-scrolled');
      }
    });
  }

  /**
   * Initiate Bootstrap tooltips
   */
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function(tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  /**
   * Initiate Bootstrap validation
   */
  var needsValidation = document.querySelectorAll('.needs-validation');
  Array.prototype.slice.call(needsValidation).forEach(function(form) {
    form.addEventListener('submit', function(event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });

})();
