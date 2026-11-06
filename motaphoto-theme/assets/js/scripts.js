// Attend que le document soit prêt et récupère jQuery sous le nom $.
jQuery(function ($) {
  
  const $gallery = $("[data-gallery]");

  // Demande au serveur les résultats correspondant aux filtres et à la page indiquée.
  function chargerGalerie(page = 1) {
    
    if (!$gallery.length || typeof motaphotoGallery === "undefined") return;

    // Repère la zone qui sera remplacée par la réponse du serveur.
    const $results = $gallery.find(".gallery-results");
    $results.attr("aria-busy", "true");

    // Envoie les choix des menus à WordPress avec une requête Ajax.
    $.ajax({   
      url: motaphotoGallery.ajaxUrl,  
      method: "POST",
      dataType: "json",
      data: {
        action: "motaphoto_filter_gallery",
        // Jeton utilisé par WordPress pour vérifier la requête.
        nonce: motaphotoGallery.nonce,
        // Slug de la catégorie choisie, ou chaîne vide si « toutes ».
        categorie: $gallery.find("[name='categorie']").val(),
        // Slug du format choisi, ou chaîne vide si « tous ».
        format: $gallery.find("[name='format']").val(),
        // Clé correspondant à l'ordre de tri sélectionné.
        tri: $gallery.find("[name='tri']").val(),
        paged: page,
      },
    })
      // Traite la réponse quand la requête a réussi.
      .done(function (response) {
                if (!response || !response.success || !response.data) {
          // Affiche les détails dans la console pour faciliter le diagnostic.
          console.error("La réponse du filtrage de la galerie est invalide.", response);
          return;
        }

        // Remplace la grille et la pagination par le HTML reçu du serveur.
        $results.html(response.data.grid + response.data.pagination);
      })
      // Signale une erreur HTTP, réseau ou de décodage de la réponse.
      .fail(function (xhr, status, error) {
        console.error("Échec du filtrage Ajax de la galerie.", status, error);
      })
      // S'exécute après succès ou échec de la requête.
      .always(function () {
        // Indique que le chargement est terminé.
        $results.attr("aria-busy", "false");
      });
  }

  // Quand un filtre ou le tri change, recommence les résultats à la première page.
  $gallery.on("change", ".sorting-form select", function () {
    chargerGalerie(1);
  });

  // Délègue les clics de pagination pour que les nouveaux boutons Ajax fonctionnent aussi.
  $gallery.on("click", ".gallery-page-link", function () {
    // Charge le numéro de page stocké dans l'attribut data-page du bouton.
    chargerGalerie($(this).data("page"));
  });

  // Ouvre la modale depuis le lien Contact ou les boutons de contact des photos.
  $(document).on(
    "click",
    'a[href="#contact"], .btn-contact-infos',
    function (event) {
      // Empêche le lien de naviguer vers une ancre ou de recharger la page.
      event.preventDefault();

      // Récupère la référence attachée au bouton (ou une chaîne vide si elle manque).
      const reference = $(this).data("reference") || "";
      // Insère la référence dans le champ du formulaire de contact.
      $(".popup-contact [name='ref-photos']").val(reference);

      // Affiche la modale de contact.
      $(".popup-overlay").css("display", "flex");
    }
  );

  // Ferme la modale uniquement si le clic porte sur le fond extérieur.
  $(".popup-overlay").on("click", function (event) {
    // Ne ferme pas la modale si l'utilisateur clique sur son contenu.
    if (event.target === this) {
      $(this).hide();
    }
  });

  // Ferme la modale lorsque l'utilisateur appuie sur la touche Échap.
  $(document).on("keydown", function (event) {
    // Vérifie que la touche Échap a été pressée.
    if (event.key === "Escape") {
      $(".popup-overlay").hide();
    }
  });
});
