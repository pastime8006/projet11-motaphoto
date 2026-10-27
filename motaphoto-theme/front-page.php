<?php
/** Modèle utilisé pour afficher les pages WordPress. */

// Inclut l'en-tête commun du thème.
get_header();

?>
<h1>ceci est front-page.php</h1>
<?php
// La boucle WordPress récupère la page demandée et prépare ses données.
while ( have_posts() ) :
    the_post();
    ?>
    <main id="main" class="site-main">
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <!-- Titre et contenu saisis dans l'administration WordPress. -->
            <h1 class="entry-title"><?php the_title(); ?></h1>

            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </article>
    </main>
    <?php
endwhile;

// Inclut le pied de page commun du thème.
get_footer();