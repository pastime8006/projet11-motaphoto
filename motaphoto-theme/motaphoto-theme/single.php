<?php
/**
 * Modèle utilisé pour afficher un article individuel.
 */

 
// Charge l'en-tête commun du site.
get_header(); ?>
 
    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">
            <h1>ceci est single.php</h1>
 
        <?php
        // La boucle charge le contenu demandé pour que les fonctions WordPress
        // comme the_title() ou the_ID() puissent l'utiliser.
        while ( have_posts() ) : the_post();
    
            // Affiche les informations spécifiques si le contenu est une photo.
            if ( 'photo' === get_post_type() ) :
                $photo_id = get_the_ID();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <?php // Affiche l'image mise en avant lorsqu'elle existe. ?>
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="photo-thumbnail">
                            <?php the_post_thumbnail( 'metaphoto-photo' ); ?>
                        </div>
                    <?php endif; ?>

                    <div class="photo-details">
                        <?php // Affiche les champs personnalisés de la photo en échappant leur texte pour le HTML. ?>
                        <p>Type : <?php echo esc_html( (string) get_field( 'types', $photo_id ) ); ?></p>
                        <p>Références : <?php echo esc_html( (string) get_field( 'references', $photo_id ) ); ?></p>
                        <p>Année : <?php echo esc_html( (string) get_field( 'annee', $photo_id ) ); ?></p>

                        <?php // Récupère les catégories et formats associés à cette photo. ?>
                        <?php
                        $categories = get_the_terms( $photo_id, 'categorie' );
                        $formats    = get_the_terms( $photo_id, 'format' );
                        ?>

                        <?php // N'affiche les catégories que si WordPress a renvoyé des termes valides. ?>
                        <?php if ( $categories && ! is_wp_error( $categories ) ) : ?>
                            <p>Catégorie : <?php echo esc_html( implode( ', ', wp_list_pluck( $categories, 'name' ) ) ); ?></p>
                        <?php endif; ?>

                        <?php // Même vérification pour les formats associés. ?>
                        <?php if ( $formats && ! is_wp_error( $formats ) ) : ?>
                            <p>Format : <?php echo esc_html( implode( ', ', wp_list_pluck( $formats, 'name' ) ) ); ?></p>
                        <?php endif; ?>
                    </div>
                </article>
                <?php
            else :
                // Pour les autres types de contenu, charge le modèle correspondant au format de l'article.
                get_template_part( 'content', get_post_format() );
                // Affiche les commentaires si la discussion est ouverte ou si des commentaires existent déjà.
                if ( comments_open() || get_comments_number() ) :
                    comments_template();
                endif;
            endif;

            // Ajoute les liens vers les contenus précédent et suivant, s'ils existent.
            the_post_navigation( array(
                // %title est remplacé par le titre du contenu voisin.
                'next_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Next', 'motaphoto-theme' ) . '</span> ' .
                    '<span class="screen-reader-text">' . __( 'Next post:', 'motaphoto-theme' ) . '</span> ' .
                    '<span class="post-title">%title</span>',
                'prev_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Previous', 'motaphoto-theme' ) . '</span> ' .
                    '<span class="screen-reader-text">' . __( 'Previous post:', 'motaphoto-theme' ) . '</span> ' .
                    '<span class="post-title">%title</span>',
            ) );
 
        // Termine la boucle WordPress.
        endwhile;
        ?>
 
        </main><!-- .site-main -->
    </div><!-- .content-area -->
 
<?php
// Charge le pied de page commun du site.
get_footer();
?>
