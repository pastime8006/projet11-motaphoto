<!-- Début du document HTML : WordPress fournit la langue et les classes adaptées à la page. -->
<!doctype html>
<!-- language_attributes() ajoute les attributs de langue et d'encodage définis par WordPress. -->
<html <?php language_attributes(); ?>>
<head>
    <!-- Définit l'encodage du site configuré dans WordPress. -->
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <!-- Adapte la largeur de la page aux écrans mobiles. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- wp_head() charge les styles, scripts et métadonnées ajoutés par le thème et les extensions. -->
    <?php wp_head(); ?>
</head>
<!-- body_class() ajoute les classes WordPress et les classes personnalisées du thème au body. -->
<body <?php body_class(); ?>>
<!-- wp_body_open() permet aux extensions d'ajouter du contenu juste après l'ouverture du body. -->
<?php wp_body_open(); ?>

<!-- En-tête principal du site. -->
<header class="site-header">
    <div class="custom-logo-container">
        <!-- Affiche le logo défini dans Apparence > Personnaliser. -->
        <?php the_custom_logo(); ?>
    </div>
    <!-- Bouton utilisé par le JavaScript pour ouvrir et fermer le menu mobile. -->
    <button class="btn-menu-mobile" type="button" aria-label="Ouvrir le menu" aria-expanded="false">
        <div class="line"></div>
    </button>

    <!-- Zone de navigation principale du site. -->
    <nav class="site-navigation" aria-label="Navigation principale">
        <?php
        // Affiche le menu associé à l'emplacement « main-menu » dans l'administration.
        wp_nav_menu(
            array(
                // Emplacement de menu déclaré dans functions.php.
                'theme_location' => 'main-menu',
                // N'ajoute pas de conteneur HTML autour de la liste du menu.
                'container'      => false,
                // Classe CSS appliquée à la liste <ul> du menu.
                'menu_class'     => 'main-menu',
                // N'affiche aucun menu de remplacement si aucun menu n'est associé.
                'fallback_cb'    => false,
            )
        );
        ?>
    </nav>
</header>
