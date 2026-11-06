<?php
//======= Carte d'une photo de la galerie
// Ce modèle est utilisé au chargement initial et après chaque réponse Ajax.
$photo_id             = get_the_ID();
$photo_categories     = get_the_terms( $photo_id, 'categorie' );
$photo_reference      = get_field( 'references', $photo_id );
$photo_category_names = ( $photo_categories && ! is_wp_error( $photo_categories ) )
    ? implode( ', ', wp_list_pluck( $photo_categories, 'name' ) )
    : '';
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'photo-card' ); ?>>
    <?php if ( has_post_thumbnail() ) : ?>
        <h4 class="lightbox-cat-right">
            <?php if ( $photo_categories && ! is_wp_error( $photo_categories ) ) : ?>
                <p><?php echo esc_html( $photo_category_names ); ?></p>
            <?php endif; ?>
        </h4>

        <h4 class="lightbox-title-left"><p><?php echo esc_html( get_the_title() ); ?></p></h4>

        <div class="photo-image">
            <?php the_post_thumbnail( 'motaphotos-photos', array(
                'class'                => 'img-lightbox',
                'data-large-src'       => wp_get_attachment_image_url( get_post_thumbnail_id(), 'large' ),
                'data-photo-reference' => (string) $photo_reference,
                'data-photo-category'  => $photo_category_names,
            ) ); ?>
            <a class="photo-eye" href="<?php the_permalink(); ?>" aria-label="Voir <?php echo esc_attr( get_the_title() ); ?>">
                <img src="<?php echo esc_url( get_theme_file_uri( '/assets/images/Icon_eye.png' ) ); ?>" alt="">
            </a>
            <div class="container-ellipse">
                <span class="photo-ellipse-bg" aria-hidden="true">
                    <img src="<?php echo esc_url( get_theme_file_uri( '/assets/images/Ellipse2.png' ) ); ?>" alt="">
                </span>
                <span class="photo-ellipse" aria-hidden="true">
                    <img src="<?php echo esc_url( get_theme_file_uri( '/assets/images/Group-31.png' ) ); ?>" alt="">
                </span>
            </div>
        </div>
    <?php endif; ?>
</article>
