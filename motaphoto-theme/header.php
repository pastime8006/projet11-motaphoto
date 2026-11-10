<!-- Début du document : WordPress fournit la langue et les classes adaptées à la page. -->
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <!-- Encodage et adaptation de la page aux écrans mobiles. -->
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Point d'insertion des styles, scripts et métadonnées ajoutés par WordPress/extensions. -->
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<!-- Point d'insertion requis par WordPress et certaines extensions. -->
<?php wp_body_open(); ?>

<header class="site-header">
    <div class="custom-logo-container">
        <?php the_custom_logo(); ?>
    </div>
   <button class="btn-menu-mobile" type="button" aria-label="Ouvrir le menu" aria-expanded="false">
    <div class="line"></div>
     </button>


    <nav class="site-navigation" aria-label="Navigation principale">
        <?php
        // Affiche le menu principal défini dans l'administration.
        wp_nav_menu(
            array(
                'theme_location' => 'main-menu',
                'container'      => false,
                'menu_class'     => 'main-menu',
                'fallback_cb'    => false,
            )
        );
        ?>
     
    </nav>

</header>