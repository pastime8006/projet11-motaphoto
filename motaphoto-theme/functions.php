<?php
//==========================================
// CHARGEMENT DES STYLES ET SCRIPTS DU THÈME
//==========================================

// Charge les styles et scripts du thème, puis transmet au JavaScript la configuration Ajax.
function motaphoto_theme_enqueue_scripts() {

    // Charge Font Awesome depuis le CDN pour les icônes de la lightbox.
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css',
        array(),
        '6.7.2'
    );

    // Utilise filemtime comme version pour actualiser les fichiers après une modification.
    wp_enqueue_style(
        'motaphoto-theme-style',
        get_stylesheet_directory_uri() . '/assets/css/main.css',
        array(),
        filemtime( get_stylesheet_directory() . '/assets/css/main.css' )
    );

// Charge la version de jQuery fournie par WordPress.
wp_enqueue_script( 'jquery' );

    // Charge le script principal après jQuery.
    wp_enqueue_script(
        'motaphoto-theme-scripts',
        get_theme_file_uri( '/assets/js/scripts.js' ),
        array( 'jquery' ),
        filemtime( get_theme_file_path( '/assets/js/scripts.js' ) ),
        true
    );

    // Rend l'URL Ajax de WordPress et un nonce accessibles dans scripts.js.
    wp_localize_script(
        'motaphoto-theme-scripts',
        'motaphotoGallery',
        array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'motaphoto_gallery_nonce' ),
        )
    );

    // Charge la lightbox dans le pied de page après jQuery.
    wp_enqueue_script(
        'motaphoto-theme-lightbox',
        get_theme_file_uri( '/assets/js/lightbox.js' ),
        array( 'jquery' ),
        filemtime( get_theme_file_path( '/assets/js/lightbox.js' ) ),
        true
    );

      // Charge le js du pied menu mobile
    wp_enqueue_script(
        'motaphoto-theme-mobile',
        get_theme_file_uri( '/assets/js/toggle-mobile.js' ),
        array( 'jquery' ),
        filemtime( get_theme_file_path( '/assets/js/toggle-mobile.js' ) ),
        true
    );
}

add_action( 'wp_enqueue_scripts', 'motaphoto_theme_enqueue_scripts' );

//==========================================
// TRI ET REQUÊTE DE LA GALERIE
//==========================================

// Retourne la liste blanche des tris autorisés et leurs paramètres WP_Query.
function motaphoto_gallery_sort_options() {
    return array(
        'date_all'  => array( 'orderby' => 'date', 'order' => 'DESC' ), // Toutes les dates
        'date_desc' => array( 'orderby' => 'date', 'order' => 'DESC' ), // Plus récentes
        'date_asc'  => array( 'orderby' => 'date', 'order' => 'ASC' ),
        'title_asc' => array( 'orderby' => 'title', 'order' => 'ASC' ),
        'title_desc'=> array( 'orderby' => 'title', 'order' => 'DESC' ),
        'annee_asc' => array( 'meta_key' => 'annee', 'orderby' => 'meta_value_num', 'order' => 'ASC' ),
        'annee_desc'=> array( 'meta_key' => 'annee', 'orderby' => 'meta_value_num', 'order' => 'DESC' ),
    );
}

// Construit la requête commune à l'affichage initial et au filtrage Ajax.
function motaphoto_get_gallery_query( $category, $format, $sort, $page = 1 ) {
    // Remplace toute option de tri non autorisée par le tri par défaut.
    $sort_options = motaphoto_gallery_sort_options();
    if ( ! isset( $sort_options[ $sort ] ) ) {
        $sort = 'date_desc';
    }

    // Définit le type de contenu, le nombre de résultats et la page, puis ajoute le tri.
    $args = array_merge(
        array(
            'post_type'      => 'photo',
            'post_status'    => 'publish',
            'posts_per_page' => 8,
            'paged'          => max( 1, absint( $page ) ),
        ),
        $sort_options[ $sort ]
    );

    // Ajoute uniquement les filtres de taxonomie effectivement sélectionnés.
    $tax_query = array();
    if ( $category !== '' ) {
        $tax_query[] = array(
            'taxonomy' => 'categorie',
            'field'    => 'slug',
            'terms'    => $category,
        );
    }
    if ( $format !== '' ) {
        $tax_query[] = array(
            'taxonomy' => 'format',
            'field'    => 'slug',
            'terms'    => $format,
        );
    }
    if ( $tax_query ) {
        // Combine les filtres sélectionnés et exige qu'une photo satisfasse chacun d'eux.
        $args['tax_query'] = array_merge( array( 'relation' => 'AND' ), $tax_query );
    }

    // Exécute la requête WordPress avec les critères préparés.
    return new WP_Query( $args );
}

//==============================================
// PAGINATION ET CHARGEMENT AJAX DE LA GALERIE
//==============================================

