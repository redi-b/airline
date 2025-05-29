$(document).ready(function () {
  $(".toast").each(function () {
    const $toast = $(this);
    const totalDuration = 5000;
    let startTime = Date.now();
    let remainingTime = totalDuration;
    let dismissTimeout;

    const dismissToast = () => {
      $toast.addClass("fade-out");
      setTimeout(() => $toast.remove(), 300);
    };

    const startDismissTimer = () => {
      startTime = Date.now();
      dismissTimeout = setTimeout(dismissToast, remainingTime);
    };

    const pauseDismissTimer = () => {
      clearTimeout(dismissTimeout);
      const elapsed = Date.now() - startTime;
      remainingTime -= elapsed;
    };

    // Start auto-dismiss
    startDismissTimer();

    // Pause on hover
    $toast.on("mouseenter", pauseDismissTimer);

    // Resume on mouse leave
    $toast.on("mouseleave", startDismissTimer);
  });

  // Handle close button click
  $(".toast .close-btn").on("click", function () {
    const $toast = $(this).closest(".toast");
    $toast.addClass("fade-out");
    setTimeout(() => $toast.remove(), 300);
  });
});
