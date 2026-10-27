
<?php
/**
 * Template Name: Photos Page
 * Template Post Type: page
 */

get_header();

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
    'posts_per_page' => -1,
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

<main id="main" class="site-main">

    <h1>Photos</h1>

    <form method="get">

        <label for="categorie">Catégorie :</label>
        <select name="categorie" id="categorie">
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

        <label for="format">Format :</label>
        <select name="format" id="format">
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

        <label for="tri">Trier par :</label>
        <select name="tri" id="tri">
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

        <button type="submit">Filtrer</button>

    </form>

    <?php if ($the_query->have_posts()) : ?>

        <?php while ($the_query->have_posts()) :
            $the_query->the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                <?php if (has_post_thumbnail()) : ?>
                    <a href="<?php the_permalink(); ?>">
                        <?php the_post_thumbnail('large'); ?>
                    </a>
                <?php endif; ?>

                <h2>
                    <a href="<?php the_permalink(); ?>">
                        <?php the_title(); ?>
                    </a>
                </h2>

                <p>Type : <?php echo esc_html((string) get_field('types')); ?></p>
                <p>Références : <?php echo esc_html((string) get_field('references')); ?></p>
                <p>Année : <?php echo esc_html((string) get_field('annee')); ?></p>

                <?php
                $post_categories = get_the_terms(get_the_ID(), 'categorie');
                $post_formats = get_the_terms(get_the_ID(), 'format');
                ?>

                <p>Catégorie :
                    <?php
                    if ($post_categories && !is_wp_error($post_categories)) {
                        echo esc_html(implode(', ', wp_list_pluck($post_categories, 'name')));
                    }
                    ?>
                </p>

                <p>Format :
                    <?php
                    if ($post_formats && !is_wp_error($post_formats)) {
                        echo esc_html(implode(', ', wp_list_pluck($post_formats, 'name')));
                    }
                    ?>
                </p>

            </article>

        <?php endwhile; ?>

    <?php else : ?>

        <p>Aucune photo trouvée.</p>

    <?php endif; ?>

    <?php wp_reset_postdata(); ?>

</main>

<?php get_footer(); ?>
