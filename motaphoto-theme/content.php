<?php
/**
 * Modèle de contenu standard pour les articles WordPress.
 *
 * Ce fichier est utilisé par get_template_part( 'content', get_post_format() )
 * lorsqu'aucun modèle spécifique au format de l'article n'existe.
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header class="entry-header">
        <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
    </header>

    <div class="entry-content">
        <?php
        the_content();

        wp_link_pages(
            array(
                'before' => '<div class="page-links">' . esc_html__( 'Pages :', 'motaphoto-theme' ),
                'after'  => '</div>',
            )
        );
        ?>
    </div>

    <footer class="entry-footer">
        <?php edit_post_link( __( 'Modifier', 'motaphoto-theme' ), '<span class="edit-link">', '</span>' ); ?>
    </footer>
</article>
