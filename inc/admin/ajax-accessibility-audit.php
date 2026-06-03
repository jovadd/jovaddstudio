<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp_ajax_js_a11y_audit', function () {
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
        'headers'   => [ 'User-Agent' => 'Mozilla/5.0 (compatible; JS-A11y-Audit/1.0)' ],
    ] );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( [ 'message' => $response->get_error_message() ] );
    }
    if ( (int) wp_remote_retrieve_response_code( $response ) !== 200 ) {
        wp_send_json_error( [ 'message' => 'HTTP ' . wp_remote_retrieve_response_code( $response ) ] );
    }

    $html = wp_remote_retrieve_body( $response );
    if ( ! $html ) wp_send_json_error( [ 'message' => 'Risposta vuota.' ] );

    $doc = new DOMDocument();
    libxml_use_internal_errors( true );
    $doc->loadHTML( '<?xml encoding="UTF-8">' . $html );
    libxml_clear_errors();
    $xp = new DOMXPath( $doc );

    $r = [];

    /* ── 1. lang attribute ── */
    $lang_nodes = $xp->query( '//html/@lang' );
    $lang       = $lang_nodes->length ? trim( $lang_nodes->item(0)->nodeValue ) : '';
    $r['lang']  = [
        'value'   => $lang,
        'status'  => $lang ? 'ok' : 'error',
        'message' => $lang ? "lang=\"{$lang}\"" : 'Attributo lang assente su <html>',
    ];

    /* ── 2. Skip link ── */
    $early_links  = $xp->query( '(//a[starts-with(@href,"#")])[position()<=8]' );
    $has_skip     = false;
    foreach ( $early_links as $link ) {
        $href = strtolower( trim( $link->getAttribute( 'href' ) ) );
        if ( preg_match( '/#(skip|main|content|primary|wrapper)/i', $href ) ) {
            $has_skip = true;
            break;
        }
    }
    $r['skip_link'] = [
        'status'  => $has_skip ? 'ok' : 'info',
        'message' => $has_skip ? 'Skip link trovato' : 'Skip link assente — consigliato per navigazione da tastiera',
    ];

    /* ── 3. Images alt ── */
    $all_imgs       = $xp->query( '//img' );
    $imgs_total     = $all_imgs->length;
    $imgs_no_alt    = 0;
    $imgs_empty_alt = 0;
    foreach ( $all_imgs as $img ) {
        if ( ! $img->hasAttribute( 'alt' ) ) {
            $imgs_no_alt++;
        } elseif ( trim( $img->getAttribute( 'alt' ) ) === '' ) {
            $imgs_empty_alt++;
        }
    }
    $r['images_alt'] = [
        'total'      => $imgs_total,
        'no_alt'     => $imgs_no_alt,
        'decorative' => $imgs_empty_alt,
        'status'     => $imgs_no_alt > 0 ? 'error' : 'ok',
        'message'    => $imgs_no_alt > 0
            ? "{$imgs_no_alt} immagini senza attributo alt"
            : ( $imgs_total > 0 ? "Tutte le {$imgs_total} immagini hanno alt" : 'Nessuna immagine' ),
        'detail'     => $imgs_empty_alt > 0 ? "{$imgs_empty_alt} decorative (alt vuoto — corretto)" : '',
    ];

    /* ── 4. Form labels ── */
    $inputs       = $xp->query( '//input[not(@type="hidden") and not(@type="submit") and not(@type="button") and not(@type="reset") and not(@type="image")] | //select | //textarea' );
    $inputs_total = $inputs->length;
    $inputs_bad   = 0;
    foreach ( $inputs as $input ) {
        if ( $input->getAttribute( 'aria-label' ) || $input->getAttribute( 'aria-labelledby' ) || $input->getAttribute( 'title' ) ) continue;
        $id = $input->getAttribute( 'id' );
        if ( $id && $xp->query( '//label[@for="' . addslashes( $id ) . '"]' )->length ) continue;
        $parent = $input->parentNode;
        $in_label = false;
        while ( $parent ) {
            if ( $parent->nodeName === 'label' ) { $in_label = true; break; }
            $parent = $parent->parentNode ?? null;
        }
        if ( ! $in_label ) $inputs_bad++;
    }
    $r['form_labels'] = [
        'total'   => $inputs_total,
        'bad'     => $inputs_bad,
        'status'  => $inputs_bad > 0 ? 'error' : ( $inputs_total > 0 ? 'ok' : 'info' ),
        'message' => $inputs_total === 0
            ? 'Nessun campo modulo'
            : ( $inputs_bad > 0 ? "{$inputs_bad} campi senza label accessibile" : "Tutti i {$inputs_total} campi hanno label" ),
    ];

    /* ── 5. Buttons without accessible text ── */
    $buttons       = $xp->query( '//button | //*[@role="button"]' );
    $buttons_total = $buttons->length;
    $buttons_bad   = 0;
    foreach ( $buttons as $btn ) {
        $text  = trim( $btn->textContent );
        $label = trim( $btn->getAttribute( 'aria-label' ) );
        $lby   = trim( $btn->getAttribute( 'aria-labelledby' ) );
        $title = trim( $btn->getAttribute( 'title' ) );
        $img_alt = $xp->query( './/img[@alt and string-length(normalize-space(@alt))>0]', $btn )->length > 0;
        if ( ! $text && ! $label && ! $lby && ! $title && ! $img_alt ) $buttons_bad++;
    }
    $r['buttons'] = [
        'total'   => $buttons_total,
        'bad'     => $buttons_bad,
        'status'  => $buttons_bad > 0 ? 'error' : ( $buttons_total > 0 ? 'ok' : 'info' ),
        'message' => $buttons_total === 0
            ? 'Nessun bottone'
            : ( $buttons_bad > 0 ? "{$buttons_bad} bottoni senza testo accessibile" : "Tutti i {$buttons_total} bottoni hanno testo" ),
    ];

    /* ── 6. Links ── */
    $bad_patterns  = [ 'clicca qui', 'click here', 'qui', 'here', 'leggi', 'leggi di più', 'leggi di piu', 'scopri', 'scopri di più', 'vai', 'link', 'more', 'di più', 'vedi', 'apri', 'vai al link' ];
    $all_links     = $xp->query( '//a[@href]' );
    $links_total   = $all_links->length;
    $links_bad     = 0;
    $links_empty   = 0;
    foreach ( $all_links as $link ) {
        $text  = trim( strtolower( preg_replace( '/\s+/', ' ', $link->textContent ) ) );
        $label = trim( $link->getAttribute( 'aria-label' ) );
        $title = trim( $link->getAttribute( 'title' ) );
        if ( ! $text && ! $label && ! $title ) {
            if ( ! $xp->query( './/img[@alt and string-length(normalize-space(@alt))>0]', $link )->length ) $links_empty++;
        } elseif ( ! $label && ! $title && in_array( $text, $bad_patterns, true ) ) {
            $links_bad++;
        }
    }
    $r['links'] = [
        'total'   => $links_total,
        'bad'     => $links_bad,
        'empty'   => $links_empty,
        'status'  => ( $links_bad > 0 || $links_empty > 0 ) ? 'warning' : 'ok',
        'message' => ( $links_bad === 0 && $links_empty === 0 )
            ? 'Tutti i link hanno testo descrittivo'
            : implode( '; ', array_filter( [
                $links_empty > 0 ? "{$links_empty} link vuoti" : '',
                $links_bad   > 0 ? "{$links_bad} link non descrittivi (\"qui\", \"leggi\", ecc.)" : '',
            ] ) ),
    ];

    /* ── 7. iframes ── */
    $iframes       = $xp->query( '//iframe' );
    $iframes_total = $iframes->length;
    $iframes_bad   = 0;
    foreach ( $iframes as $f ) {
        if ( ! trim( $f->getAttribute( 'title' ) ) ) $iframes_bad++;
    }
    $r['iframes'] = [
        'total'   => $iframes_total,
        'bad'     => $iframes_bad,
        'status'  => $iframes_bad > 0 ? 'error' : ( $iframes_total > 0 ? 'ok' : 'info' ),
        'message' => $iframes_total === 0
            ? 'Nessun iframe'
            : ( $iframes_bad > 0 ? "{$iframes_bad} iframe senza attributo title" : 'Tutti gli iframe hanno title' ),
    ];

    /* ── 8. Video captions ── */
    $videos       = $xp->query( '//video' );
    $videos_total = $videos->length;
    $videos_bad   = 0;
    foreach ( $videos as $v ) {
        if ( ! $xp->query( './/track[@kind="captions" or @kind="subtitles"]', $v )->length ) $videos_bad++;
    }
    $r['videos'] = [
        'total'   => $videos_total,
        'bad'     => $videos_bad,
        'status'  => $videos_bad > 0 ? 'warning' : ( $videos_total > 0 ? 'ok' : 'info' ),
        'message' => $videos_total === 0
            ? 'Nessun video'
            : ( $videos_bad > 0 ? "{$videos_bad} video senza caption/sottotitoli" : 'Tutti i video hanno caption' ),
    ];

    /* ── 9. Positive tabindex ── */
    $tab_nodes  = $xp->query( '//*[@tabindex]' );
    $tab_bad    = 0;
    foreach ( $tab_nodes as $n ) {
        if ( (int) $n->getAttribute( 'tabindex' ) > 0 ) $tab_bad++;
    }
    $r['tabindex'] = [
        'count'   => $tab_bad,
        'status'  => $tab_bad > 0 ? 'warning' : 'ok',
        'message' => $tab_bad > 0
            ? "{$tab_bad} elementi con tabindex positivo (altera ordine focus da tastiera)"
            : 'Nessun tabindex positivo',
    ];

    /* ── 10. Tables ── */
    $tables       = $xp->query( '//table' );
    $tables_total = $tables->length;
    $tables_bad   = 0;
    foreach ( $tables as $t ) {
        $has_th      = $xp->query( './/th', $t )->length > 0;
        $has_caption = $xp->query( './/caption', $t )->length > 0;
        $has_scope   = $xp->query( './/th[@scope]', $t )->length > 0;
        $has_label   = $t->getAttribute( 'aria-label' ) || $t->getAttribute( 'aria-labelledby' );
        if ( $has_th && ( ( ! $has_caption && ! $has_label ) || ! $has_scope ) ) $tables_bad++;
    }
    $r['tables'] = [
        'total'   => $tables_total,
        'bad'     => $tables_bad,
        'status'  => $tables_bad > 0 ? 'warning' : ( $tables_total > 0 ? 'ok' : 'info' ),
        'message' => $tables_total === 0
            ? 'Nessuna tabella'
            : ( $tables_bad > 0 ? "{$tables_bad} tabelle senza caption o scope sulle intestazioni" : 'Tabelle accessibili' ),
    ];

    /* ── 11. Color contrast ── */
    $contrast_issues = [];
    $contrast_ok     = 0;

    // Inline styles
    foreach ( $xp->query( '//*[@style]' ) as $el ) {
        $style = $el->getAttribute( 'style' );
        $color = js_a11y_parse_inline_color( $style, 'color' );
        $bg    = js_a11y_parse_inline_color( $style, 'background(?:-color)?' );
        if ( $color && $bg ) {
            $ratio = js_a11y_contrast_ratio( $color, $bg );
            if ( $ratio !== null ) {
                $tag  = $el->nodeName;
                $text = mb_substr( trim( preg_replace( '/\s+/', ' ', $el->textContent ) ), 0, 35 );
                if ( $ratio < 4.5 ) {
                    $contrast_issues[] = [ 'tag' => $tag, 'text' => $text, 'ratio' => round( $ratio, 2 ), 'source' => 'inline' ];
                } else {
                    $contrast_ok++;
                }
            }
        }
    }

    // Main theme CSS (first non-core stylesheet)
    $css_pairs = js_a11y_extract_css_colors( $xp );

    $css_fails = array_filter( $css_pairs, fn( $p ) => $p['ratio'] < 4.5 );
    $css_ok    = array_filter( $css_pairs, fn( $p ) => $p['ratio'] >= 4.5 );

    if ( ! empty( $css_fails ) ) {
        foreach ( $css_fails as $p ) {
            $contrast_issues[] = [ 'tag' => $p['selector'], 'text' => '', 'ratio' => round( $p['ratio'], 2 ), 'source' => 'css' ];
        }
    }

    $all_ok = $contrast_ok + count( $css_ok );

    $r['contrast'] = [
        'issues'     => $contrast_issues,
        'ok'         => $all_ok,
        'css_checked'=> count( $css_pairs ),
        'status'     => ! empty( $contrast_issues ) ? 'error' : ( count( $css_pairs ) > 0 || $contrast_ok > 0 ? 'ok' : 'info' ),
        'message'    => ! empty( $contrast_issues )
            ? count( $contrast_issues ) . ' problemi di contrasto rilevati'
            : ( $all_ok > 0
                ? "{$all_ok} combinazioni colore verificate — tutto ok"
                : 'Nessuno stile colore inline trovato — verifica manuale CSS' ),
        'note'       => count( $css_pairs ) === 0 && $contrast_ok === 0
            ? 'I colori definiti nei CSS file con variabili CSS (var(--...)) non sono verificabili automaticamente.'
            : '',
    ];

    wp_send_json_success( [ 'results' => $r ] );
} );

