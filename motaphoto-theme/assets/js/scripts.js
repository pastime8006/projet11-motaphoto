jQuery(function ($) {
  // Ouvre la modale depuis le lien Contact ou les boutons de contact des photos.
  $(document).on(
    "click",
    'a[href="#contact"], .btn-contact-infos',
    function (event) {
      event.preventDefault();

      const reference = $(this).data("reference") || "";
      $(".popup-contact [name='ref-photos']").val(reference);

      $(".popup-overlay").css("display", "flex");
    }
  );

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


