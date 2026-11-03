<?php
// Récupère l’identifiant transmis au template et le normalise en entier non négatif.
$photo_id = isset( $args['photo_id'] ) ? absint( $args['photo_id'] ) : 0;

// Charge les champs personnalisés et les taxonomies associés à la photo.
$photo_type = get_field( 'types', $photo_id );
$photo_references = get_field( 'references', $photo_id );
$photo_year = get_field( 'annee', $photo_id );
$photo_categories = get_the_terms( $photo_id, 'categorie' );
$photo_formats = get_the_terms( $photo_id, 'format' );
?>
<div class="photos-informations">
    <h2><?php echo esc_html( get_the_title( $photo_id ) ); ?></h2>

    <?php if ( $photo_references !== null && $photo_references !== '' ) : ?>
        <p>RÉFÉRENCES : <?php echo esc_html( (string) $photo_references ); ?></p>
    <?php endif; ?>

    <?php // Ignore les erreurs WordPress et affiche les noms des termes séparés par des virgules. ?>
    <?php if ( $photo_categories && ! is_wp_error( $photo_categories ) ) : ?>
        <p>CATÉGORIE : <?php echo esc_html( implode( ', ', wp_list_pluck( $photo_categories, 'name' ) ) ); ?></p>
    <?php endif; ?>

    <?php if ( $photo_formats && ! is_wp_error( $photo_formats ) ) : ?>
        <p>FORMAT : <?php echo esc_html( implode( ', ', wp_list_pluck( $photo_formats, 'name' ) ) ); ?></p>
    <?php endif; ?>

    <?php if ( $photo_type !== null && $photo_type !== '' ) : ?>
        <p>TYPE : <?php echo esc_html( (string) $photo_type ); ?></p>
    <?php endif; ?>

    <?php if ( $photo_year !== null && $photo_year !== '' ) : ?>
        <p>ANNÉE : <?php echo esc_html( (string) $photo_year ); ?></p>
    <?php endif; ?>
    
</div>

<?php 
