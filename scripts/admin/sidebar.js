$(document).ready(function () {
  // Toggle sidebar state and update icon
  function toggleSidebar(setActive) {
    const $sidebar = $("#sidebar");
    const $toggleIcon = $("#toggle-icon");
    const $container = $(".dashboard-container");
    const $footerContent = $(".footer .container");

    const isActive =
      setActive !== undefined ? setActive : !$sidebar.hasClass("active");

    if (isActive) {
      $sidebar.removeClass("collapsed").addClass("active");
      $toggleIcon.attr({
        src: "../assets/icons/chevron-left.svg",
        alt: "Collapse Sidebar Icon",
      });
      $container.removeClass("collapsed");
      $footerContent.addClass("with-sidebar");
      setCookie("sidebarState", "active", 30);
    } else {
      $sidebar.removeClass("active").addClass("collapsed");
      $toggleIcon.attr({
        src: "../assets/icons/chevron-right.svg",
        alt: "Expand Sidebar Icon",
      });
      $container.addClass("collapsed");
      $footerContent.removeClass("with-sidebar");
      setCookie("sidebarState", "collapsed", 30);
    }
  }

  // Initialize sidebar state
  function initializeSidebar() {
    const savedState = getCookie("sidebarState");
    const isMobile = $(window).width() <= 768;
    let shouldBeActive;

    if (savedState) {
      shouldBeActive = savedState === "active";
    } else {
      shouldBeActive = !isMobile; // Active on desktop, collapsed on mobile
    }

    toggleSidebar(shouldBeActive);
  }

  // Handle sidebar toggle click
  $("#sidebar-toggle").click(function () {
    toggleSidebar();
  });

  // Handle window resize
  $(window).resize(function () {
    const isMobile = $(window).width() <= 768;
    const $sidebar = $("#sidebar");
    const savedState = getCookie("sidebarState");

    // Only change state if no cookie or screen size changes expected state
    if (!savedState) {
      const shouldBeActive = !isMobile;
      if (shouldBeActive !== $sidebar.hasClass("active")) {
        toggleSidebar(shouldBeActive);
      }
    }
  });

  // Initialize sidebar on load
  initializeSidebar();
});
