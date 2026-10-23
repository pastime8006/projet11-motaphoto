<?php
function motaphoto_theme_enqueue_scripts() {

    wp_enqueue_style(
        'motaphoto-theme-style',
        get_stylesheet_directory_uri() . '/assets/css/main.css',
        array(),
        filemtime( get_stylesheet_directory() . '/assets/css/main.css' )
    );

//============================
// scripts.js
//============================

    wp_enqueue_script(
        'motaphoto-theme-scripts',
        get_theme_file_uri( '/assets/js/scripts.js' ),
        array(),
        '1.0',
        true
    );
}

add_action( 'wp_enqueue_scripts', 'motaphoto_theme_enqueue_scripts' );


  
