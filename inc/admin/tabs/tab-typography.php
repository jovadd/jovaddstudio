<?php defined( 'ABSPATH' ) || exit;

$weights_list = [
    '100' => 'Thin 100',
    '200' => 'ExtraLight 200',
    '300' => 'Light 300',
    '400' => 'Regular 400',
    '500' => 'Medium 500',
    '600' => 'SemiBold 600',
    '700' => 'Bold 700',
    '800' => 'ExtraBold 800',
    '900' => 'Black 900',
];

$font_roles = [
    'heading' => [
        'label'    => __( 'Font Heading', 'jovaddstudio' ),
        'var'      => '--font-heading',
        'defaults' => [ '400', '600', '700' ],
    ],
    'body' => [
        'label'    => __( 'Font Body', 'jovaddstudio' ),
        'var'      => '--font-text',
        'defaults' => [ '300', '400', '500' ],
    ],
    'mono' => [
        'label'    => __( 'Font Mono', 'jovaddstudio' ),
        'var'      => '--font-mono',
        'defaults' => [ '400', '700' ],
    ],
];
?>

<div class="js-section">
  <h2 class="js-section-title"><?php esc_html_e( 'Google Fonts API', 'jovaddstudio' ); ?></h2>
  <?php js_field_text(
      'fonts_api_key',
      __( 'API Key', 'jovaddstudio' ),
      __( 'Necessaria per la ricerca autocomplete dei font. Ottienila su console.cloud.google.com — abilita "Google Fonts Developer API".', 'jovaddstudio' ),
      'text',
      'AIza...'
  ); ?>
  <div class="js-field">
    <div class="js-field-label"></div>
    <div class="js-field-control">
      <button type="button" class="button js-load-fonts-btn">
        <?php esc_html_e( 'Aggiorna cache lista font', 'jovaddstudio' ); ?>
      </button>
      <span class="js-fonts-cache-status"></span>
    </div>
  </div>
</div>

<?php foreach ( $font_roles as $role => $meta ) :
    $installed = js_get_option( "font_{$role}_family" );
?>
<div class="js-section">
  <h2 class="js-section-title">
    <?php echo esc_html( $meta['label'] ); ?>
    <code class="js-section-var"><?php echo esc_html( $meta['var'] ); ?></code>
  </h2>

  <div class="js-font-picker" data-role="<?php echo esc_attr( $role ); ?>">

    <!-- Installed badge -->
    <div class="js-font-installed" <?php if ( ! $installed ) echo 'hidden'; ?>>
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      <span><?php esc_html_e( 'Installato:', 'jovaddstudio' ); ?></span>
      <strong class="js-installed-font-name"><?php echo esc_html( $installed ); ?></strong>
      <button type="button" class="js-font-remove" data-role="<?php echo esc_attr( $role ); ?>">
        <?php esc_html_e( 'Rimuovi', 'jovaddstudio' ); ?>
      </button>
    </div>

    <!-- Hidden input preserved by form save -->
    <input type="hidden" name="jovaddstudio_options[font_<?php echo esc_attr( $role ); ?>_family]" class="js-font-family-hidden" value="<?php echo esc_attr( $installed ); ?>">

    <!-- Search row -->
    <div class="js-font-search-row">
      <div class="js-font-autocomplete-wrap">
        <input
          type="text"
          class="js-font-name-input"
          placeholder="<?php esc_attr_e( 'Cerca font (es. Inter, Poppins…)', 'jovaddstudio' ); ?>"
          value="<?php echo esc_attr( $installed ); ?>"
          autocomplete="off"
        >
        <ul class="js-font-suggestions" hidden></ul>
      </div>
      <input type="hidden" class="js-font-role" value="<?php echo esc_attr( $role ); ?>">
      <button type="button" class="button button-primary js-font-download-btn">
        <?php esc_html_e( 'Scarica e Installa', 'jovaddstudio' ); ?>
      </button>
    </div>

    <!-- Weight selector -->
    <div class="js-weights-wrap">
      <p class="js-weights-label"><?php esc_html_e( 'Pesi da scaricare:', 'jovaddstudio' ); ?></p>
      <div class="js-font-weights">
        <?php foreach ( $weights_list as $w => $label ) : ?>
          <label class="js-font-weight-chip <?php echo in_array( $w, $meta['defaults'], true ) ? 'is-default' : ''; ?>">
            <input
              type="checkbox"
              class="js-font-weight-check"
              value="<?php echo esc_attr( $w ); ?>"
              <?php checked( in_array( $w, $meta['defaults'], true ) ); ?>
            >
            <span><?php echo esc_html( $label ); ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <p class="js-font-status" aria-live="polite"></p>

  </div><!-- /.js-font-picker -->
</div>
<?php endforeach; ?>
