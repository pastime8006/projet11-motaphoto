<?php
/**
 * Modèle utilisé pour afficher un article individuel.
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php while ( have_posts() ) : ?>
            <?php
            the_post();

            if ( 'photo' === get_post_type() ) :
                $photo_id = get_the_ID();
                $previous_photo = get_previous_post();
                $next_photo     = get_next_post();

                // À la première photo, faire revenir la flèche gauche à la plus récente.
                if ( ! $previous_photo ) {
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

                // À la dernière photo, faire revenir la flèche droite à la plus ancienne.
                if ( ! $next_photo ) {
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
                <div class="main page-infos">
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <div class="content-zone">
                            <div class="content-zone-infos">
                                <?php get_template_part( 'templates_part/infos-photos', null, array( 'photo_id' => $photo_id ) ); ?>
                            </div>

                            <div class="content-zone-photos">
                                <?php if ( has_post_thumbnail( $photo_id ) ) : ?>
                                    <?php echo get_the_post_thumbnail( $photo_id, 'large', array( 'class' => 'img-zone-photos' ) ); ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="contact-zone-infos">
                            <div class="contact-infos">
                                <p>Cette photo vous intéresse ?</p>
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
                                    <?php if ( has_post_thumbnail( $photo_id ) ) : ?>
                                        <?php echo get_the_post_thumbnail( $photo_id, 'thumbnail', array( 'class' => 'thumbnail-photo' ) ); ?>
                                    <?php endif; ?>
                                    <div class="thumbnails-arrows">
                                        <?php if ( $previous_photo ) : ?>
                                            <a href="<?php echo esc_url( get_permalink( $previous_photo ) ); ?>" aria-label="Photo précédente">
                                                <img src="<?php echo esc_url( get_theme_file_uri( '/assets/images/arrow-thumbnails-righleftt.png' ) ); ?>" alt="">
                                            </a>
                                        <?php else : ?>
                                            <span aria-hidden="true">
                                                <img src="<?php echo esc_url( get_theme_file_uri( '/assets/images/arrow-thumbnails-righleftt.png' ) ); ?>" alt="">
                                            </span>
                                        <?php endif; ?>

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
                        get_template_part(
                            'templates_part/content-related-photos',
                            null,
                            array( 'photo_id' => $photo_id )
                        );
                        ?>
                    </article>
                </div>
                <?php
            else :
                get_template_part( 'content', get_post_format() );
            endif;

            ?>
        <?php endwhile; ?>
    </main>
</div>

<?php get_footer(); ?>
