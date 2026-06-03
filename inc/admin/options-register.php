<?php
defined( 'ABSPATH' ) || exit;

add_action( 'admin_init', function () {
    register_setting( 'jovaddstudio', 'jovaddstudio_options', [
        'sanitize_callback' => 'js_sanitize_options',
        'default'           => [],
    ] );
} );

function js_sanitize_options( $input ): array {
    if ( ! is_array( $input ) ) return [];

    $existing = get_option( 'jovaddstudio_options', [] );

    // Non-dev users: preserve ALL existing settings, update only allowed fields.
    // This prevents any bypass attempt via direct POST.
    if ( ! js_is_dev() ) {
        $clean = $existing;
        if ( isset( $input['wa_float_phone'] ) ) {
            $clean['wa_float_phone'] = sanitize_text_field( $input['wa_float_phone'] );
        }
        if ( isset( $input['wa_float_msg'] ) ) {
            $clean['wa_float_msg'] = sanitize_text_field( $input['wa_float_msg'] );
        }
        return $clean;
    }

    $clean = [];

    // --- Plain text fields ---
    $text_fields = [
        'maintenance_headline',
        'header_cta_label', 'header_cta_url', 'header_cta_whatsapp_msg',
        'wa_float_msg',
        'social_instagram', 'social_linkedin', 'social_behance', 'social_github', 'social_x',
        'fonts_api_key',
        'font_heading_family', 'font_body_family', 'font_mono_family',
        'analytics_ga_id', 'analytics_gtm_id', 'analytics_fb_pixel',
        'seo_meta_description', 'seo_robots', 'seo_google_sc_verification',
        'special_404_title', 'special_404_cta_text', 'special_404_cta_url',
    ];
    foreach ( $text_fields as $key ) {
        if ( isset( $input[ $key ] ) ) {
            $clean[ $key ] = sanitize_text_field( $input[ $key ] );
        }
    }

    // --- URL fields ---
    $url_fields = [ 'seo_og_image', 'social_instagram', 'social_linkedin', 'social_behance', 'social_github', 'social_x' ];
    foreach ( $url_fields as $key ) {
        if ( isset( $input[ $key ] ) ) {
            $clean[ $key ] = esc_url_raw( $input[ $key ] );
        }
    }

    // --- Enum fields (whitelist) ---
    if ( isset( $input['header_cta_type'] ) ) {
        $allowed                  = [ 'none', 'link', 'whatsapp', 'both' ];
        $clean['header_cta_type'] = in_array( $input['header_cta_type'], $allowed, true )
            ? $input['header_cta_type'] : 'none';
    }
    if ( isset( $input['seo_robots'] ) ) {
        $allowed               = [ 'index,follow', 'noindex,follow', 'index,nofollow', 'noindex,nofollow' ];
        $clean['seo_robots']   = in_array( $input['seo_robots'], $allowed, true )
            ? $input['seo_robots'] : 'index,follow';
    }

    // --- Textarea fields (preserve newlines) ---
    foreach ( [ 'maintenance_text', 'special_404_text' ] as $key ) {
        if ( isset( $input[ $key ] ) ) {
            $clean[ $key ] = sanitize_textarea_field( $input[ $key ] );
        }
    }

    // --- Custom code (analytics scripts — allow script tags) ---
    if ( isset( $input['analytics_custom_head'] ) ) {
        $clean['analytics_custom_head'] = wp_kses(
            $input['analytics_custom_head'],
            [ 'script' => [ 'async' => [], 'src' => [], 'type' => [], 'id' => [] ], 'noscript' => [], 'img' => [ 'src' => [], 'height' => [], 'width' => [], 'style' => [], 'alt' => [] ] ]
        );
    }

    // --- Integer (page IDs) ---
    $int_fields = [ 'special_privacy_page', 'special_cookie_page' ];
    foreach ( $int_fields as $key ) {
        if ( isset( $input[ $key ] ) ) {
            $clean[ $key ] = absint( $input[ $key ] );
        }
    }

    // --- Integer fields ---
    $int_fields_extra = [ 'perf_img_max_kb', 'perf_img_max_width' ];
    foreach ( $int_fields_extra as $key ) {
        if ( isset( $input[ $key ] ) ) {
            $clean[ $key ] = absint( $input[ $key ] ) ?: ( $key === 'perf_img_max_kb' ? 300 : 2000 );
        }
    }

    // --- Boolean toggles ---
    $bool_fields = [
        // Performance — images
        'perf_img_optimize', 'perf_img_webp',
        // Performance
        'perf_disable_gutenberg',
        'perf_disable_emoji', 'perf_disable_jquery_migrate', 'perf_move_jquery_footer',
        'perf_remove_rss_links', 'perf_remove_rsd', 'perf_remove_wlw',
        'perf_remove_shortlink', 'perf_remove_rest_link', 'perf_remove_oembed',
        'perf_remove_version_strings', 'perf_disable_block_library_css',
        'perf_disable_comments', 'perf_disable_comment_reply_js', 'perf_disable_heartbeat',
        // Security
        'seo_disable_theme_meta',
        'sec_remove_generator', 'sec_remove_version_query', 'sec_disable_xmlrpc',
        'sec_disable_file_edit', 'sec_disable_login_errors', 'sec_disable_user_enum',
        'sec_disable_feed', 'sec_disable_pingback',
        // Maintenance
        'maintenance_on',
        // Footer
        'footer_all_rights',
        // Header CTA
        'header_cta_new_tab',
        // WhatsApp float
        'wa_float_enable',
        // Footer
        'footer_hide_credits',
    ];
    foreach ( $bool_fields as $key ) {
        $clean[ $key ] = ! empty( $input[ $key ] ) ? '1' : '0';
    }

    // --- Preserve font CSS (managed by AJAX, not form submit) ---
    foreach ( [ 'font_heading_css', 'font_body_css', 'font_mono_css' ] as $key ) {
        if ( isset( $existing[ $key ] ) ) {
            $clean[ $key ] = $existing[ $key ];
        }
    }

    return $clean;
}
