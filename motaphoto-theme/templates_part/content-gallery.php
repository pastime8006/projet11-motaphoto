<?php
// Charger les catégories et les formats disponibles pour alimenter les filtres.
$categories = get_terms([
    'taxonomy'   => 'categorie',
    'hide_empty' => false,
]);

$formats = get_terms([
    'taxonomy'   => 'format',
    'hide_empty' => false,
]);

// Lire et nettoyer les filtres transmis dans l’URL afin de les réutiliser dans le formulaire et la requête.
// Lire les paramètres de filtrage
$selected_category = isset($_GET['categorie'])
    ? sanitize_title(wp_unslash($_GET['categorie']))
    : '';

$selected_format = isset($_GET['format'])
    ? sanitize_title(wp_unslash($_GET['format']))
    : '';

$selected_sort = isset($_GET['tri'])
    ? sanitize_key(wp_unslash($_GET['tri']))
    : 'date_all';

// Revenir au tri par date décroissante si l’URL contient une option non autorisée.
if (!isset(motaphoto_gallery_sort_options()[$selected_sort])) {
    $selected_sort = 'date_all';
}

$current_page = max(1, absint(get_query_var('paged')), absint(get_query_var('page')));
$the_query = motaphoto_get_gallery_query(
    $selected_category,
    $selected_format,
    $selected_sort,
    $current_page
);
?>

<!-- Conteneur principal de la galerie de photos. -->
<section class="photos-page-content" data-gallery>

    <!-- Les sélections sont appliquées sans recharger la page. -->
    <form class="sorting-form" method="get" action="<?php echo esc_url(get_permalink(get_queried_object_id())); ?>">
        <!-- Liste les catégories disponibles et conserve la sélection active. -->
        <div class="form-taxonomy">
            <select name="categorie" id="categorie" class="form-categorie" aria-label="Catégorie">
                <option value="">Toutes les catégories</option>

                <?php if (!is_wp_error($categories)) : ?>
                    <?php foreach ($categories as $term) : ?>
                        <option
                            value="<?php echo esc_attr($term->slug); ?>"
                            <?php selected($selected_category, $term->slug); ?>>
                            <?php echo esc_html($term->name); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

            <!-- Liste les formats disponibles et conserve le format sélectionné. -->
            <select name="format" id="format" class="form-format" aria-label="Format">
                <option value="">Tous les formats</option>

                <?php if (!is_wp_error($formats)) : ?>
                    <?php foreach ($formats as $term) : ?>
                        <option
                            value="<?php echo esc_attr($term->slug); ?>"
                            <?php selected($selected_format, $term->slug); ?>>
                            <?php echo esc_html($term->name); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <!-- Propose les modes de tri pris en charge par la requête de la galerie. -->
        <select name="tri" id="tri" class="form-tri" aria-label="Tri">
            <option value="date_all" <?php selected($selected_sort, 'date_all'); ?>>
                Toutes les dates
            </option>
            <option value="date_desc" <?php selected($selected_sort, 'date_desc'); ?>>
                Date : plus récent
            </option>
            <option value="date_asc" <?php selected($selected_sort, 'date_asc'); ?>>
                Date : plus ancien
            </option>
            
        </select>
        

    </form>


    <div class="gallery-results" aria-live="polite" aria-busy="false">
        <?php if ($the_query->have_posts()) : ?>
            <div class="photos-grid">
                <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
                    <?php get_template_part('templates_part/content', 'photo-card'); ?>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <p>Aucune photo trouvée.</p>
        <?php endif; ?>
        <?php echo motaphoto_render_gallery_load_more($the_query, $current_page); ?>
    </div>

    <?php wp_reset_postdata(); ?>

</section>
