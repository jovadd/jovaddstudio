<?php defined( 'ABSPATH' ) || exit; ?>

<div class="js-section">
  <h2 class="js-section-title"><?php esc_html_e( 'Plugin SEO di terze parti', 'jovaddstudio' ); ?></h2>
  <?php js_field_toggle( 'seo_disable_theme_meta', __( 'Disabilita meta SEO del tema', 'jovaddstudio' ), __( 'Attiva se usi RankMath, SEOPress, Yoast o simili. Disabilita l\'iniezione di meta description, robots e OG image dal tema per evitare conflitti. La verifica Search Console rimane attiva.', 'jovaddstudio' ) ); ?>
</div>

<div class="js-section">
  <h2 class="js-section-title"><?php esc_html_e( 'SEO base', 'jovaddstudio' ); ?></h2>
  <?php
  js_field_text( 'seo_meta_description', __( 'Meta description', 'jovaddstudio' ), __( 'Descrizione default mostrata nei risultati di ricerca (homepage). Max 160 caratteri.', 'jovaddstudio' ) );
  ?>
  <!-- OG Image con media uploader -->
  <div class="js-field">
    <div class="js-field-label">
      <label><?php esc_html_e( 'OG Image default', 'jovaddstudio' ); ?></label>
      <p class="js-field-hint"><?php esc_html_e( 'Immagine mostrata sui social quando si condivide il sito (min. 1200×630px)', 'jovaddstudio' ); ?></p>
    </div>
    <div class="js-field-control">
      <?php $og = js_get_option( 'seo_og_image' ); ?>
      <div class="js-media-picker">
        <?php if ( $og ) : ?>
          <img class="js-media-preview" src="<?php echo esc_url( $og ); ?>" alt="">
        <?php else : ?>
          <div class="js-media-preview-placeholder"><?php esc_html_e( 'Nessuna immagine', 'jovaddstudio' ); ?></div>
        <?php endif; ?>
        <input type="hidden" name="jovaddstudio_options[seo_og_image]" class="js-media-value" value="<?php echo esc_url( $og ); ?>">
        <div class="js-media-actions">
          <button type="button" class="button js-media-select"><?php esc_html_e( 'Seleziona immagine', 'jovaddstudio' ); ?></button>
          <?php if ( $og ) : ?>
            <button type="button" class="button js-media-remove"><?php esc_html_e( 'Rimuovi', 'jovaddstudio' ); ?></button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php
  js_field_select( 'seo_robots', __( 'Robots', 'jovaddstudio' ), [
      'index,follow'     => 'index, follow (default)',
      'noindex,follow'   => 'noindex, follow',
      'index,nofollow'   => 'index, nofollow',
      'noindex,nofollow' => 'noindex, nofollow',
  ] );
  ?>
</div>

<div class="js-section">
  <h2 class="js-section-title"><?php esc_html_e( 'Google Search Console', 'jovaddstudio' ); ?></h2>
  <?php js_field_text( 'seo_google_sc_verification', __( 'Codice di verifica', 'jovaddstudio' ), __( 'Incolla solo il valore dell\'attributo content del meta tag fornito da Search Console (es. abc123XYZ).', 'jovaddstudio' ), 'text', 'abc123XYZ…' ); ?>
  <div class="js-alert js-alert-info">
    <span><?php esc_html_e( 'Il meta tag google-site-verification viene iniettato automaticamente nel &lt;head&gt;.', 'jovaddstudio' ); ?></span>
  </div>
</div>

<div class="js-section">
  <h2 class="js-section-title"><?php esc_html_e( 'Google Analytics 4', 'jovaddstudio' ); ?></h2>
  <?php js_field_text( 'analytics_ga_id', __( 'Measurement ID', 'jovaddstudio' ), __( 'Formato: G-XXXXXXXXXX — trovalo in Analytics › Amministrazione › Flusso dati', 'jovaddstudio' ), 'text', 'G-XXXXXXXXXX' ); ?>
</div>

<div class="js-section">
  <h2 class="js-section-title"><?php esc_html_e( 'Google Tag Manager', 'jovaddstudio' ); ?></h2>
  <?php js_field_text( 'analytics_gtm_id', __( 'Container ID', 'jovaddstudio' ), __( 'Formato: GTM-XXXXXXX — trovalo in Tag Manager › Amministrazione', 'jovaddstudio' ), 'text', 'GTM-XXXXXXX' ); ?>
  <div class="js-alert js-alert-info">
    <span><?php esc_html_e( 'Il codice GTM viene iniettato automaticamente sia nel &lt;head&gt; che nel &lt;body&gt; (noscript).', 'jovaddstudio' ); ?></span>
  </div>
