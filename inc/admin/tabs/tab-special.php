<?php
defined( 'ABSPATH' ) || exit;

$pages = get_pages( [ 'post_status' => 'publish' ] );
$page_options = [ '' => __( '— Seleziona pagina —', 'jovaddstudio' ) ];
foreach ( $pages as $page ) {
    $page_options[ $page->ID ] = $page->post_title;
}
?>

<div class="js-section">
  <h2 class="js-section-title"><?php esc_html_e( 'Pagina 404', 'jovaddstudio' ); ?></h2>
  <?php
  js_field_text( 'special_404_title',    __( 'Titolo',      'jovaddstudio' ), '', 'text', __( 'Pagina non trovata', 'jovaddstudio' ) );
  js_field_textarea( 'special_404_text', __( 'Messaggio',   'jovaddstudio' ), __( 'Testo mostrato sotto il titolo nella pagina 404', 'jovaddstudio' ), 3 );
  js_field_text( 'special_404_cta_text', __( 'CTA testo',   'jovaddstudio' ), '', 'text', __( 'Torna alla home', 'jovaddstudio' ) );
  js_field_text( 'special_404_cta_url',  __( 'CTA URL',     'jovaddstudio' ), '', 'url',  home_url( '/' ) );
  ?>
</div>

<div class="js-section">
  <h2 class="js-section-title"><?php esc_html_e( 'Pagine di sistema', 'jovaddstudio' ); ?></h2>
  <?php
  js_field_select( 'special_privacy_page', __( 'Privacy Policy',   'jovaddstudio' ), $page_options, __( 'Usata in footer, cookie banner e form', 'jovaddstudio' ) );
  js_field_select( 'special_cookie_page',  __( 'Cookie Policy',    'jovaddstudio' ), $page_options, __( 'Usata nel cookie banner e nei riferimenti legali', 'jovaddstudio' ) );
  ?>
</div>
