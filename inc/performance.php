<?php
defined( 'ABSPATH' ) || exit;

/* ----------------------------------------------------------
   Image optimization on upload
   ---------------------------------------------------------- */

add_filter( 'wp_handle_upload', function ( array $file ): array {
    if ( '1' !== js_get_option( 'perf_img_optimize' ) ) return $file;
    if ( ! isset( $file['file'] ) || ! str_starts_with( $file['type'] ?? '', 'image/' ) ) return $file;
    if ( in_array( $file['type'], [ 'image/svg+xml', 'image/gif' ], true ) ) return $file;

    $path      = $file['file'];
    $max_kb    = (int) js_get_option( 'perf_img_max_kb', 300 );
    $max_width = (int) js_get_option( 'perf_img_max_width', 2000 );
    $to_webp   = '1' === js_get_option( 'perf_img_webp' );

    // Resize if too wide
    $editor = wp_get_image_editor( $path );
    if ( is_wp_error( $editor ) ) return $file;

    $size = $editor->get_size();
    if ( ! empty( $size['width'] ) && $size['width'] > $max_width ) {
        $editor->resize( $max_width, null, false );
    }

    // Compress until under max_kb (quality walk-down: 85 → 70 → 55)
    $qualities = [ 85, 70, 55 ];
    foreach ( $qualities as $quality ) {
        $editor->set_quality( $quality );
        $saved = $editor->save( $path );
        if ( is_wp_error( $saved ) ) break;
        if ( filesize( $path ) <= $max_kb * 1024 ) break;
    }

    // Convert to WebP
    if ( $to_webp && function_exists( 'imagewebp' ) ) {
        $webp_path = preg_replace( '/\.(jpe?g|png)$/i', '.webp', $path );
        $webp_mime = 'image/webp';

        // WP 5.8+ supports WebP natively
        if ( method_exists( $editor, 'save' ) ) {
            $webp_editor = wp_get_image_editor( $path );
            if ( ! is_wp_error( $webp_editor ) ) {
                $webp_editor->set_quality( 82 );
                $webp_saved = $webp_editor->save( $webp_path, $webp_mime );
                if ( ! is_wp_error( $webp_saved ) && file_exists( $webp_path ) ) {
                    // Swap file reference to WebP
                    $file['file'] = $webp_path;
                    $file['url']  = str_replace( basename( $path ), basename( $webp_path ), $file['url'] );
                    $file['type'] = $webp_mime;
                    // Remove original
                    wp_delete_file( $path );
                }
            }
        }
    }

    return $file;
} );

/* Emoji ------------------------------------------------------- */
add_action( 'init', function () {
    if ( '1' !== js_get_option( 'perf_disable_emoji' ) ) return;
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
    add_filter( 'emoji_svg_url', '__return_false' );
} );

/* jQuery Migrate --------------------------------------------- */
add_action( 'wp_default_scripts', function ( $scripts ) {
    if ( '1' !== js_get_option( 'perf_disable_jquery_migrate' ) ) return;
    if ( is_admin() ) return;
    if ( isset( $scripts->registered['jquery'] ) ) {
        $scripts->registered['jquery']->deps = array_diff(
            $scripts->registered['jquery']->deps,
            [ 'jquery-migrate' ]
        );
    }
} );

/* jQuery to footer ------------------------------------------- */
add_action( 'wp_enqueue_scripts', function () {
    if ( '1' !== js_get_option( 'perf_move_jquery_footer' ) ) return;
    if ( is_admin() ) return;
    wp_scripts()->add_data( 'jquery',         'group', 1 );
    wp_scripts()->add_data( 'jquery-core',    'group', 1 );
    wp_scripts()->add_data( 'jquery-migrate', 'group', 1 );
}, 99 );

/* RSS feed links --------------------------------------------- */
add_action( 'init', function () {
    if ( '1' !== js_get_option( 'perf_remove_rss_links' ) ) return;
    remove_action( 'wp_head', 'feed_links',       2 );
    remove_action( 'wp_head', 'feed_links_extra', 3 );
} );

/* Head cleanup (RSD, WLW, shortlink, REST, oEmbed) ----------- */
add_action( 'init', function () {
    if ( '1' === js_get_option( 'perf_remove_rsd' ) )       remove_action( 'wp_head', 'rsd_link' );
    if ( '1' === js_get_option( 'perf_remove_wlw' ) )       remove_action( 'wp_head', 'wlw_manifest_link' );
    if ( '1' === js_get_option( 'perf_remove_shortlink' ) ) remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
    if ( '1' === js_get_option( 'perf_remove_rest_link' ) ) remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
    if ( '1' === js_get_option( 'perf_remove_oembed' ) ) {
        remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
        remove_action( 'wp_head', 'wp_oembed_add_host_js' );
    }
} );

