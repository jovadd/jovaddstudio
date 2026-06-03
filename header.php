<?php
$cta_type  = js_get_option( 'header_cta_type', 'none' );
$cta_label = js_get_option( 'header_cta_label' );

// --- WhatsApp button (type: whatsapp | both) ---
$show_wa = in_array( $cta_type, [ 'whatsapp', 'both' ], true );
$wa_url  = '';
if ( $show_wa ) {
    // Numero da ACF Options — campo condiviso con tutto il sito
    $wa_phone = preg_replace( '/\D/', '', js_acf( 'whatsapp' ) );
    if ( $wa_phone ) {
        $wa_url = 'https://wa.me/' . $wa_phone;
        $wa_msg = js_get_option( 'header_cta_whatsapp_msg' );
        if ( $wa_msg ) {
            $wa_url .= '?text=' . rawurlencode( $wa_msg );
        }
    }
}

// --- Link CTA button (type: link | both) ---
$show_link = in_array( $cta_type, [ 'link', 'both' ], true );
$link_url  = $show_link ? js_get_option( 'header_cta_url' ) : '';
$link_ext  = '1' === js_get_option( 'header_cta_new_tab', '0' );

$has_any_cta = ( $show_wa && $wa_url ) || ( $show_link && $link_url && $cta_label );

// WhatsApp SVG icon (reused below)
$wa_icon = '<svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">'
    . '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>'
    . '</svg>';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="header header-sticky" role="banner">
  <div class="container">
    <div class="header-inner header-inner-end">

      <!-- Logo -->
      <div class="header-logo">
        <?php if ( has_custom_logo() ) : ?>
          <?php the_custom_logo(); ?>
        <?php else : ?>
          <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
            <?php bloginfo( 'name' ); ?>
          </a>
        <?php endif; ?>
      </div>

      <!-- Primary navigation -->
      <nav id="main-nav" class="nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'jovaddstudio' ); ?>">
        <?php
        wp_nav_menu( [
            'theme_location' => 'primary',
            'container'      => false,
            'menu_class'     => 'nav-list',
            'fallback_cb'    => false,
        ] );
        ?>
      </nav>

      <!-- Actions -->
      <div class="header-actions">

        <?php if ( $has_any_cta ) : ?>
          <div class="header-sep" aria-hidden="true"></div>

          <?php if ( $show_link && $link_url && $cta_label ) : ?>
            <a
              href="<?php echo esc_url( $link_url ); ?>"
              class="btn btn-s"
              <?php if ( $link_ext ) echo 'target="_blank" rel="noopener noreferrer"'; ?>
            >
              <?php echo esc_html( $cta_label ); ?>
            </a>
          <?php endif; ?>

          <?php if ( $show_wa && $wa_url ) : ?>
            <?php
            // In "both" mode: icon only. In "whatsapp" only mode: icon + label.
            $wa_is_icon_only = ( 'both' === $cta_type );
            ?>
            <a
              href="<?php echo esc_url( $wa_url ); ?>"
              class="btn btn-s btn-whatsapp"
              target="_blank"
              rel="noopener noreferrer"
              aria-label="WhatsApp"
            >
              <?php echo $wa_icon; ?>
              <?php if ( ! $wa_is_icon_only && $cta_label ) : ?>
                <?php echo esc_html( $cta_label ); ?>
              <?php endif; ?>
            </a>
          <?php endif; ?>

        <?php endif; ?>

        <!-- Hamburger — mobile only -->
        <button
          class="nav-toggle"
          aria-controls="main-nav"
          aria-expanded="false"
          aria-label="<?php esc_attr_e( 'Toggle navigation', 'jovaddstudio' ); ?>"
        >
          <span></span>
          <span></span>
          <span></span>
        </button>

      </div><!-- /.header-actions -->

    </div><!-- /.header-inner -->
  </div><!-- /.container -->
</header>
