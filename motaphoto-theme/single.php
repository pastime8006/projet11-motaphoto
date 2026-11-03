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
                            <div class="thumbnails-zone-infos">THUMBNAILS</div>
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

                if ( comments_open() || get_comments_number() ) :
                    comments_template();
                endif;
            endif;

            the_post_navigation(
                array(
                    'next_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Next', 'motaphoto-theme' ) . '</span> ' .
                        '<span class="screen-reader-text">' . __( 'Next post:', 'motaphoto-theme' ) . '</span> ' .
                        '<span class="post-title">%title</span>',
                    'prev_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Previous', 'motaphoto-theme' ) . '</span> ' .
                        '<span class="screen-reader-text">' . __( 'Previous post:', 'motaphoto-theme' ) . '</span> ' .
                        '<span class="post-title">%title</span>',
                )
            );
            ?>
        <?php endwhile; ?>
    </main>
</div>

<?php get_footer(); ?>
