<?php
// Récupérer l’identifiant de la photo affichée, transmis au template.
$photo_id = isset($args['photo_id']) ? absint($args['photo_id']) : 0;

// Chercher les catégories associées à cette photo.
$categories = get_the_terms($photo_id, 'categorie');

// Préparer la recherche uniquement si la photo possède des catégories valides.
if ($photo_id && $categories && !is_wp_error($categories)) :
    $category_ids = wp_list_pluck($categories, 'term_id');

    // Récupérer au maximum deux autres photos partageant au moins une catégorie.
    $related_photos = new WP_Query([
        'post_type'      => 'photo',
        'post_status'    => 'publish',
        'posts_per_page' => 2,
        'post__not_in'   => [$photo_id],
        'tax_query'      => [
            [
                'taxonomy' => 'categorie',
                'field'    => 'term_id',
                'terms'    => $category_ids,
                'operator' => 'IN',
            ],
        ],
    ]);

    // Afficher le bloc seulement si au moins une photo apparentée existe.
    if ($related_photos->have_posts()) :
        ?>
        <section class="photos-parent">
            <h3>VOUS AIMEREZ AUSSI</h3>
            <div class="img-container">
                <div class="photos-grid">
                    <?php while ($related_photos->have_posts()) : $related_photos->the_post(); 
                        $photo_categories = get_the_terms(get_the_ID(), 'categorie'); ?>

                        <article id="post-<?php the_ID(); ?>" <?php post_class('photo-card'); ?>>
                            <?php if (has_post_thumbnail()) : ?>
                                <h4 class="lightbox-cat-right">
                                    <?php if ($photo_categories && !is_wp_error($photo_categories)) : ?>
                                        <p><?php echo esc_html(implode(', ', wp_list_pluck($photo_categories, 'name'))); ?></p>
                                    <?php endif; ?>
                                </h4>

                                <h4 class="lightbox-title-left">
                                    <?php $photo_id = isset($args['photo_id']) ? absint($args['photo_id']) : 0; ?>
                                    <p><?php echo esc_html(get_the_title()); ?></p>
                                </h4>
                                <div class="photo-image">
                                    <?php the_post_thumbnail('motaphotos-photos', array(
                                        'class'          => 'img-lightbox',
                                        'data-large-src' => wp_get_attachment_image_url( get_post_thumbnail_id(), 'large' ),
                                    )); ?>
                                    <a
                                        class="photo-eye"
                                        href="<?php the_permalink(); ?>"
                                        aria-label="Voir <?php echo esc_attr(get_the_title()); ?>"
                                    >
                                        <img src="<?php echo esc_url(get_theme_file_uri('/assets/images/Icon_eye.png')); ?>" alt="">
                                    </a>
                                    <div class="container-ellipse">
                                        <span class="photo-ellipse-bg" aria-hidden="true">
                                            <img src="<?php echo esc_url(get_theme_file_uri('/assets/images/Ellipse2.png')); ?>" alt="">
                                        </span>
                                        <span class="photo-ellipse" aria-hidden="true">
                                            <img src="<?php echo esc_url(get_theme_file_uri('/assets/images/Group-31.png')); ?>" alt="">
                                        </span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>
        <?php
    endif;

    // Restaurer la photo courante après la boucle personnalisée.
    wp_reset_postdata();
endif;
