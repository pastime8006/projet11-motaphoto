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
    <!-- Le titre du site renvoie vers la page d'accueil. -->
    <a class="site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>">
        <?php bloginfo( 'name' ); ?>
    </a>
</header>