// Génère le bouton permettant de charger la page suivante de la galerie.
function motaphoto_render_gallery_load_more( $query, $current_page ) {
    // Garde le bouton visible, mais désactivé quand il n'y a plus de page.
    $has_more_pages = $current_page < $query->max_num_pages;

    // Capture le bouton pour l'affichage initial ou la réponse Ajax.
    ob_start();
    ?>
    <div class="gallery-pagination">
        <button
            type="button"
            class="photos-pagination gallery-load-more"
            data-page="<?php echo esc_attr( $current_page + 1 ); ?>"
            <?php disabled( ! $has_more_pages ); ?>
            aria-label="<?php esc_attr_e( 'Charger 8 photos supplémentaires', 'motaphoto-theme' ); ?>"
        ><?php esc_html_e( 'Charger plus', 'motaphoto-theme' ); ?></button>
    </div>
    <?php
    return ob_get_clean();
}

// Traite la requête Ajax, génère la grille et renvoie le résultat au format JSON.
function motaphoto_ajax_filter_gallery() {
    // Vérifie le nonce fourni par le navigateur avant de traiter les filtres.
    check_ajax_referer( 'motaphoto_gallery_nonce', 'nonce' );

    // Récupère puis nettoie les valeurs envoyées par le formulaire.
    $category = isset( $_POST['categorie'] ) ? sanitize_title( wp_unslash( $_POST['categorie'] ) ) : '';
    $format   = isset( $_POST['format'] ) ? sanitize_title( wp_unslash( $_POST['format'] ) ) : '';
    $sort     = isset( $_POST['tri'] ) ? sanitize_key( wp_unslash( $_POST['tri'] ) ) : 'date_desc';
    $page     = isset( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1;
    $append   = isset( $_POST['append'] ) && 1 === absint( $_POST['append'] );
    $query    = motaphoto_get_gallery_query( $category, $format, $sort, $page );

    // Génère une grille complète ou uniquement les cartes à ajouter à la suite.
    ob_start();
    if ( $query->have_posts() ) {
        if ( ! $append ) {
            echo '<div class="photos-grid">';
        }
        while ( $query->have_posts() ) {
            $query->the_post();
            get_template_part( 'template-parts/content', 'photo-card' );
        }
        if ( ! $append ) {
            echo '</div>';
        }
    } elseif ( ! $append ) {
        echo '<p>Aucune photo trouvée.</p>';
    }
    // Prépare les cartes renvoyées et le bouton pour la page suivante.
    $grid_html = ob_get_clean();
    $load_more_html = motaphoto_render_gallery_load_more( $query, max( 1, $page ) );

    // Restaure les données de l'article principal et renvoie la réponse Ajax.
    wp_reset_postdata();
    wp_send_json_success(
        array(
            'grid'       => $grid_html,
            'pagination' => $load_more_html,
            'append'     => $append,
        )
    );
}
// Enregistre le traitement Ajax pour les utilisateurs connectés et les visiteurs.
add_action( 'wp_ajax_motaphoto_filter_gallery', 'motaphoto_ajax_filter_gallery' );
add_action( 'wp_ajax_nopriv_motaphoto_filter_gallery', 'motaphoto_ajax_filter_gallery' );

//==========================================
// CONFIGURATION GENERALE DU THEME
//==========================================

// Active les fonctionnalités WordPress utilisées par le thème.
function motaphoto_theme_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_image_size( 'motaphotos-photos', 564, 495, true );
      add_theme_support( 'custom-logo', array(
        'height'      => 100,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ) );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
  
}

add_action( 'after_setup_theme', 'motaphoto_theme_setup' );

//==========================================
// MENUS DE NAVIGATION
//==========================================

// Déclare les emplacements de menus disponibles dans l'administration WordPress.
function register_motaphotos_menus(){
    register_nav_menu( 'main-menu' , __('Menu principal', 'motaphotos') );
    register_nav_menu( 'footer-menu' , __('Menu footer', 'motaphotos') );
}

add_action('after_setup_theme', 'register_motaphotos_menus');

//==========================================
// OPTIONS D'AFFICHAGE DES PAGES WORDPRESS
//==========================================

/**
 * Active également le support natif des attributs de page à l'initialisation.
 */
add_action('init', function () {
    add_post_type_support('page', 'page-attributes');
});


/**
 * Ajoute le panneau des options d'affichage dans l'éditeur de pages.
 */
add_action('add_meta_boxes_page', function () {
    add_meta_box(
        'custom_page_display_options',
        'Options d’affichage',
        'custom_page_display_options_callback',
        'page',
        'side',
        'default'
    );
});

//==========================================
// AFFICHAGE DES OPTIONS DANS L'EDITEUR
//==========================================

/**
 * Affiche les cases à cocher et leur état actuel dans le panneau d'options.
 */
