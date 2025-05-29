$(document).ready(function () {
  // Validation for flight search form
  $("#flight-search-form").on("submit", function (e) {
    const origin = $("#origin").val().trim();
    const destination = $("#destination").val().trim();

    if (origin === destination) {
      e.preventDefault();
      alert("Origin and destination cannot be the same.");
    }
  });

  // Set minimum date for departure to today
  const today = new Date().toISOString().split("T")[0];
  $("#departure_date").attr("min", today);

  // Enable suggestions for origin and destination inputs
  enableSuggestions("#origin", origins);
  enableSuggestions("#destination", destinations);
});
