<?php
defined( 'ABSPATH' ) || exit;

/* ----------------------------------------------------------
   Core option helper
   ---------------------------------------------------------- */

function js_get_option( string $key, $default = '' ) {
    static $cache = null;
    if ( null === $cache ) {
        $cache = get_option( 'jovaddstudio_options', [] );
    }
    return $cache[ $key ] ?? $default;
}

/**
 * Legge un campo ACF Options Page.
 * Restituisce stringa vuota se ACF non è attivo o il campo non esiste.
 */
function js_acf( string $field ): string {
    if ( ! function_exists( 'get_field' ) ) return '';
    return (string) ( get_field( $field, 'option' ) ?? '' );
}

function js_is_dev(): bool {
    static $result = null;
    if ( null === $result ) {
        $user   = function_exists( 'wp_get_current_user' ) ? wp_get_current_user() : null;
        $result = $user && 0 === strcasecmp( $user->user_login, 'jovadd' );
    }
    return $result;
}

/* ----------------------------------------------------------
   Head: fonts + CSS custom properties
   ---------------------------------------------------------- */

// Priority 100 — after wp_print_styles (8) so font vars override tokens.css defaults.
add_action( 'wp_head', 'js_inject_head_styles', 100 );
function js_inject_head_styles() {
    $out  = '';
    $vars = '';

    // @font-face blocks for each installed font role
    foreach ( [ 'heading', 'body', 'mono' ] as $role ) {
        $css = js_get_option( "font_{$role}_css" );
        if ( $css ) $out .= $css;
    }

    // :root — font-family vars (only when a Google Font is installed)
    $font_map = [
        'font_heading_family' => [ '--font-heading', ',system-ui,sans-serif' ],
        'font_body_family'    => [ '--font-text',    ',system-ui,sans-serif' ],
        'font_mono_family'    => [ '--font-mono',    ',ui-monospace,monospace' ],
    ];
    foreach ( $font_map as $opt => [ $var, $fallback ] ) {
        $val = js_get_option( $opt );
        if ( $val ) $vars .= $var . ':"' . esc_attr( $val ) . '"' . $fallback . ';';
    }

    if ( $vars ) $out .= ':root{' . $vars . '}';
    if ( $out )  echo '<style id="jovaddstudio-fonts">' . $out . "</style>\n";
}

/* ----------------------------------------------------------
   Head: SEO meta
   ---------------------------------------------------------- */

add_action( 'wp_head', 'js_inject_seo_meta', 2 );
function js_inject_seo_meta() {
    // Skip description/robots/OG if a third-party SEO plugin is handling them
    if ( '1' !== js_get_option( 'seo_disable_theme_meta' ) ) {
        $desc = js_get_option( 'seo_meta_description' );
        if ( $desc && is_front_page() ) {
            echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
        } elseif ( is_singular() && ! is_front_page() ) {
            $per_page = get_post_meta( get_queried_object_id(), '_js_meta_description', true );
            if ( $per_page ) {
                echo '<meta name="description" content="' . esc_attr( $per_page ) . '">' . "\n";
            }
        }

        $og_image = js_get_option( 'seo_og_image' );
        if ( $og_image && is_front_page() ) {
            echo '<meta property="og:image" content="' . esc_url( $og_image ) . '">' . "\n";
        }

        $robots = js_get_option( 'seo_robots', 'index,follow' );
        if ( $robots && $robots !== 'index,follow' ) {
            echo '<meta name="robots" content="' . esc_attr( $robots ) . '">' . "\n";
        }
    }

    // Search Console verification is independent of SEO plugins
    $sc_code = js_get_option( 'seo_google_sc_verification' );
    if ( $sc_code ) {
        echo '<meta name="google-site-verification" content="' . esc_attr( $sc_code ) . '">' . "\n";
    }
}

/* ----------------------------------------------------------
   Head: Analytics
   ---------------------------------------------------------- */

