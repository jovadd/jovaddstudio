<?php defined( 'ABSPATH' ) || exit; ?>

<div class="js-section">
  <h2 class="js-section-title"><?php esc_html_e( 'Manutenzione', 'jovaddstudio' ); ?></h2>

  <?php if ( '1' === js_get_option( 'maintenance_on', '0' ) ) : ?>
    <div class="js-alert js-alert-warning" style="margin-bottom:var(--space-5);">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      <?php esc_html_e( 'Il sito è attualmente in manutenzione — visibile solo agli utenti loggati.', 'jovaddstudio' ); ?>
    </div>
  <?php endif; ?>

  <?php
  js_field_toggle( 'maintenance_on', __( 'Abilita modalità manutenzione', 'jovaddstudio' ), __( 'I visitatori non loggati vedranno la pagina di manutenzione (HTTP 503).', 'jovaddstudio' ) );
  js_field_text( 'maintenance_headline', __( 'Titolo', 'jovaddstudio' ), '', 'text', 'Qualcosa di nuovo sta arrivando' );
  js_field_textarea( 'maintenance_text', __( 'Testo descrittivo', 'jovaddstudio' ), __( 'Opzionale. Mostrato sotto il titolo nella pagina di manutenzione.', 'jovaddstudio' ), 3 );
  ?>
</div>

<div class="js-alert js-alert-info" style="margin-bottom:0;">
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  <?php esc_html_e( 'Dati aziendali (telefono, P.IVA, ragione sociale, social, mail, indirizzo) → gestiti dall\'ACF Options Page del progetto.', 'jovaddstudio' ); ?>
</div>

<div class="js-section">
  <h2 class="js-section-title"><?php esc_html_e( 'Footer', 'jovaddstudio' ); ?></h2>
  <?php
  js_field_toggle( 'footer_all_rights',  __( 'Mostra "Tutti i diritti riservati"', 'jovaddstudio' ) );
  js_field_toggle( 'footer_hide_credits', __( 'Nascondi "Made with ❤️ by Jovadd"', 'jovaddstudio' ) );
  ?>
</div>

<div class="js-section">
  <h2 class="js-section-title"><?php esc_html_e( 'Header CTA', 'jovaddstudio' ); ?></h2>
  <?php
  js_field_select( 'header_cta_type', __( 'Tipo pulsante', 'jovaddstudio' ), [
      'none'      => __( '— Nessuno', 'jovaddstudio' ),
      'link'      => __( 'CTA link', 'jovaddstudio' ),
      'whatsapp'  => __( 'WhatsApp', 'jovaddstudio' ),
      'both'      => __( 'Entrambi (WhatsApp + CTA link)', 'jovaddstudio' ),
  ], __( 'Il numero WhatsApp viene letto dal campo ACF "whatsapp".', 'jovaddstudio' ) );
  js_field_text( 'header_cta_label', __( 'Testo pulsante', 'jovaddstudio' ), '', 'text', 'Contattaci' );
  ?>

  <div class="js-cta-group" data-cta-group="link">
    <?php
    js_field_text( 'header_cta_url', 'URL', '', 'url', 'https://...' );
    js_field_toggle( 'header_cta_new_tab', __( 'Apri in nuova scheda', 'jovaddstudio' ) );
    ?>
  </div>

  <div class="js-cta-group" data-cta-group="whatsapp">
    <?php
    js_field_text( 'header_cta_whatsapp_msg', __( 'Messaggio precompilato', 'jovaddstudio' ), __( 'Opzionale. Numero letto da ACF "whatsapp".', 'jovaddstudio' ), 'text', 'Ciao, vorrei informazioni su...' );
    ?>
  </div>
</div>

<div class="js-section">
  <h2 class="js-section-title"><?php esc_html_e( 'WhatsApp — pulsante fisso mobile', 'jovaddstudio' ); ?></h2>
  <?php
  js_field_toggle( 'wa_float_enable', __( 'Abilita pulsante fisso', 'jovaddstudio' ), __( 'Visibile solo su mobile (≤767px). Numero letto da ACF "whatsapp".', 'jovaddstudio' ) );
  js_field_text( 'wa_float_msg', __( 'Messaggio precompilato', 'jovaddstudio' ), __( 'Opzionale.', 'jovaddstudio' ), 'text', 'Ciao, vorrei informazioni su...' );
  ?>
</div>
