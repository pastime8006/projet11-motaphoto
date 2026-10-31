<?php
// Récupérer les termes des taxonomies
$categories = get_terms([
    'taxonomy'   => 'categorie',
    'hide_empty' => false,
]);

$formats = get_terms([
    'taxonomy'   => 'format',
    'hide_empty' => false,
]);

// Lire les paramètres de filtrage
$selected_category = isset($_GET['categorie'])
    ? sanitize_text_field(wp_unslash($_GET['categorie']))
    : '';

$selected_format = isset($_GET['format'])
    ? sanitize_text_field(wp_unslash($_GET['format']))
    : '';

$selected_sort = isset($_GET['tri'])
    ? sanitize_key(wp_unslash($_GET['tri']))
    : 'date_desc';

// Définir les tris autorisés
$sort_options = [
    'date_desc' => [
        'orderby' => 'date',
        'order'   => 'DESC',
    ],
    'date_asc' => [
        'orderby' => 'date',
        'order'   => 'ASC',
    ],
    'title_asc' => [
        'orderby' => 'title',
        'order'   => 'ASC',
    ],
    'title_desc' => [
        'orderby' => 'title',
        'order'   => 'DESC',
    ],
    'annee_asc' => [
        'meta_key' => 'annee',
        'orderby'  => 'meta_value_num',
        'order'    => 'ASC',
    ],
    'annee_desc' => [
        'meta_key' => 'annee',
        'orderby'  => 'meta_value_num',
        'order'    => 'DESC',
    ],
];

if (!isset($sort_options[$selected_sort])) {
    $selected_sort = 'date_desc';
}

$query_args = [
    'post_type'      => 'photo',
    'post_status'    => 'publish',
    'posts_per_page' => 8,
    'paged'          => max(1, absint(get_query_var('paged')), absint(get_query_var('page'))),
];

$query_args = array_merge(
    $query_args,
    $sort_options[$selected_sort]
);

// Filtrer par taxonomies
$tax_query = [];

if ($selected_category !== '') {
    $tax_query[] = [
        'taxonomy' => 'categorie',
        'field'    => 'slug',
        'terms'    => $selected_category,
    ];
}

if ($selected_format !== '') {
    $tax_query[] = [
        'taxonomy' => 'format',
        'field'    => 'slug',
        'terms'    => $selected_format,
    ];
}

if (!empty($tax_query)) {
    $query_args['tax_query'] = $tax_query;
    $query_args['tax_query']['relation'] = 'AND';
}

$the_query = new WP_Query($query_args);
?>

<section class="photos-page-content">

    <form class="sorting-form" method="get" action="<?php echo esc_url(get_permalink(get_queried_object_id())); ?>">
        <div class="form-taxonomy">
            <select name="categorie" id="categorie" class="form-categorie">
                <option value="">CATÉGORIES</option>

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

            <select name="format" id="format" class="form-format">
                <option value="">FORMATS</option>

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
        <select name="tri" id="tri" class="form-tri">
            <option value="date_desc" <?php selected($selected_sort, 'date_desc'); ?>>
                Date : plus récent
            </option>
            <option value="date_asc" <?php selected($selected_sort, 'date_asc'); ?>>
                Date : plus ancien
            </option>
            <option value="title_asc" <?php selected($selected_sort, 'title_asc'); ?>>
                Titre : A à Z
            </option>
            <option value="title_desc" <?php selected($selected_sort, 'title_desc'); ?>>
                Titre : Z à A
            </option>
            <option value="annee_asc" <?php selected($selected_sort, 'annee_asc'); ?>>
                Année : croissante
            </option>
            <option value="annee_desc" <?php selected($selected_sort, 'annee_desc'); ?>>
                Année : décroissante
            </option>
        </select>

        <!-- <button type="submit">Filtrer</button> -->

    </form>


    <?php if ($the_query->have_posts()) : ?>
        <div class="photos-grid">
            <?php while ($the_query->have_posts()) :
                $the_query->the_post(); ?>

                <article id="post-<?php the_ID(); ?>" <?php post_class('photo-card'); ?>>

                    <?php if (has_post_thumbnail()) : ?>
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail('motaphotos-photos'); ?>
                            <span class="photo-eye" aria-hidden="true">
                                <img src="<?php echo esc_url(get_theme_file_uri('/assets/images/Icon_eye.png')); ?>" alt="">
                            </span>
                            <span class="photo-ellipse-bg" aria-hidden="true">
                                <img src="<?php echo esc_url(get_theme_file_uri('/assets/images/Ellipse2.png')); ?>" alt="">
                            </span>
                            <span class="photo-ellipse" aria-hidden="true">
                                <img src="<?php echo esc_url(get_theme_file_uri('/assets/images/Group-31.png')); ?>" alt="">
                            </span>
                        </a>

                    <?php endif; ?>

                    <!-- Titre de la photo masqué sur cette page.
                    <h2>
                        <a href="<?php the_permalink(); ?>">
                            <?php the_title(); ?>
                        </a>
                    </h2>
                    -->

                </article>

            <?php endwhile; ?>
        </div>

    <?php else : ?>

        <p>Aucune photo trouvée.</p>

    <?php endif; ?>

    <!-- BOUTON PAGINATION -->

    <div class="gallery-pagination">
        <button class="btn-pagination">
            <?php if ($the_query->max_num_pages > 1) : ?>
                <nav class="photos-pagination" aria-label="Pagination des photos">
                    <?php
                    echo paginate_links([
                        'base'      => esc_url(add_query_arg('paged', '%#%')),
                        'format'    => '',
                        'current'   => max(1, absint(get_query_var('paged')), absint(get_query_var('page'))),
                        'total'     => $the_query->max_num_pages,
                        'prev_text' => __('Précédent', 'motaphoto-theme'),
                        'next_text' => __('Suivant', 'motaphoto-theme'),
                    ]);
                    ?>
                </nav>
            <?php endif; ?>
        </button>
    </div>
    <?php wp_reset_postdata(); ?>

</section>