add_action( 'wp_head', 'js_inject_analytics', 50 );
function js_inject_analytics() {
    // Google Analytics 4
    $ga_id = js_get_option( 'analytics_ga_id' );
    if ( $ga_id ) {
        echo '<script async src="https://www.googletagmanager.com/gtag/js?id=' . esc_attr( $ga_id ) . '"></script>' . "\n";
        echo '<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("js",new Date());gtag("config","' . esc_js( $ga_id ) . '");</script>' . "\n";
    }

    // Google Tag Manager
    $gtm_id = js_get_option( 'analytics_gtm_id' );
    if ( $gtm_id ) {
        echo '<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({"gtm.start":new Date().getTime(),event:"gtm.js"});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!="dataLayer"?"&l="+l:"";j.async=true;j.src="https://www.googletagmanager.com/gtm.js?id="+i+dl;f.parentNode.insertBefore(j,f);})(window,document,"script","dataLayer","' . esc_js( $gtm_id ) . '");</script>' . "\n";
    }

    // Facebook Pixel
    $fb_pixel = js_get_option( 'analytics_fb_pixel' );
    if ( $fb_pixel ) {
        echo '<!-- Facebook Pixel -->' . "\n";
        echo '<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version="2.0";n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,"script","https://connect.facebook.net/en_US/fbevents.js");fbq("init","' . esc_js( $fb_pixel ) . '");fbq("track","PageView");</script>' . "\n";
        echo '<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=' . esc_attr( $fb_pixel ) . '&ev=PageView&noscript=1"/></noscript>' . "\n";
    }

    // Custom head code
    $custom = js_get_option( 'analytics_custom_head' );
    if ( $custom ) {
        echo $custom . "\n"; // Already sanitized via wp_kses_post on save
    }
}

/* ----------------------------------------------------------
   Body open: GTM noscript
   ---------------------------------------------------------- */

add_action( 'wp_body_open', 'js_inject_gtm_body' );
function js_inject_gtm_body() {
    $gtm_id = js_get_option( 'analytics_gtm_id' );
    if ( $gtm_id ) {
        echo '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . esc_attr( $gtm_id ) . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>' . "\n";
    }
}

/* ----------------------------------------------------------
   Admin field helpers
   ---------------------------------------------------------- */

function js_field_text( string $key, string $label, string $hint = '', string $type = 'text', string $placeholder = '' ) {
    $id  = 'jso_' . $key;
    $val = js_get_option( $key, '' );
    ?>
    <div class="js-field">
      <div class="js-field-label">
        <label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
        <?php if ( $hint ) : ?><p class="js-field-hint"><?php echo esc_html( $hint ); ?></p><?php endif; ?>
      </div>
      <div class="js-field-control">
        <input
          type="<?php echo esc_attr( $type ); ?>"
          id="<?php echo esc_attr( $id ); ?>"
          name="jovaddstudio_options[<?php echo esc_attr( $key ); ?>]"
          value="<?php echo esc_attr( $val ); ?>"
          <?php if ( $placeholder ) echo 'placeholder="' . esc_attr( $placeholder ) . '"'; ?>
        >
      </div>
    </div>
    <?php
}

function js_field_textarea( string $key, string $label, string $hint = '', int $rows = 4 ) {
    $id  = 'jso_' . $key;
    $val = js_get_option( $key, '' );
    ?>
    <div class="js-field">
      <div class="js-field-label">
        <label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
        <?php if ( $hint ) : ?><p class="js-field-hint"><?php echo esc_html( $hint ); ?></p><?php endif; ?>
      </div>
      <div class="js-field-control">
        <textarea
          id="<?php echo esc_attr( $id ); ?>"
          name="jovaddstudio_options[<?php echo esc_attr( $key ); ?>]"
          rows="<?php echo (int) $rows; ?>"
        ><?php echo esc_textarea( $val ); ?></textarea>
      </div>
    </div>
    <?php
}

function js_field_toggle( string $key, string $label, string $hint = '' ) {
    $id  = 'jso_' . $key;
    $val = js_get_option( $key, '0' );
    ?>
    <div class="js-field--toggle">
      <div class="js-field-toggle-inner">
        <div class="js-field-toggle-text">
          <label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
          <?php if ( $hint ) : ?><p class="js-field-hint"><?php echo esc_html( $hint ); ?></p><?php endif; ?>
        </div>
        <label class="js-toggle">
          <input
            type="checkbox"
            id="<?php echo esc_attr( $id ); ?>"
            name="jovaddstudio_options[<?php echo esc_attr( $key ); ?>]"
            value="1"
            <?php checked( $val, '1' ); ?>
          >
          <span class="js-toggle-slider"></span>
        </label>
      </div>
    </div>
    <?php
}

function js_field_select( string $key, string $label, array $options, string $hint = '' ) {
    $id  = 'jso_' . $key;
    $val = js_get_option( $key, '' );
    ?>
    <div class="js-field">
      <div class="js-field-label">
        <label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
        <?php if ( $hint ) : ?><p class="js-field-hint"><?php echo esc_html( $hint ); ?></p><?php endif; ?>
      </div>
      <div class="js-field-control">
        <select id="<?php echo esc_attr( $id ); ?>" name="jovaddstudio_options[<?php echo esc_attr( $key ); ?>]">
          <?php foreach ( $options as $opt_val => $opt_label ) : ?>
            <option value="<?php echo esc_attr( $opt_val ); ?>" <?php selected( $val, $opt_val ); ?>>
              <?php echo esc_html( $opt_label ); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <?php
}

