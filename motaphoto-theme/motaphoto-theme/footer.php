<!-- Pied de page commun aux pages du thème. -->
<footer class="site-footer">
    <?php
    wp_nav_menu(
        array(
            'theme_location' => 'footer-menu',
            'container'      => false,
            'menu_class'     => 'footer-menu',
            'fallback_cb'    => false,
        )
    );
    ?>
</footer>

<?php get_template_part( 'templates_part/modal' ); ?>

<!-- WordPress ajoute ici les scripts chargés dans le footer. -->
<?php
wp_footer();
?>
</body>
</html>
