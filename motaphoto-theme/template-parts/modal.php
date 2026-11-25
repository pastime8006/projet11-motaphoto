<div
    class="popup-overlay"
    role="dialog"
    aria-modal="true"
    aria-labelledby="popup-title"
>
    <div class="popup-contact">
        <h1 id="popup-title" class="popup-title">
            <img
                src="<?php echo esc_url( get_theme_file_uri( '/assets/images/Contact-header.png' ) ); ?>"
                alt="<?php esc_attr_e( 'Contact', 'motaphoto-theme' ); ?>"
            >
        </h1>

        <?php
        echo do_shortcode(
            '[contact-form-7 id="543a9a2" title="Contact form 1"]'
        );
        ?>
    </div>
</div>
