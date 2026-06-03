<?php
defined( 'ABSPATH' ) || exit;

/* ----------------------------------------------------------
   Frontend — intercept non-logged-in visitors
   ---------------------------------------------------------- */

add_action( 'template_redirect', function () {
    if ( '1' !== js_get_option( 'maintenance_on', '0' ) ) return;
    if ( is_user_logged_in() ) return;

    // Never block WP-Cron or REST API calls
    if ( defined( 'DOING_CRON' ) && DOING_CRON ) return;
    if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) return;

    status_header( 503 );
    header( 'Retry-After: 3600' );
    nocache_headers();

    get_template_part( 'maintenance' );
    exit;
}, 1 );

/* ----------------------------------------------------------
   Admin — banner di avviso quando la manutenzione è attiva
   ---------------------------------------------------------- */

add_action( 'admin_notices', function () {
    if ( '1' !== js_get_option( 'maintenance_on', '0' ) ) return;
    $url = admin_url( 'admin.php?page=jovaddstudio#general' );
    ?>
    <div class="notice notice-warning" style="display:flex;align-items:center;gap:12px;padding:10px 14px;">
      <strong><?php esc_html_e( 'Modalità Manutenzione Attiva', 'jovaddstudio' ); ?></strong>
      <span style="color:#6b7280;"><?php esc_html_e( 'Il sito è visibile solo agli utenti loggati.', 'jovaddstudio' ); ?></span>
      <a href="<?php echo esc_url( $url ); ?>" style="margin-left:auto;white-space:nowrap;">
        <?php esc_html_e( 'Disattiva &rarr;', 'jovaddstudio' ); ?>
      </a>
    </div>
    <?php
} );
