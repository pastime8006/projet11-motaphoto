jQuery(function($) {
    // Ferme la modale uniquement si le clic porte sur le fond extérieur.
    $('.popup-overlay').on('click', function(event) {
        if (event.target === this) {
            $(this).hide();
        }
    });

    // Ferme la modale lorsque l'utilisateur appuie sur la touche Échap.
    $(document).on('keydown', function(event) {
        if (event.key === 'Escape') {
            $('.popup-overlay').hide();
        }
    });
});
