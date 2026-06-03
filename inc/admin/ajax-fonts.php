<?php
defined( 'ABSPATH' ) || exit;

/* ----------------------------------------------------------
   AJAX: Download font da Google Fonts
   ---------------------------------------------------------- */
add_action( 'wp_ajax_js_download_font', 'js_ajax_download_font' );

function js_ajax_download_font(): void {
    check_ajax_referer( 'js_admin_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => __( 'Permesso negato.', 'jovaddstudio' ) ] );
    }

    $font_family = sanitize_text_field( wp_unslash( $_POST['font_family'] ?? '' ) );
    $font_role   = sanitize_key( $_POST['font_role'] ?? 'heading' );
    $weights_raw = sanitize_text_field( wp_unslash( $_POST['weights'] ?? '["400"]' ) );
    $weights     = json_decode( $weights_raw, true );

    if ( ! $font_family ) {
        wp_send_json_error( [ 'message' => __( 'Nome font mancante.', 'jovaddstudio' ) ] );
    }

    if ( ! in_array( $font_role, [ 'heading', 'body', 'mono' ], true ) ) {
        $font_role = 'heading';
    }

    $valid_weights = [ '100','200','300','400','500','600','700','800','900' ];
    $weights       = array_values( array_filter( (array) $weights, fn( $w ) => in_array( (string) $w, $valid_weights, true ) ) );
    if ( empty( $weights ) ) $weights = [ '400' ];

    // Build Google Fonts CSS2 API URL
    $family_param = str_replace( ' ', '+', $font_family );
    $wght_str     = implode( ';', $weights );

    // Body font: include italic axes
    if ( 'body' === $font_role ) {
        $api_url = "https://fonts.googleapis.com/css2?family={$family_param}:ital,wght@0,{$wght_str};1,{$wght_str}&display=swap";
    } else {
        $api_url = "https://fonts.googleapis.com/css2?family={$family_param}:wght@{$wght_str}&display=swap";
    }

    // Fetch CSS — Chrome UA required for woff2 format
    $response = wp_remote_get( $api_url, [
        'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
        'timeout'    => 20,
    ] );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( [ 'message' => __( 'Errore connessione Google Fonts: ', 'jovaddstudio' ) . $response->get_error_message() ] );
    }

    $css_body = wp_remote_retrieve_body( $response );

    if ( empty( $css_body ) || strpos( $css_body, '@font-face' ) === false ) {
        wp_send_json_error( [ 'message' => __( 'Font non trovato o nome errato. Controlla la spelling (es. "Inter", "Poppins").', 'jovaddstudio' ) ] );
    }

    $font_faces = js_parse_google_font_css( $css_body );

    if ( empty( $font_faces ) ) {
        wp_send_json_error( [ 'message' => __( 'Impossibile parsare il CSS del font.', 'jovaddstudio' ) ] );
    }

    // Prepare local directory
    $font_slug  = sanitize_title( $font_family );
    $fonts_dir  = get_template_directory()     . '/assets/fonts/' . $font_slug;
    $fonts_uri  = get_template_directory_uri() . '/assets/fonts/' . $font_slug;

    if ( ! is_dir( $fonts_dir ) ) {
        wp_mkdir_p( $fonts_dir );
    }

    global $wp_filesystem;
    if ( ! $wp_filesystem ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
    }

    $local_css  = '';
    $downloaded = 0;

    foreach ( $font_faces as $face ) {
        if ( empty( $face['src_url'] ) ) continue;

        $weight   = preg_replace( '/\s+/', '', $face['weight'] ?? '400' );
        $style    = $face['style'] ?? 'normal';
        $range_id = substr( md5( $face['unicode_range'] ?? '' ), 0, 8 );
        $filename = "{$font_slug}-{$weight}-{$style}-{$range_id}.woff2";
        $filepath = $fonts_dir . '/' . $filename;

        if ( ! file_exists( $filepath ) ) {
            $dl = wp_remote_get( $face['src_url'], [ 'timeout' => 30 ] );

            if ( is_wp_error( $dl ) || 200 !== wp_remote_retrieve_response_code( $dl ) ) {
                continue;
            }

            $wp_filesystem->put_contents( $filepath, wp_remote_retrieve_body( $dl ), FS_CHMOD_FILE );
        }

        if ( ! file_exists( $filepath ) ) continue;

        $local_url  = $fonts_uri . '/' . $filename;
        $family_css = "'{$face['family']}'";
        $uni        = ! empty( $face['unicode_range'] ) ? "  unicode-range: {$face['unicode_range']};\n" : '';

        $local_css .= "@font-face {\n"
            . "  font-family: {$family_css};\n"
            . "  font-style: {$style};\n"
            . "  font-weight: {$weight};\n"
            . "  font-display: swap;\n"
            . $uni
            . "  src: url('{$local_url}') format('woff2');\n"
            . "}\n";

        $downloaded++;
    }

    if ( 0 === $downloaded ) {
        wp_send_json_error( [ 'message' => __( 'Nessun file font scaricato. Controlla la connessione.', 'jovaddstudio' ) ] );
    }

    // Save to options
    $options = get_option( 'jovaddstudio_options', [] );
    $options[ "font_{$font_role}_family" ] = $font_family;
    $options[ "font_{$font_role}_css" ]    = $local_css;
    update_option( 'jovaddstudio_options', $options );

    wp_send_json_success( [
        'message'     => sprintf( __( '%s installato (%d file scaricati).', 'jovaddstudio' ), $font_family, $downloaded ),
        'font_family' => $font_family,
        'font_role'   => $font_role,
        'files'       => $downloaded,
    ] );
}

