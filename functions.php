<?php
defined( 'ABSPATH' ) || exit;

/* ----------------------------------------------------------
   Theme setup
   ---------------------------------------------------------- */
add_action( 'after_setup_theme', function () {
    load_theme_textdomain( 'jovaddstudio', get_template_directory() . '/languages' );

    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-logo' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'script', 'style' ] );
    add_theme_support( 'editor-styles' );
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'align-wide' );

    register_nav_menus( [
        'primary' => __( 'Primary Navigation', 'jovaddstudio' ),
        'footer'  => __( 'Footer Navigation',  'jovaddstudio' ),
    ] );
} );

/* ----------------------------------------------------------
   Enqueue assets
   ---------------------------------------------------------- */
add_action( 'wp_enqueue_scripts', function () {
    $v   = wp_get_theme()->get( 'Version' );
    $uri = get_template_directory_uri();

    wp_enqueue_style( 'js-tokens',     $uri . '/assets/css/tokens.css',     [],                $v );
    wp_enqueue_style( 'js-framework',  $uri . '/assets/css/framework.css',  [ 'js-tokens' ],   $v );
    wp_enqueue_style( 'js-components', $uri . '/assets/css/components.css', [ 'js-framework' ], $v );

    wp_enqueue_script( 'js-main', $uri . '/assets/js/main.js', [], $v, true );
} );

/* ----------------------------------------------------------
   Include modules
   ---------------------------------------------------------- */
foreach ( [
    'inc/helpers.php',           // js_get_option() + head injection
    'inc/breadcrumbs.php',       // js_breadcrumbs() — HTML + JSON-LD
    'inc/maintenance.php',       // maintenance mode (frontend intercept + admin notice)
    'inc/performance.php',       // performance hooks (frontend)
    'inc/security.php',          // security hooks (global)
    'inc/admin/options-register.php',  // register_setting + sanitize
    'inc/admin/options-assets.php',    // admin CSS/JS enqueue
    'inc/admin/ajax-fonts.php',        // AJAX: Google Fonts download
    'inc/admin/ajax-rename-theme.php', // AJAX: rinomina tema (aggiorna style.css)
    'inc/admin/ajax-seo-audit.php',         // AJAX: SEO audit on-demand
    'inc/admin/ajax-accessibility-audit.php', // AJAX: accessibility audit on-demand
    'inc/admin/ajax-meta-description.php',    // AJAX: salva meta description per-pagina
    'inc/admin/duplicate-post.php',          // duplica articoli e pagine
    'inc/admin/options-page.php',      // admin menu + page render
] as $file ) {
    $path = get_template_directory() . '/' . $file;
    if ( file_exists( $path ) ) {
        require_once $path;
    }
}
