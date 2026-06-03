<?php
defined( 'ABSPATH' ) || exit;

/* Meta generator --------------------------------------------- */
add_filter( 'the_generator', function ( string $gen ): string {
    return '1' === js_get_option( 'sec_remove_generator' ) ? '' : $gen;
} );

/* Versione WP da CSS/JS -------------------------------------- */
add_filter( 'style_loader_src',  'js_remove_wp_ver_query', 999 );
add_filter( 'script_loader_src', 'js_remove_wp_ver_query', 999 );
function js_remove_wp_ver_query( string $src ): string {
    if ( '1' !== js_get_option( 'sec_remove_version_query' ) ) return $src;
    if ( strpos( $src, 'ver=' . get_bloginfo( 'version' ) ) !== false ) {
        return remove_query_arg( 'ver', $src );
    }
    return $src;
}

/* Disabilita file editing ------------------------------------ */
add_action( 'admin_init', function () {
    if ( '1' !== js_get_option( 'sec_disable_file_edit' ) ) return;
    if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
        define( 'DISALLOW_FILE_EDIT', true );
    }
} );

/* Nascondi errori login -------------------------------------- */
add_filter( 'login_errors', function ( string $errors ): string {
    if ( '1' !== js_get_option( 'sec_disable_login_errors' ) ) return $errors;
    return __( 'Credenziali non valide. Riprova.', 'jovaddstudio' );
} );

/* Disabilita XML-RPC ----------------------------------------- */
add_filter( 'xmlrpc_enabled', function ( bool $enabled ): bool {
    return '1' === js_get_option( 'sec_disable_xmlrpc' ) ? false : $enabled;
} );

/* Rimuovi X-Pingback header ---------------------------------- */
add_filter( 'wp_headers', function ( array $headers ): array {
    if ( '1' === js_get_option( 'sec_disable_xmlrpc' ) || '1' === js_get_option( 'sec_disable_pingback' ) ) {
        unset( $headers['X-Pingback'] );
    }
    return $headers;
} );

/* Disabilita pingback XML-RPC method ------------------------- */
add_filter( 'xmlrpc_methods', function ( array $methods ): array {
    if ( '1' !== js_get_option( 'sec_disable_pingback' ) ) return $methods;
    unset( $methods['pingback.ping'] );
    return $methods;
} );

/* User enumeration — REST + ?author= ------------------------- */
add_filter( 'rest_endpoints', function ( array $endpoints ): array {
    if ( '1' !== js_get_option( 'sec_disable_user_enum' ) ) return $endpoints;
    if ( ! current_user_can( 'list_users' ) ) {
        unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
    }
    return $endpoints;
} );

add_action( 'template_redirect', function () {
    if ( '1' !== js_get_option( 'sec_disable_user_enum' ) ) return;
    if ( is_author() || isset( $_GET['author'] ) ) {
        wp_safe_redirect( home_url( '/' ), 301 );
        exit;
    }
} );

/* Disabilita RSS feeds --------------------------------------- */
add_action( 'template_redirect', function () {
    if ( '1' !== js_get_option( 'sec_disable_feed' ) ) return;
    if ( is_feed() ) {
        wp_safe_redirect( home_url( '/' ), 301 );
        exit;
    }
} );