</div>

<div class="js-section">
  <h2 class="js-section-title"><?php esc_html_e( 'Facebook Pixel', 'jovaddstudio' ); ?></h2>
  <?php js_field_text( 'analytics_fb_pixel', __( 'Pixel ID', 'jovaddstudio' ), __( 'ID numerico — trovalo in Facebook Business Manager › Gestione eventi', 'jovaddstudio' ), 'text', '1234567890' ); ?>
</div>

<div class="js-section">
  <h2 class="js-section-title"><?php esc_html_e( 'Codice custom &lt;head&gt;', 'jovaddstudio' ); ?></h2>
  <?php js_field_textarea( 'analytics_custom_head', __( 'Script personalizzati', 'jovaddstudio' ), __( 'Aggiunto subito prima della chiusura &lt;/head&gt;. Supporta tag &lt;script&gt; e &lt;noscript&gt;.', 'jovaddstudio' ), 6 ); ?>
</div>

<!-- ── SEO Audit ── -->
<div class="js-section js-seo-audit-section">
  <h2 class="js-section-title">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <?php esc_html_e( 'Audit struttura SEO', 'jovaddstudio' ); ?>
  </h2>
  <p class="js-field-hint" style="margin-bottom:12px"><?php esc_html_e( 'Analisi on-demand: seleziona una pagina e clicca Analizza per verificare H1/H2, alt immagini, meta, canonical, OG tag e tag semantici.', 'jovaddstudio' ); ?></p>

  <div class="js-audit-list">
    <?php
    $audit_posts = get_posts( [
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ] );
    foreach ( $audit_posts as $p ) :
        $url = get_permalink( $p->ID );
    ?>
    <?php $saved_desc = get_post_meta( $p->ID, '_js_meta_description', true ); ?>
    <div class="js-audit-row" data-post-id="<?php echo (int) $p->ID; ?>">
      <div class="js-audit-row-header">
        <div class="js-audit-row-title">
          <span class="js-audit-status-dot js-audit-seo-dot" title="SEO: non analizzato"></span>
          <span class="js-audit-status-dot js-audit-a11y-dot" title="Accessibilità: non analizzato"></span>
          <span><?php echo esc_html( $p->post_title ); ?></span>
          <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener" class="js-audit-ext-link" title="Apri pagina">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
          </a>
        </div>
        <div class="js-audit-actions">
          <button
            type="button"
            class="button js-audit-btn"
            data-url="<?php echo esc_url( $url ); ?>"
            data-nonce="<?php echo esc_attr( wp_create_nonce( 'js_admin_nonce' ) ); ?>"
          ><?php esc_html_e( 'SEO', 'jovaddstudio' ); ?></button>
          <button
            type="button"
            class="button js-a11y-btn"
            data-url="<?php echo esc_url( $url ); ?>"
            data-nonce="<?php echo esc_attr( wp_create_nonce( 'js_admin_nonce' ) ); ?>"
          ><?php esc_html_e( 'Accessibilità', 'jovaddstudio' ); ?></button>
          <button
            type="button"
            class="button js-meta-toggle<?php echo $saved_desc ? ' has-value' : ''; ?>"
            title="<?php esc_attr_e( 'Modifica meta description', 'jovaddstudio' ); ?>"
          ><?php esc_html_e( 'Meta', 'jovaddstudio' ); ?></button>
        </div>
      </div>
      <div class="js-meta-edit" hidden>
        <textarea
          class="js-meta-textarea"
          rows="2"
          maxlength="160"
          placeholder="<?php esc_attr_e( 'Meta description (50–160 caratteri)', 'jovaddstudio' ); ?>"
        ><?php echo esc_textarea( $saved_desc ); ?></textarea>
        <div class="js-meta-edit-footer">
          <span class="js-meta-counter"></span>
          <span class="js-meta-save-msg"></span>
          <button
            type="button"
            class="button button-primary js-meta-save"
            data-post-id="<?php echo (int) $p->ID; ?>"
            data-nonce="<?php echo esc_attr( wp_create_nonce( 'js_admin_nonce' ) ); ?>"
          ><?php esc_html_e( 'Salva', 'jovaddstudio' ); ?></button>
        </div>
      </div>
      <div class="js-audit-results" hidden></div>
      <div class="js-a11y-results" hidden></div>
    </div>
    <?php endforeach; ?>
    <?php if ( empty( $audit_posts ) ) : ?>
      <p class="js-field-hint"><?php esc_html_e( 'Nessuna pagina pubblicata.', 'jovaddstudio' ); ?></p>
    <?php endif; ?>
  </div>
</div>
