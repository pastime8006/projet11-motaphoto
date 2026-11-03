<?php
function motaphoto_theme_enqueue_scripts() {

    wp_enqueue_style(
        'motaphoto-theme-style',
        get_stylesheet_directory_uri() . '/assets/css/main.css',
        array(),
        filemtime( get_stylesheet_directory() . '/assets/css/main.css' )
    );

// Charge la version de jQuery fournie par WordPress.
wp_enqueue_script( 'jquery' );

//============================
// scripts.js
//============================

    wp_enqueue_script(
        'motaphoto-theme-scripts',
        get_theme_file_uri( '/assets/js/scripts.js' ),
        array( 'jquery' ),
        filemtime( get_theme_file_path( '/assets/js/scripts.js' ) ),
        true
    );

     wp_enqueue_script(
        'motaphoto-theme-lightbox',
        get_theme_file_uri( '/assets/js/lightbox.js' ),
        array( 'jquery' ),
        filemtime( get_theme_file_path( '/assets/js/lightbox.js' ) ),
        true
    );
}

add_action( 'wp_enqueue_scripts', 'motaphoto_theme_enqueue_scripts' );


//============================
// add theme support
//============================


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
    add_theme_support( 'menus' );
    add_theme_support( 'add_meta_boxes_page' );
    
}

add_action( 'after_setup_theme', 'motaphoto_theme_setup' );

//============================
// Menus
//============================
  
function register_motaphotos_menus(){
    register_nav_menu( 'main-menu' , __('Menu principal', 'motaphotos') );
    register_nav_menu( 'footer-menu' , __('Menu footer', 'motaphotos') );
}

add_action('after_setup_theme', 'register_motaphotos_menus');


/**
 * Activer les attributs de page WordPress
 */
add_action('init', function () {
    add_post_type_support('page', 'page-attributes');
});


//==========================================
// * OPTIONS D'AFFICHAGE DES PAGES WORDPRESS
//==========================================

/**
 * Activer les attributs natifs des pages WordPress
 */
add_action('init', function () {
    add_post_type_support('page', 'page-attributes');
});


/**
 * 1. Ajouter le panneau dans l'édition des pages.
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

/**
 * 2. Afficher les cases à cocher.
 */
function custom_page_display_options_callback($post) {

    wp_nonce_field(
        'custom_save_page_display_options',
        'custom_page_display_nonce'
    );

    $options = [
        'hide_title'   => 'Masquer le titre',
        'hide_header'  => 'Masquer l’en-tête',
        'hide_footer'  => 'Masquer le pied de page',
        'hide_featured'=> 'Masquer l’image mise en avant',
        'hide_sidebar' => 'Masquer la barre latérale',
    ];

    echo '<p>Choisissez les éléments à masquer sur cette page.</p>';

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

/**
 * 3. Enregistrer les réglages.
 */
add_action('save_post_page', function ($post_id) {

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

    if (
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
        wp_is_post_revision($post_id)
    ) {
        return;
    }

    if (!current_user_can('edit_page', $post_id)) {
        return;
    }

    $allowed = [
        'hide_title',
        'hide_header',
        'hide_footer',
        'hide_featured',
        'hide_sidebar',
    ];

    $submitted = (
        isset($_POST['custom_display_options']) &&
        is_array($_POST['custom_display_options'])
    )
        ? wp_unslash($_POST['custom_display_options'])
        : [];

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

/**
 * 4. Ajouter une classe CSS au body
 * selon les réglages de la page affichée.
 */
add_filter('body_class', function ($classes) {

    if (!is_page()) {
        return $classes;
    }

    $post_id = get_queried_object_id();

    if (!$post_id) {
        return $classes;
    }

    $class_map = [
        'hide_title'    => 'page-hide-title',
        'hide_header'   => 'page-hide-header',
        'hide_footer'   => 'page-hide-footer',
        'hide_featured' => 'page-hide-featured',
        'hide_sidebar'  => 'page-hide-sidebar',
    ];

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

/**
 * 5. Appliquer les masquages avec du CSS.
 */
add_action('wp_head', function () {

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
