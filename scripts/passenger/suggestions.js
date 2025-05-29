function enableSuggestions(inputSelector, suggestions) {
  const $input = $(inputSelector);
  const $container = $input.parent();
  const $dropdown = $('<ul class="suggestion-dropdown"></ul>');

  // Append dropdown to the form-group for relative positioning
  $container.append($dropdown);

  // Update suggestions list
  function updateSuggestions(value) {
    $dropdown.empty();
    let matches = suggestions;

    // Filter suggestions if there's input
    if (value.length > 0) {
      matches = suggestions.filter((item) =>
        item.toLowerCase().includes(value.toLowerCase())
      );
    }

    // Only show dropdown if there are matches
    if (matches.length === 0) {
      $dropdown.hide();
      return;
    }

    matches.forEach((match) => {
      const $item = $(`<li class="suggestion-item">${match}</li>`);
      $dropdown.append($item);
      $item.on("click", () => {
        $input.val(match);
        $dropdown.empty().hide();
      });
    });

    $dropdown.show();
  }

  // Show all suggestions on focus
  $input.on("focus", () => {
    updateSuggestions($input.val().trim());
  });

  // Update suggestions on input
  $input.on("input", () => {
    updateSuggestions($input.val().trim());
  });

  // Hide dropdown on blur (with slight delay to allow clicks)
  $input.on("blur", () => {
    setTimeout(() => $dropdown.empty().hide(), 200);
  });

  // Handle keyboard navigation
  $input.on("keydown", (e) => {
    const $items = $dropdown.find(".suggestion-item");
    if ($items.length === 0) return;

    let index = $items.index($items.filter(".selected"));

    if (e.key === "ArrowDown") {
      e.preventDefault();
      if (index < $items.length - 1) {
        $items.removeClass("selected");
        $items.eq(index + 1).addClass("selected");
      }
    } else if (e.key === "ArrowUp") {
      e.preventDefault();
      if (index > 0) {
        $items.removeClass("selected");
        $items.eq(index - 1).addClass("selected");
      }
    } else if (e.key === "Enter" && index >= 0) {
      e.preventDefault();
      $input.val($items.eq(index).text());
      $dropdown.empty().hide();
    }
  });
}
