<?php
/**
 * Modèle utilisé pour afficher un article individuel.
 */

// Charge l'en-tête commun du thème.
get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php // Parcourt le contenu demandé par WordPress, généralement une seule photo ?>
        <?php while ( have_posts() ) : ?>
            <?php
            the_post();

            // Utilise une mise en page spécifique pour le custom post type « photo ».
            if ( 'photo' === get_post_type() ) :
                // Mémorise l'identifiant de la photo pour les fonctions et templates associés.
                $photo_id = get_the_ID();

                // Récupère les photos précédente et suivante selon leur date de publication.
                $previous_photo = get_previous_post();
                $next_photo     = get_next_post();

                // À la première photo, fait revenir la flèche gauche vers la plus récente.
                if ( ! $previous_photo ) {
                    // Recherche la photo publiée la plus récemment pour créer une navigation circulaire.
                    $last_photo = get_posts(
                        array(
                            'post_type'      => 'photo',
                            'post_status'    => 'publish',
                            'posts_per_page' => 1,
                            'orderby'        => 'date',
                            'order'          => 'DESC',
                        )
                    );
                    $previous_photo = $last_photo ? $last_photo[0] : null;
                }

                // À la dernière photo, fait revenir la flèche droite vers la plus ancienne.
                if ( ! $next_photo ) {
                    // Recherche la photo publiée la plus anciennement pour créer une navigation circulaire.
                    $first_photo = get_posts(
                        array(
                            'post_type'      => 'photo',
                            'post_status'    => 'publish',
                            'posts_per_page' => 1,
                            'orderby'        => 'date',
                            'order'          => 'ASC',
                        )
                    );
                    $next_photo = $first_photo ? $first_photo[0] : null;
                }
                ?>
                <!-- Conteneur principal des informations et de l'image de la photo. -->
                <div class="main page-infos">
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <div class="content-zone">
                            <div class="content-zone-infos">
                                <?php // Affiche les métadonnées de la photo dans le template dédié. ?>
                                <?php get_template_part( 'template-parts/infos-photos', null, array( 'photo_id' => $photo_id ) ); ?>
                            </div>

                            <div class="content-zone-photos">
                                <?php // Affiche l'image mise en avant dans un format adapté à la page. ?>
                                <?php if ( has_post_thumbnail( $photo_id ) ) : ?>
                                    <?php echo get_the_post_thumbnail( $photo_id, 'large', array( 'class' => 'img-zone-photos' ) ); ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="contact-zone-infos">
                            <div class="contact-infos">
                                <p>Cette photo vous intéresse ?</p>
                                <?php // Transmet la référence ACF au JavaScript pour préremplir le contact. ?>
                                <button
                                    class="btn-contact-infos"
                                    type="button"
                                    data-reference="<?php echo esc_attr((string) get_field('references', $photo_id)); ?>"
                                >
                                    Contact
                                </button>
                            </div>
                            <div class="thumbnails-zone-infos">
                                <div class="thumbnails-box">
                                    <?php // Affiche une miniature de la photo courante. ?>
                                    <?php if ( has_post_thumbnail( $photo_id ) ) : ?>
                                        <?php echo get_the_post_thumbnail( $photo_id, 'thumbnail', array( 'class' => 'thumbnail-photo' ) ); ?>
                                    <?php endif; ?>
                                    <div class="thumbnails-arrows">
                                        <?php // Crée le lien vers la photo précédente ou une flèche inactive. ?>
                                        <?php if ( $previous_photo ) : ?>
                                            <a href="<?php echo esc_url( get_permalink( $previous_photo ) ); ?>" aria-label="Photo précédente">
                                                <img src="<?php echo esc_url( get_theme_file_uri( '/assets/images/arrow-thumbnails-righleftt.png' ) ); ?>" alt="">
                                            </a>
                                        <?php else : ?>
                                            <span aria-hidden="true">
                                                <img src="<?php echo esc_url( get_theme_file_uri( '/assets/images/arrow-thumbnails-righleftt.png' ) ); ?>" alt="">
                                            </span>
                                        <?php endif; ?>

                                        <?php // Crée le lien vers la photo suivante ou une flèche inactive. ?>
                                        <?php if ( $next_photo ) : ?>
                                            <a href="<?php echo esc_url( get_permalink( $next_photo ) ); ?>" aria-label="Photo suivante">
                                                <img src="<?php echo esc_url( get_theme_file_uri( '/assets/images/arrow-thumbnails-right.png' ) ); ?>" alt="">
                                            </a>
                                        <?php else : ?>
                                            <span aria-hidden="true">
                                                <img src="<?php echo esc_url( get_theme_file_uri( '/assets/images/arrow-thumbnails-right.png' ) ); ?>" alt="">
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php
                        // Affiche les photos liées ou similaires dans le template dédié.
                        get_template_part(
                            'template-parts/content-related-photos',
                            null,
                            array( 'photo_id' => $photo_id )
                        );
                        ?>
                    </article>
                </div>
                <?php
            else :
                // Pour les autres types de contenus, charge le template content.php
                // ou un template correspondant au format de publication.
                get_template_part( 'content', get_post_format() );
            endif;

            ?>
        <?php endwhile; ?>
    </main>
</div>

<?php get_footer(); ?>
