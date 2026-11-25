<?php
/** Modèle utilisé pour afficher la page d’accueil. */

// Inclut l'en-tête commun du thème.
get_header();

?>

<?php
// La boucle WordPress récupère la page demandée et prépare ses données.
while ( have_posts() ) :
    the_post();
    ?>

    <main id="main" class="site-main">
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <?php if (has_post_thumbnail()) : ?>
                <figure class="hero-header">
                    <?php the_post_thumbnail('full', [
                        'class' => 'hero-header__image img-lightbox',
                        'alt'   => esc_attr(get_the_title()),
                    ]); ?>
                    <img class="titre-header"
                        src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/titre-header.png'); ?>"
                        alt="Photographie événement"
                    >
                </figure>
            <?php endif; ?>

            <!-- Titre et contenu saisis dans l'administration WordPress. -->
            <h1 class="entry-title"><?php the_title(); ?></h1>

            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </article>
    </main>
    <?php
endwhile;

get_template_part( 'template-parts/content-gallery' );



// Inclut le pied de page commun du thème.
get_footer();
