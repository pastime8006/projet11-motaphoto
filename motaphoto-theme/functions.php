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
        '1.0',
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
      add_theme_support( 'custom-logo', array(
        'height'      => 100,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ) );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
    add_theme_support( 'menus' );
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