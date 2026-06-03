<?php
defined( 'ABSPATH' ) || exit;

add_action( 'admin_enqueue_scripts', function ( string $hook ) {
    if ( 'toplevel_page_jovaddstudio' !== $hook ) return;

    $v   = wp_get_theme()->get( 'Version' );
    $uri = get_template_directory_uri();

    // Admin styles
    wp_enqueue_style( 'js-admin', $uri . '/assets/css/admin.css', [], $v );

    // Admin JS
    wp_enqueue_script( 'js-admin', $uri . '/assets/js/admin.js', [], $v, true );

    // WP Media uploader (for OG image)
    wp_enqueue_media();

    // Localize with image processing defaults
    wp_localize_script( 'js-admin', 'jsAdminData', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'js_admin_nonce' ),
        'strings' => [
            'downloading'    => __( 'Download in corso…', 'jovaddstudio' ),
            'installed'      => __( 'Installato con successo.', 'jovaddstudio' ),
            'error_network'  => __( 'Errore di rete.', 'jovaddstudio' ),
            'error_empty'    => __( 'Inserisci il nome del font.', 'jovaddstudio' ),
            'select_image'   => __( 'Seleziona immagine', 'jovaddstudio' ),
            'use_image'      => __( 'Usa immagine', 'jovaddstudio' ),
            'audit_running'  => __( 'Analisi…', 'jovaddstudio' ),
            'audit_analyze'  => __( 'Analizza', 'jovaddstudio' ),
            'audit_rerun'    => __( 'Rianalizza', 'jovaddstudio' ),
        ],
    ] );
} );

/* ----------------------------------------------------------
   Admin menu icon — mask-image approach
   Fires on all admin pages so the sidebar icon is always correct.
   currentColor inherits WP admin color scheme (gray → white on hover/active).
   ---------------------------------------------------------- */
add_action( 'admin_head', function () {
    $paths = implode( '', [
        '<path d="M12 2L2 7l10 5 10-5-10-5z" stroke="black" stroke-width="2" stroke-linejoin="round"/>',
        '<path d="M2 17l10 5 10-5" stroke="black" stroke-width="2" stroke-linejoin="round"/>',
        '<path d="M2 12l10 5 10-5" stroke="black" stroke-width="2" stroke-linejoin="round"/>',
    ] );

    $svg = rawurlencode(
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">' . $paths . '</svg>'
    );

    echo '<style id="jovaddstudio-menu-icon">
#adminmenu #toplevel_page_jovaddstudio .wp-menu-image {
    background: transparent !important;
}
#adminmenu #toplevel_page_jovaddstudio .wp-menu-image::before {
    content: \'\' !important;
    display: block;
    width: 20px;
    height: 20px;
    margin: 0 auto;
    background-color: currentColor;
    -webkit-mask-image: url("data:image/svg+xml,' . $svg . '");
    mask-image: url("data:image/svg+xml,' . $svg . '");
    -webkit-mask-repeat: no-repeat;
    mask-repeat: no-repeat;
    -webkit-mask-position: center;
    mask-position: center;
    -webkit-mask-size: 20px 20px;
    mask-size: 20px 20px;
    -webkit-mask-mode: alpha;
    mask-mode: alpha;
}
</style>' . "\n";
} );