/* ----------------------------------------------------------
   AJAX: Font list per autocomplete
   ---------------------------------------------------------- */
add_action( 'wp_ajax_js_get_fonts_list', 'js_ajax_get_fonts_list' );

function js_ajax_get_fonts_list(): void {
    check_ajax_referer( 'js_admin_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => __( 'Permesso negato.', 'jovaddstudio' ) ] );
    }

    // Transient cache — 24h
    $cached = get_transient( 'jovaddstudio_gfonts_list' );
    if ( $cached ) {
        wp_send_json_success( [ 'fonts' => $cached ] );
    }

    $api_key = js_get_option( 'fonts_api_key' );
    if ( ! $api_key ) {
        wp_send_json_error( [ 'message' => __( 'Inserisci prima la Google Fonts API Key nella tab Tipografia.', 'jovaddstudio' ) ] );
    }

    $response = wp_remote_get(
        'https://www.googleapis.com/webfonts/v1/webfonts?key=' . urlencode( $api_key ) . '&sort=popularity',
        [ 'timeout' => 15 ]
    );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( [ 'message' => $response->get_error_message() ] );
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( empty( $body['items'] ) ) {
        wp_send_json_error( [ 'message' => __( 'Nessun font trovato. Verifica l\'API Key.', 'jovaddstudio' ) ] );
    }

    $fonts = array_map( fn( $f ) => [
        'family'   => $f['family'],
        'category' => $f['category'] ?? '',
        'variants' => $f['variants'] ?? [],
    ], $body['items'] );

    set_transient( 'jovaddstudio_gfonts_list', $fonts, DAY_IN_SECONDS );

    wp_send_json_success( [ 'fonts' => $fonts ] );
}

/* ----------------------------------------------------------
   AJAX: Rimuovi font installato
   ---------------------------------------------------------- */
add_action( 'wp_ajax_js_remove_font', 'js_ajax_remove_font' );

function js_ajax_remove_font(): void {
    check_ajax_referer( 'js_admin_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => __( 'Permesso negato.', 'jovaddstudio' ) ] );
    }

    $font_role = sanitize_key( $_POST['font_role'] ?? '' );
    if ( ! in_array( $font_role, [ 'heading', 'body', 'mono' ], true ) ) {
        wp_send_json_error( [ 'message' => __( 'Ruolo non valido.', 'jovaddstudio' ) ] );
    }

    $options = get_option( 'jovaddstudio_options', [] );
    unset( $options[ "font_{$font_role}_family" ], $options[ "font_{$font_role}_css" ] );
    update_option( 'jovaddstudio_options', $options );

    wp_send_json_success();
}

/* ----------------------------------------------------------
   Helper: parse @font-face blocks da CSS Google Fonts
   ---------------------------------------------------------- */
function js_parse_google_font_css( string $css ): array {
    $faces = [];
    preg_match_all( '/@font-face\s*\{([^}]+)\}/s', $css, $matches );

    foreach ( $matches[1] as $block ) {
        $face = [];

        if ( preg_match( '/font-family:\s*[\'"]?([^\'";\n]+)[\'"]?\s*;/i', $block, $m ) ) {
            $face['family'] = trim( $m[1], "' \t\n\r" );
        }
        if ( preg_match( '/font-style:\s*([^;]+);/i', $block, $m ) ) {
            $face['style'] = trim( $m[1] );
        }
        if ( preg_match( '/font-weight:\s*([^;]+);/i', $block, $m ) ) {
            $face['weight'] = trim( $m[1] );
        }
        if ( preg_match( '/unicode-range:\s*([^;]+);/i', $block, $m ) ) {
            $face['unicode_range'] = trim( $m[1] );
        }
        // woff2 URL — two possible formats
        if ( preg_match( '/url\([\'"]?([^\'")]+\.woff2)[\'"]?\)\s+format\([\'"]woff2[\'"]\)/i', $block, $m ) ) {
            $face['src_url'] = $m[1];
        } elseif ( preg_match( '/url\([\'"]?([^\'")]+\.woff2)[\'"]?\)/i', $block, $m ) ) {
            $face['src_url'] = $m[1];
        }

        if ( ! empty( $face['src_url'] ) ) {
            $faces[] = $face;
        }
    }

    return $faces;
}
