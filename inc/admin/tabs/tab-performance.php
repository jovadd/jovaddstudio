<?php defined( 'ABSPATH' ) || exit; ?>

<!-- ── Immagini ── -->
<div class="js-section">
  <h2 class="js-section-title">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
    <?php esc_html_e( 'Ottimizzazione immagini', 'jovaddstudio' ); ?>
  </h2>
  <?php
  js_field_toggle(
      'perf_img_optimize',
      __( 'Ottimizza immagini al caricamento', 'jovaddstudio' ),
      __( 'Ridimensiona automaticamente i file troppo grandi e applica compressione lossy via GD/Imagick.', 'jovaddstudio' )
  );
  js_field_text(
      'perf_img_max_kb',
      __( 'Peso massimo (KB)', 'jovaddstudio' ),
      __( 'Immagini più pesanti di questo limite vengono ricompresse. Default: 300 KB.', 'jovaddstudio' ),
      'number',
      '300'
  );
  js_field_text(
      'perf_img_max_width',
      __( 'Larghezza massima (px)', 'jovaddstudio' ),
      __( 'Immagini più larghe vengono ridimensionate mantenendo le proporzioni. Default: 2000 px.', 'jovaddstudio' ),
      'number',
      '2000'
  );
  js_field_toggle(
      'perf_img_webp',
      __( 'Converti in WebP', 'jovaddstudio' ),
      __( 'Richiede PHP 8.1+ con GD o Imagick. La versione originale viene conservata come fallback.', 'jovaddstudio' )
  );
  ?>
</div>

<!-- ── Script & Stili ── -->
<div class="js-section">
  <h2 class="js-section-title">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
    <?php esc_html_e( 'Script & Stili', 'jovaddstudio' ); ?>
  </h2>
  <?php
  js_field_toggle( 'perf_disable_gutenberg',         __( 'Disabilita Gutenberg',          'jovaddstudio' ), __( 'Sostituisce l\'editor a blocchi con quello classico su tutti i post type e rimuove tutti gli stili Gutenberg dal frontend (block-library, global-styles e CSS inline).', 'jovaddstudio' ) );
  js_field_toggle( 'perf_disable_emoji',            __( 'Disabilita Emoji WordPress',    'jovaddstudio' ), __( 'Rimuove wp-emoji-release.js e le CSS associate (~10kB)', 'jovaddstudio' ) );
  js_field_toggle( 'perf_disable_jquery_migrate',   __( 'Rimuovi jQuery Migrate',        'jovaddstudio' ), __( 'jQuery Migrate serve solo per plugin legacy. Risparmia ~30kB', 'jovaddstudio' ) );
  js_field_toggle( 'perf_move_jquery_footer',       __( 'Sposta jQuery in footer',       'jovaddstudio' ), __( 'Migliora il Time to First Byte. Verifica la compatibilità con i plugin attivi.', 'jovaddstudio' ) );
  js_field_toggle( 'perf_disable_block_library_css', __( 'Disabilita Block Library CSS', 'jovaddstudio' ), __( 'Rimuove wp-block-library.css e global-styles. Ridondante se Gutenberg è disabilitato.', 'jovaddstudio' ) );
  js_field_toggle( 'perf_disable_comment_reply_js', __( 'Rimuovi comment-reply.js',      'jovaddstudio' ), __( 'Sicuro se i commenti sono disabilitati.', 'jovaddstudio' ) );
  js_field_toggle( 'perf_disable_heartbeat',        __( 'Disabilita WP Heartbeat',       'jovaddstudio' ), __( 'Heartbeat fa polling ogni 15–60s. Disabilitarlo riduce le richieste in admin.', 'jovaddstudio' ) );
  js_field_toggle( 'perf_remove_version_strings',   __( 'Rimuovi ?ver= da CSS/JS',       'jovaddstudio' ), __( 'Migliora il cache hit rate su CDN — i file vengono serviti senza query string versione.', 'jovaddstudio' ) );
  ?>
</div>

<!-- ── Head cleanup ── -->
<div class="js-section">
  <h2 class="js-section-title">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
    <?php esc_html_e( 'Pulizia &lt;head&gt;', 'jovaddstudio' ); ?>
  </h2>
  <?php
  js_field_toggle( 'perf_remove_rss_links', __( 'Rimuovi link RSS',              'jovaddstudio' ), __( 'Rimuove i tag <link> per i feed RSS/Atom dall\'head.', 'jovaddstudio' ) );
  js_field_toggle( 'perf_remove_rsd',       __( 'Rimuovi RSD link',              'jovaddstudio' ), __( 'Really Simple Discovery — usato da applicazioni di blogging legacy.', 'jovaddstudio' ) );
  js_field_toggle( 'perf_remove_wlw',       __( 'Rimuovi WLW Manifest',          'jovaddstudio' ), __( 'Windows Live Writer — software di blogging obsoleto.', 'jovaddstudio' ) );
  js_field_toggle( 'perf_remove_shortlink', __( 'Rimuovi Shortlink',             'jovaddstudio' ), __( 'Rimuove il tag <link rel="shortlink"> dall\'head.', 'jovaddstudio' ) );
  js_field_toggle( 'perf_remove_rest_link', __( 'Rimuovi REST API link',         'jovaddstudio' ), __( 'Rimuove <link rel="https://api.w.org/"> dall\'head. La REST API rimane funzionale.', 'jovaddstudio' ) );
  js_field_toggle( 'perf_remove_oembed',    __( 'Rimuovi oEmbed discovery',      'jovaddstudio' ), __( 'Rimuove i link per l\'auto-discovery oEmbed.', 'jovaddstudio' ) );
  ?>
</div>

<!-- ── Commenti ── -->
<div class="js-section">
  <h2 class="js-section-title">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
    <?php esc_html_e( 'Commenti', 'jovaddstudio' ); ?>
  </h2>
  <?php
  js_field_toggle( 'perf_disable_comments', __( 'Disabilita commenti sitewide', 'jovaddstudio' ), __( 'Rimuove il supporto commenti da tutti i post type, nasconde la voce "Commenti" dal menu admin e deregistra il widget Commenti recenti.', 'jovaddstudio' ) );
  ?>
</div>
