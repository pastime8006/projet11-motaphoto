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
    ? sanitize_text_field(wp_unslash($_GET['categorie']))
    : '';

$selected_format = isset($_GET['format'])
    ? sanitize_text_field(wp_unslash($_GET['format']))
    : '';

$selected_sort = isset($_GET['tri'])
    ? sanitize_key(wp_unslash($_GET['tri']))
    : 'date_desc';

// Associer chaque option de tri à ses critères WP_Query.
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

// Revenir au tri par date décroissante si l’URL contient une option non autorisée.
if (!isset($sort_options[$selected_sort])) {
    $selected_sort = 'date_desc';
}

// Préparer la requête des photos publiées et limiter les résultats à huit par page.
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

// Construire les critères de filtrage par catégorie et par format.
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

// N’ajouter les critères de taxonomie à la requête que si au moins un filtre est sélectionné.
if (!empty($tax_query)) {
    $query_args['tax_query'] = $tax_query;
    $query_args['tax_query']['relation'] = 'AND';
}

// Récupérer les photos correspondant aux filtres, au tri et à la page demandée.
$the_query = new WP_Query($query_args);
?>

<!-- Conteneur principal de la galerie de photos. -->
<section class="photos-page-content">

    <!-- Formulaire GET : permet de choisir une catégorie, un format et un ordre de tri. -->
    <form class="sorting-form" method="get" action="<?php echo esc_url(get_permalink(get_queried_object_id())); ?>">
        <!-- Liste les catégories disponibles et conserve la sélection active. -->
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

            <!-- Liste les formats disponibles et conserve le format sélectionné. -->
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
        <!-- Propose les modes de tri pris en charge par la requête de la galerie. -->
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
        

        <!-- Bouton de soumission désactivé dans l’interface actuelle. -->
        <!-- <button type="submit">Filtrer</button> -->

    </form>


    <!-- Afficher la grille lorsque la requête contient des photos. -->
    <?php if ($the_query->have_posts()) : ?>
        <!-- Parcourir les photos trouvées pour produire une carte par résultat. -->
        <div class="photos-grid">
            <?php while ($the_query->have_posts()) :
                $the_query->the_post();
                $photo_year       = get_field('annee', get_the_ID());
                $photo_categories = get_the_terms(get_the_ID(), 'categorie');
                $photo_reference  = get_field('references', get_the_ID());
                $photo_category_names = ($photo_categories && !is_wp_error($photo_categories))
                    ? implode(', ', wp_list_pluck($photo_categories, 'name'))
                    : '';
            ?>

                <article id="post-<?php the_ID(); ?>" <?php post_class('photo-card'); ?>>

                    <!-- Afficher la photo avec un lien vers sa page uniquement sur l’icône œil. -->
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
                                'data-photo-reference' => (string) $photo_reference,
                                'data-photo-category'  => $photo_category_names,
                            )); ?>
                            <a class="photo-eye" href="<?php the_permalink(); ?>" aria-label="Voir <?php echo esc_attr(get_the_title()); ?>">
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

                    <!-- Titre volontairement masqué sur cette page.
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

        <!-- Signaler qu’aucune photo ne correspond aux critères sélectionnés. -->
        <p>Aucune photo trouvée.</p>

    <?php endif; ?>

    <!-- Afficher les liens de navigation uniquement si les résultats occupent plusieurs pages. -->
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
    <!-- Restaurer les données de l’article principal après la boucle personnalisée. -->
    <?php wp_reset_postdata(); ?>

</section>
