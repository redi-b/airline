$(document).ready(function () {
  // Initialize accordion
  $(".accordion-header").on("click", function (e) {
    const $header = $(this);
    const $body = $header.next(".accordion-body");
    const $card = $header.closest(".accordion");

    // Toggle active class
    $body.slideToggle(300);
    $card.toggleClass("active");
  });

  // Prevent accordion toggle on button clicks
  $(".header-actions").on("click", function (e) {
    e.stopPropagation();
  });
});
