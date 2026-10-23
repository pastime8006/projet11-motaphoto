<!-- Pied de page commun aux pages du thème. -->
<footer class="site-footer">
    <!-- Affiche l'année actuelle et le nom configuré dans WordPress. -->
    <p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
</footer>

<!-- WordPress ajoute ici les scripts chargés dans le footer. -->
<?php wp_footer(); ?>
</body>
</html>
