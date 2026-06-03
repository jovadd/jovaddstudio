<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp_ajax_js_rename_theme', 'js_ajax_rename_theme' );

function js_ajax_rename_theme(): void {
    check_ajax_referer( 'js_admin_nonce', 'nonce' );

    if ( ! js_is_dev() ) {
        wp_send_json_error( [ 'message' => 'Permesso negato.' ] );
    }

    $new_name = sanitize_text_field( wp_unslash( $_POST['theme_name'] ?? '' ) );

    if ( ! $new_name ) {
        wp_send_json_error( [ 'message' => 'Inserisci un nome valido.' ] );
    }

    $style_path = get_template_directory() . '/style.css';
    $content    = file_get_contents( $style_path );

    if ( $content === false ) {
        wp_send_json_error( [ 'message' => 'Impossibile leggere style.css.' ] );
    }

    // Aggiorna Theme Name e Description nel header di style.css
    $content = preg_replace(
        '/^(Theme Name:\s*).*$/m',
        '${1}' . $new_name,
        $content,
        1
    );
    $content = preg_replace(
        '/^(Description:\s*).*$/m',
        '${1}Tema WordPress personalizzato creato da Jovadd per ' . $new_name . '.',
        $content,
        1
    );

    global $wp_filesystem;
    if ( ! $wp_filesystem ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
    }

    if ( ! $wp_filesystem->put_contents( $style_path, $content, FS_CHMOD_FILE ) ) {
        wp_send_json_error( [ 'message' => 'Impossibile scrivere style.css — verifica i permessi.' ] );
    }

    // Svuota la cache del tema di WP
    delete_transient( 'theme_roots' );
    wp_clean_themes_cache();

    wp_send_json_success( [ 'name' => $new_name ] );
}