/* ── Color contrast helpers ── */

function js_a11y_parse_inline_color( string $style, string $prop ): ?string {
    if ( ! preg_match( '/' . $prop . '\s*:\s*([^;]+)/i', $style, $m ) ) return null;
    return js_a11y_normalize_color( trim( $m[1] ) );
}

function js_a11y_normalize_color( string $val ): ?string {
    $val = strtolower( trim( $val ) );
    if ( preg_match( '/^#([0-9a-f]{3,6})$/i', $val, $m ) ) {
        $h = $m[1];
        if ( strlen( $h ) === 3 ) $h = $h[0].$h[0].$h[1].$h[1].$h[2].$h[2];
        return strlen( $h ) === 6 ? '#' . $h : null;
    }
    if ( preg_match( '/^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/i', $val, $m ) ) {
        return sprintf( '#%02x%02x%02x', (int) $m[1], (int) $m[2], (int) $m[3] );
    }
    $named = [
        'white' => '#ffffff', 'black' => '#000000', 'red' => '#ff0000',
        'blue'  => '#0000ff', 'green' => '#008000', 'yellow' => '#ffff00',
        'gray'  => '#808080', 'grey'  => '#808080', 'silver' => '#c0c0c0',
        'navy'  => '#000080', 'teal'  => '#008080', 'maroon' => '#800000',
    ];
    return $named[ $val ] ?? null;
}

