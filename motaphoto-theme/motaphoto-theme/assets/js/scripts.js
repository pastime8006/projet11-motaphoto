jQuery(function ($) {
  // Ouvre la modale depuis tous les liens Contact, y compris ceux du footer.
  $(document).on("click", 'a[href="#contact"]', function (event) {
    event.preventDefault();
    $(".popup-overlay").show();
  });

    // Ferme la modale uniquement si le clic porte sur le fond extérieur.
  $(".popup-overlay").on("click", function (event) {
    if (event.target === this) {
      $(this).hide();
    }
  });

  // Ferme la modale lorsque l'utilisateur appuie sur la touche Échap.
  $(document).on("keydown", function (event) {
    if (event.key === "Escape") {
      $(".popup-overlay").hide();
    }
  });
});
