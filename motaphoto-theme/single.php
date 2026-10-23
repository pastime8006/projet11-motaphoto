<?php
/**
 * Modèle utilisé pour afficher un article individuel.
 */
 
get_header(); ?>
 
    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">
 
        <?php
        // La boucle récupère l'article demandé et prépare ses données d'affichage.
        while ( have_posts() ) : the_post();
 
            /*
             * Inclut un modèle de contenu adapté au format de l'article.
             * WordPress peut ainsi utiliser un fichier content-<format>.php s'il existe.
             */
            get_template_part( 'content', get_post_format() );
 
            // Affiche les commentaires s'ils sont autorisés ou déjà présents.
            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;
 
            // Ajoute des liens vers l'article précédent et l'article suivant.
            the_post_navigation( array(
                'next_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Next', 'twentyfifteen' ) . '</span> ' .
                    '<span class="screen-reader-text">' . __( 'Next post:', 'twentyfifteen' ) . '</span> ' .
                    '<span class="post-title">%title</span>',
                'prev_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Previous', 'twentyfifteen' ) . '</span> ' .
                    '<span class="screen-reader-text">' . __( 'Previous post:', 'twentyfifteen' ) . '</span> ' .
                    '<span class="post-title">%title</span>',
            ) );
 
        // Fin de la boucle des articles.
        endwhile;
        ?>
 
        </main><!-- .site-main -->
    </div><!-- .content-area -->
 
<?php get_footer(); ?>