function js_a11y_luminance( string $hex ): float {
    $hex = ltrim( $hex, '#' );
    $ch  = [ hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ) ];
    $l   = 0.0;
    foreach ( [ 0.2126, 0.7152, 0.0722 ] as $i => $w ) {
        $v  = $ch[ $i ] / 255;
        $l += $w * ( $v <= 0.04045 ? $v / 12.92 : pow( ( $v + 0.055 ) / 1.055, 2.4 ) );
    }
    return $l;
}

function js_a11y_contrast_ratio( string $c1, string $c2 ): ?float {
    if ( strlen( $c1 ) !== 7 || strlen( $c2 ) !== 7 ) return null;
    $l1 = js_a11y_luminance( $c1 );
    $l2 = js_a11y_luminance( $c2 );
    return ( max( $l1, $l2 ) + 0.05 ) / ( min( $l1, $l2 ) + 0.05 );
}

function js_a11y_extract_css_colors( DOMXPath $xp ): array {
    $results = [];
    $links   = $xp->query( '//link[@rel="stylesheet"]/@href' );

    foreach ( $links as $link ) {
        $href = $link->nodeValue;
        if ( strpos( $href, 'wp-includes' ) !== false ) continue;
        if ( ! filter_var( $href, FILTER_VALIDATE_URL ) ) continue;
        if ( preg_match( '#fonts\.googleapis|fonts\.gstatic|cdn\.#', $href ) ) continue;

        $css_res = wp_remote_get( $href, [ 'timeout' => 10, 'sslverify' => false ] );
        if ( is_wp_error( $css_res ) ) continue;
        $css = wp_remote_retrieve_body( $css_res );
        if ( ! $css || strpos( $css, 'var(' ) !== false ) continue; // skip CSS-variable-heavy files

        // Parse selector blocks: selector { ... color: ...; background: ...; }
        preg_match_all( '/([a-z0-9\s,>:.#*\[\]="\'_-]+)\{([^}]+)\}/i', $css, $blocks, PREG_SET_ORDER );
        foreach ( $blocks as $block ) {
            $sel   = trim( preg_replace( '/\s+/', ' ', $block[1] ) );
            $decls = $block[2];
            $color = js_a11y_parse_inline_color( $decls, 'color' );
            $bg    = js_a11y_parse_inline_color( $decls, 'background(?:-color)?' );
            if ( $color && $bg ) {
                $ratio = js_a11y_contrast_ratio( $color, $bg );
                if ( $ratio !== null ) {
                    $results[] = [ 'selector' => mb_substr( $sel, 0, 40 ), 'color' => $color, 'bg' => $bg, 'ratio' => $ratio ];
                }
            }
        }
        break; // first matching stylesheet only
    }
    return $results;
}
