<div class="popup-overlay">
    <div class="popup-contact">
        <h1 class="popup-title">
            <img
                src="<?php echo esc_url( get_theme_file_uri( '/assets/images/Contact-header.png' ) ); ?>"
                alt="<?php esc_attr_e( 'Contact', 'motaphoto-theme' ); ?>"
            >
        </h1>

        <?php
        echo apply_shortcodes(
            '[contact-form-7 id="543a9a2" title="Contact form 1"]'
        );
        ?>
    </div>
</div>
