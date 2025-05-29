$(document).ready(function () {
  $(".navbar-toggle").on("click", function () {
    $(this).toggleClass("active");
    $(".navbar-links").toggleClass("active");
  });

  // Close menu when clicking a link on mobile
  $(".navbar-link").on("click", function () {
    if ($(window).width() <= 768) {
      $(".navbar-toggle").removeClass("active");
      $(".navbar-links").removeClass("active");
    }
  });
});