function custom_page_display_options_callback($post) {

    // Génère le jeton vérifié lors de l'enregistrement des réglages.
    wp_nonce_field(
        'custom_save_page_display_options',
        'custom_page_display_nonce'
    );

    // Associe chaque clé de réglage au libellé de sa case à cocher.
    $options = [
        'hide_title'   => 'Masquer le titre',
        'hide_header'  => 'Masquer l’en-tête',
        'hide_footer'  => 'Masquer le pied de page',
        'hide_featured'=> 'Masquer l’image mise en avant',
        'hide_sidebar' => 'Masquer la barre latérale',
    ];

    echo '<p>Choisissez les éléments à masquer sur cette page.</p>';

    // Affiche une case par option et la coche si elle est déjà enregistrée.
    foreach ($options as $key => $label) {
        $value = get_post_meta(
            $post->ID,
            '_display_' . $key,
            true
        );

        echo '<p><label>';
        echo '<input type="checkbox" name="custom_display_options['
            . esc_attr($key)
            . ']" value="1" '
            . checked($value, '1', false)
            . '> ';
        echo esc_html($label);
        echo '</label></p>';
    }
}

//==========================================
// ENREGISTREMENT DES OPTIONS DE PAGE
//==========================================

/**
 * Enregistre les options cochées après validation de la requête et des droits.
 */
add_action('save_post_page', function ($post_id) {

    // Ignore une requête sans nonce valide.
    if (
        !isset($_POST['custom_page_display_nonce']) ||
        !wp_verify_nonce(
            sanitize_text_field(
                wp_unslash($_POST['custom_page_display_nonce'])
            ),
            'custom_save_page_display_options'
        )
    ) {
        return;
    }

    // Ignore les sauvegardes automatiques et les révisions.
    if (
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
        wp_is_post_revision($post_id)
    ) {
        return;
    }

    // Vérifie que l'utilisateur peut modifier cette page.
    if (!current_user_can('edit_page', $post_id)) {
        return;
    }

    // Limite les réglages traités à ceux prévus par le thème.
    $allowed = [
        'hide_title',
        'hide_header',
        'hide_footer',
        'hide_featured',
        'hide_sidebar',
    ];

    // Récupère les options soumises ou utilise un tableau vide si rien n'est envoyé.
    $submitted = (
        isset($_POST['custom_display_options']) &&
        is_array($_POST['custom_display_options'])
    )
        ? wp_unslash($_POST['custom_display_options'])
        : [];

    // Enregistre les cases cochées et supprime les options décochées.
    foreach ($allowed as $key) {
        if (
            isset($submitted[$key]) &&
            $submitted[$key] === '1'
        ) {
            update_post_meta(
                $post_id,
                '_display_' . $key,
                '1'
            );
        } else {
            delete_post_meta(
                $post_id,
                '_display_' . $key
            );
        }
    }
});

//==========================================
// APPLICATION DES OPTIONS SUR LE SITE
//==========================================

/**
 * Ajoute au body les classes correspondant aux réglages de la page affichée.
 */
add_filter('body_class', function ($classes) {

    // Ces classes ne concernent que les pages et nécessitent un identifiant valide.
    if (!is_page()) {
        return $classes;
    }

    $post_id = get_queried_object_id();

    if (!$post_id) {
        return $classes;
    }

    // Associe chaque réglage enregistré à la classe CSS correspondante.
    $class_map = [
        'hide_title'    => 'page-hide-title',
        'hide_header'   => 'page-hide-header',
        'hide_footer'   => 'page-hide-footer',
        'hide_featured' => 'page-hide-featured',
        'hide_sidebar'  => 'page-hide-sidebar',
    ];

    // Ajoute au body les classes des options activées.
    foreach ($class_map as $option => $class) {
        if (
            get_post_meta(
                $post_id,
                '_display_' . $option,
                true
            ) === '1'
        ) {
            $classes[] = $class;
        }
    }

    return $classes;
});

//==========================================
// REGLES CSS POUR MASQUER LES ELEMENTS
//==========================================

/**
 * Injecte les règles CSS qui masquent les éléments demandés sur la page.
 */
add_action('wp_head', function () {

    // N'ajoute ces règles que sur les pages WordPress.
    if (!is_page()) {
        return;
    }
    ?>
    <style id="custom-page-display-options">

    /* Masquer le titre */
    body.page-hide-title .entry-title,
    body.page-hide-title .wp-block-post-title,
    body.page-hide-title .page-title,
    body.page-hide-title h1.entry-title {
        display: none !important;
    }

    /* Masquer l'en-tête : sélecteurs courants */
    body.page-hide-header #masthead,
    body.page-hide-header .site-header,
    body.page-hide-header .elementor-location-header,
    body.page-hide-header header.wp-block-template-part {
        display: none !important;
    }

    /* Masquer le pied de page */
    body.page-hide-footer #colophon,
    body.page-hide-footer .site-footer,
    body.page-hide-footer .elementor-location-footer,
    body.page-hide-footer footer.wp-block-template-part {
        display: none !important;
    }

    /* Masquer l'image mise en avant */
    body.page-hide-featured .post-thumbnail,
    body.page-hide-featured .wp-block-post-featured-image,
    body.page-hide-featured .featured-image,
    body.page-hide-featured .post-featured-image {
        display: none !important;
    }

    /* Masquer la barre latérale */
    body.page-hide-sidebar #secondary,
    body.page-hide-sidebar .sidebar,
    body.page-hide-sidebar .widget-area,
    body.page-hide-sidebar .elementor-location-sidebar {
        display: none !important;
    }

    </style>
    <?php
});