/* Remove ?ver= from CSS/JS ----------------------------------- */
add_filter( 'style_loader_src',  'js_strip_version_query', 999 );
add_filter( 'script_loader_src', 'js_strip_version_query', 999 );
function js_strip_version_query( string $src ): string {
    if ( '1' !== js_get_option( 'perf_remove_version_strings' ) ) return $src;
    return strpos( $src, '?ver=' ) !== false ? remove_query_arg( 'ver', $src ) : $src;
}

/* Gutenberg (editor + all frontend styles) ------------------- */
add_action( 'init', function () {
    if ( '1' !== js_get_option( 'perf_disable_gutenberg' ) ) return;
    add_filter( 'use_block_editor_for_post',      '__return_false', 10 );
    add_filter( 'use_block_editor_for_post_type', '__return_false', 10 );
    remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
    remove_action( 'wp_footer',          'wp_enqueue_global_styles', 1 );
    remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles_custom_css' );
    remove_action( 'wp_enqueue_scripts', 'wp_enqueue_registered_block_scripts_and_styles' );
    remove_action( 'wp_enqueue_scripts', 'wp_enqueue_classic_theme_styles' );
} );

add_action( 'wp_enqueue_scripts', function () {
    if ( '1' !== js_get_option( 'perf_disable_gutenberg' ) ) return;
    wp_dequeue_style( 'wp-block-library' );
    wp_dequeue_style( 'wp-block-library-theme' );
    wp_dequeue_style( 'classic-theme-styles' );
    wp_deregister_style( 'global-styles' );
}, 100 );

// WP 6.x: WP_Block::render() enqueues block styles unconditionally during content rendering.
// A late hook of wp_head then prints them as <style id="wp-block-*-inline-css">.
// Output-buffer the entire wp_head output and strip those tags before they reach the browser.
add_action( 'wp_head', function () {
    if ( '1' !== js_get_option( 'perf_disable_gutenberg' ) ) return;
    ob_start();
}, 0 );
add_action( 'wp_head', function () {
    if ( '1' !== js_get_option( 'perf_disable_gutenberg' ) ) return;
    $html = ob_get_clean();
    if ( false !== $html ) {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo preg_replace( '/<style[^>]+id=["\']wp-block-[^"\']*["\'][^>]*>.*?<\/style>\n?/si', '', $html );
    }
}, PHP_INT_MAX );

// Block styles enqueued during do_blocks() / the_content are printed in footer — strip them there too.
add_action( 'wp_footer', function () {
    if ( '1' !== js_get_option( 'perf_disable_gutenberg' ) ) return;
    global $wp_styles;
    if ( empty( $wp_styles ) ) return;
    foreach ( array_keys( (array) $wp_styles->registered ) as $handle ) {
        if ( str_starts_with( $handle, 'wp-block-' ) ) {
            wp_dequeue_style( $handle );
            wp_deregister_style( $handle );
        }
    }
}, 0 );

/* Block library CSS ------------------------------------------ */
add_action( 'wp_enqueue_scripts', function () {
    if ( '1' !== js_get_option( 'perf_disable_block_library_css' ) ) return;
    wp_dequeue_style( 'wp-block-library' );
    wp_dequeue_style( 'wp-block-library-theme' );
    wp_dequeue_style( 'global-styles' );
}, 100 );

/* Comment reply JS ------------------------------------------- */
add_action( 'wp_enqueue_scripts', function () {
    if ( '1' !== js_get_option( 'perf_disable_comment_reply_js' ) ) return;
    wp_dequeue_script( 'comment-reply' );
}, 100 );

/* Comments sitewide ------------------------------------------ */
add_action( 'init', function () {
    if ( '1' !== js_get_option( 'perf_disable_comments' ) ) return;
    foreach ( get_post_types() as $pt ) {
        if ( post_type_supports( $pt, 'comments' ) ) {
            remove_post_type_support( $pt, 'comments' );
            remove_post_type_support( $pt, 'trackbacks' );
        }
    }
} );

add_action( 'admin_menu', function () {
    if ( '1' !== js_get_option( 'perf_disable_comments' ) ) return;
    remove_menu_page( 'edit-comments.php' );
} );

add_action( 'widgets_init', function () {
    if ( '1' !== js_get_option( 'perf_disable_comments' ) ) return;
    unregister_widget( 'WP_Widget_Recent_Comments' );
}, 11 );

/* Heartbeat -------------------------------------------------- */
add_action( 'init', function () {
    if ( '1' !== js_get_option( 'perf_disable_heartbeat' ) ) return;
    wp_deregister_script( 'heartbeat' );
}, 1 );
