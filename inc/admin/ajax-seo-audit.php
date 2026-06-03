<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp_ajax_js_seo_audit', function () {
    check_ajax_referer( 'js_admin_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Permessi insufficienti.' ] );
    }

    $url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
    if ( ! $url || ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
        wp_send_json_error( [ 'message' => 'URL non valido.' ] );
    }

    $response = wp_remote_get( $url, [
        'timeout'   => 20,
        'sslverify' => false,
        'headers'   => [ 'User-Agent' => 'Mozilla/5.0 (compatible; JS-SEO-Audit/1.0)' ],
    ] );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( [ 'message' => $response->get_error_message() ] );
    }

    $code = wp_remote_retrieve_response_code( $response );
    if ( (int) $code !== 200 ) {
        wp_send_json_error( [ 'message' => "HTTP {$code}" ] );
    }

    $html = wp_remote_retrieve_body( $response );
    if ( ! $html ) {
        wp_send_json_error( [ 'message' => 'Risposta vuota.' ] );
    }

    $doc = new DOMDocument();
    libxml_use_internal_errors( true );
    $doc->loadHTML( '<?xml encoding="UTF-8">' . $html );
    libxml_clear_errors();
    $xp = new DOMXPath( $doc );

    /* ── H1 ── */
    $h1_nodes = $xp->query( '//h1' );
    $h1_count = $h1_nodes->length;
    $h1_texts = [];
    foreach ( $h1_nodes as $node ) {
        $t = trim( preg_replace( '/\s+/', ' ', $node->textContent ) );
        if ( $t ) $h1_texts[] = mb_substr( $t, 0, 80 );
    }

    /* ── Heading hierarchy ── */
    $hcounts = [];
    foreach ( range( 2, 6 ) as $lvl ) {
        $n = $xp->query( '//h' . $lvl )->length;
        if ( $n ) $hcounts[ 'h' . $lvl ] = $n;
    }

    $hierarchy_issues = [];
    $prev = 1;
    foreach ( range( 2, 6 ) as $lvl ) {
        if ( isset( $hcounts[ 'h' . $lvl ] ) ) {
            if ( $lvl > $prev + 1 ) {
                $hierarchy_issues[] = "Salto H{$prev} → H{$lvl}";
            }
            $prev = $lvl;
        }
    }

    /* ── Images alt ── */
    $all_imgs    = $xp->query( '//img' );
    $imgs_total  = $all_imgs->length;
    $imgs_no_alt = 0;
    foreach ( $all_imgs as $img ) {
        if ( ! $img->hasAttribute( 'alt' ) ) $imgs_no_alt++;
    }

    /* ── Meta description ── */
    $meta_desc_nodes = $xp->query( '//meta[@name="description"]/@content' );
    $desc_val        = $meta_desc_nodes->length ? trim( $meta_desc_nodes->item(0)->nodeValue ) : '';
    $desc_len        = mb_strlen( $desc_val );

    /* ── Title tag ── */
    $title_nodes = $xp->query( '//title' );
    $title_val   = $title_nodes->length ? trim( $title_nodes->item(0)->textContent ) : '';
    $title_len   = mb_strlen( $title_val );

    /* ── Canonical ── */
    $canonical_nodes = $xp->query( '//link[@rel="canonical"]/@href' );
    $canonical_val   = $canonical_nodes->length ? trim( $canonical_nodes->item(0)->nodeValue ) : '';

    /* ── Open Graph ── */
    $og_map = [ 'og:title', 'og:description', 'og:image', 'og:type' ];
    $og     = [];
    foreach ( $og_map as $prop ) {
        $nodes = $xp->query( '//meta[@property="' . $prop . '"]/@content' );
        $og[ $prop ] = $nodes->length ? trim( $nodes->item(0)->nodeValue ) : '';
    }

    /* ── Semantic tags ── */
    $semantic_tags = [ 'main', 'article', 'nav', 'header', 'footer', 'section', 'aside' ];
    $semantic      = [];
    foreach ( $semantic_tags as $tag ) {
        $n = $xp->query( '//' . $tag )->length;
        if ( $n ) $semantic[ $tag ] = $n;
    }

    /* ── Structured data ── */
    $ld_nodes    = $xp->query( '//script[@type="application/ld+json"]' );
    $ld_count    = $ld_nodes->length;

    /* ── Assemble result ── */
    $r = [];

    // Title
    $r['title'] = [
        'value'   => $title_val ? ( mb_strlen( $title_val ) > 70 ? mb_substr( $title_val, 0, 67 ) . '…' : $title_val ) : '',
        'length'  => $title_len,
        'status'  => ! $title_val ? 'error' : ( $title_len < 10 || $title_len > 70 ? 'warning' : 'ok' ),
        'message' => ! $title_val
            ? 'Tag <title> assente'
            : ( $title_len < 10 ? "Troppo corto ({$title_len} car.)" : ( $title_len > 70 ? "Troppo lungo ({$title_len} car., max 70)" : "Lunghezza ottimale ({$title_len} car.)" ) ),
    ];

    // H1
    $r['h1'] = [
        'count'   => $h1_count,
        'texts'   => $h1_texts,
        'status'  => $h1_count === 1 ? 'ok' : ( $h1_count === 0 ? 'error' : 'warning' ),
        'message' => $h1_count === 0 ? 'Nessun H1 trovato' : ( $h1_count === 1 ? 'H1 presente' : "{$h1_count} H1 trovati (uno solo raccomandato)" ),
    ];

    // Headings
    $r['headings'] = [
        'counts'  => $hcounts,
        'issues'  => $hierarchy_issues,
        'status'  => empty( $hierarchy_issues ) ? 'ok' : 'warning',
        'message' => empty( $hierarchy_issues ) ? 'Gerarchia heading corretta' : implode( ', ', $hierarchy_issues ),
    ];

    // Images
    $r['images'] = [
        'total'   => $imgs_total,
        'no_alt'  => $imgs_no_alt,
        'status'  => $imgs_no_alt === 0 ? 'ok' : 'error',
        'message' => $imgs_no_alt === 0
            ? "Tutte le {$imgs_total} immagini hanno alt"
            : "{$imgs_no_alt} immagini su {$imgs_total} senza attributo alt",
    ];

    // Meta description
    $r['meta_description'] = [
        'value'   => $desc_val ? ( $desc_len > 80 ? mb_substr( $desc_val, 0, 77 ) . '…' : $desc_val ) : '',
        'length'  => $desc_len,
        'status'  => ! $desc_val ? 'error' : ( $desc_len < 50 || $desc_len > 160 ? 'warning' : 'ok' ),
        'message' => ! $desc_val
            ? 'Meta description assente'
            : ( $desc_len < 50 ? "Troppo corta ({$desc_len} car., min 50)" : ( $desc_len > 160 ? "Troppo lunga ({$desc_len} car., max 160)" : "Lunghezza ottimale ({$desc_len} car.)" ) ),
    ];

    // Canonical
    $r['canonical'] = [
        'value'   => $canonical_val,
        'status'  => $canonical_val ? 'ok' : 'warning',
        'message' => $canonical_val ? 'Presente' : 'Canonical assente',
    ];

    // Open Graph
    $og_missing = array_keys( array_filter( $og, fn( $v ) => $v === '' ) );
    $r['og'] = [
        'data'    => $og,
        'missing' => $og_missing,
        'status'  => empty( $og_missing ) ? 'ok' : ( count( $og_missing ) >= 3 ? 'warning' : 'warning' ),
        'message' => empty( $og_missing ) ? 'Tutti i tag OG presenti' : 'Mancanti: ' . implode( ', ', $og_missing ),
    ];

    // Semantic
    $r['semantic'] = [
        'found'   => $semantic,
        'status'  => isset( $semantic['main'] ) ? 'ok' : 'warning',
        'message' => isset( $semantic['main'] ) ? 'Tag semantici presenti' : 'Tag <main> assente',
    ];

    // Structured data
    $r['structured_data'] = [
        'count'   => $ld_count,
        'status'  => $ld_count > 0 ? 'ok' : 'info',
        'message' => $ld_count > 0 ? "{$ld_count} blocco/i JSON-LD trovato/i" : 'Nessun JSON-LD',
    ];

    wp_send_json_success( [ 'results' => $r ] );
} );
