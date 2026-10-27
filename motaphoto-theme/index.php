<?php
// Inclut l'en-tête commun (doctype, head et début du body).
get_header(); ?>
h1>ceci est index.php</h1>
<main id="main" class="site-main">
<?php
while (have_posts()) : the_post(); ?>
    <article id="content-post-<?php the_ID(); ?>" <?php post_class( 'content-article' ); ?>>
        <div class="content-heading">
            <h1 class="content-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
        </div>

        <div class="entry-content">
            <?php the_content(); ?>
        </div>
    </article>
<?php endwhile; ?>
</main>

<!-- Inclut le pied de page commun et ferme le document HTML. -->
<?php get_footer();
