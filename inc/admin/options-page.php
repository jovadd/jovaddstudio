<?php
defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
    if ( ! js_is_dev() ) return;

    add_menu_page(
        __( 'Jovadd Studio', 'jovaddstudio' ),
        __( 'Jovadd Studio', 'jovaddstudio' ),
        'manage_options',
        'jovaddstudio',
        'js_render_options_page',
        'none',
        60
    );
} );

function js_render_options_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Accesso non autorizzato.', 'jovaddstudio' ) );
    }

    $tabs = [
        'general' => [
            'label' => __( 'Generale', 'jovaddstudio' ),
            'icon'  => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93A10 10 0 0 0 4.93 4.93M4.93 19.07A10 10 0 0 0 19.07 19.07"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2"/></svg>',
        ],
        'typography' => [
            'label' => __( 'Tipografia', 'jovaddstudio' ),
            'icon'  => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7V4h16v3M9 20h6M12 4v16"/></svg>',
        ],
        'seo' => [
            'label' => __( 'SEO & Analytics', 'jovaddstudio' ),
            'icon'  => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
        ],
        'performance' => [
            'label' => __( 'Performance', 'jovaddstudio' ),
            'icon'  => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>',
        ],
        'security' => [
            'label' => __( 'Sicurezza', 'jovaddstudio' ),
            'icon'  => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
        ],
        'special' => [
            'label' => __( 'Pagine Speciali', 'jovaddstudio' ),
            'icon'  => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>',
        ],
    ];

    $saved = ! empty( $_GET['settings-updated'] );
    // phpcs:ignore WordPress.Security.NonceVerification

    // Theme icon SVG (same as the admin menu icon)
    $brand_icon = '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">'
        . '<path d="M12 2L2 7l10 5 10-5-10-5z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>'
        . '<path d="M2 17l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>'
        . '<path d="M2 12l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>'
        . '</svg>';
    ?>
    <div id="js-options-page" class="wrap">
    <form method="post" action="options.php" novalidate>
      <?php settings_fields( 'jovaddstudio' ); ?>

      <div class="js-options-panel">

        <!-- ── Header (full width) ── -->
        <header class="js-admin-header">
          <div class="js-admin-brand">
            <div class="js-admin-brand-icon"><?php echo $brand_icon; ?></div>
            <div class="js-admin-brand-text">
              <h1><?php esc_html_e( 'Jovadd Studio', 'jovaddstudio' ); ?></h1>
              <span class="js-admin-version">v<?php echo esc_html( wp_get_theme()->get( 'Version' ) ); ?></span>
            </div>
          </div>
          <?php if ( $saved ) : ?>
            <div class="js-toast is-visible">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              <?php esc_html_e( 'Impostazioni salvate', 'jovaddstudio' ); ?>
            </div>
          <?php endif; ?>
        </header>

        <!-- ── Sidebar ── -->
        <aside class="js-sidebar">
          <nav class="js-sidebar-nav" role="tablist">
            <span class="js-sidebar-label"><?php esc_html_e( 'Impostazioni', 'jovaddstudio' ); ?></span>
            <?php foreach ( $tabs as $id => $tab ) : ?>
              <button
                class="js-tab-btn"
                role="tab"
                type="button"
                data-tab="<?php echo esc_attr( $id ); ?>"
                aria-controls="panel-<?php echo esc_attr( $id ); ?>"
                aria-selected="false"
              >
                <span class="js-tab-icon"><?php echo $tab['icon']; ?></span>
                <?php echo esc_html( $tab['label'] ); ?>
              </button>
            <?php endforeach; ?>
          </nav>

          <!-- Rinomina tema -->
          <div class="js-sidebar-project">
            <p class="js-sidebar-project-label"><?php esc_html_e( 'Progetto', 'jovaddstudio' ); ?></p>
            <input
              type="text"
              id="js-theme-name-input"
              class="js-theme-name-input"
              value="<?php echo esc_attr( wp_get_theme()->get( 'Name' ) ); ?>"
              placeholder="<?php esc_attr_e( 'Nome cliente…', 'jovaddstudio' ); ?>"
              autocomplete="off"
            >
            <button type="button" id="js-rename-theme-btn" class="js-rename-btn">
              <?php esc_html_e( 'Rinomina tema', 'jovaddstudio' ); ?>
            </button>
            <p class="js-rename-status" aria-live="polite"></p>
          </div>

          <div class="js-sidebar-footer">
            <?php
            submit_button(
                __( 'Salva impostazioni', 'jovaddstudio' ),
                'primary',
                'submit',
                false
            );
            ?>
          </div>
        </aside>

        <!-- ── Content ── -->
        <main class="js-content">
          <div class="js-content-inner">
            <?php foreach ( $tabs as $id => $tab ) : ?>
              <div
                id="panel-<?php echo esc_attr( $id ); ?>"
                class="js-tab-panel"
                role="tabpanel"
                data-panel="<?php echo esc_attr( $id ); ?>"
                hidden
              >
                <?php
                $file = get_template_directory() . '/inc/admin/tabs/tab-' . $id . '.php';
                if ( file_exists( $file ) ) include $file;
                ?>
              </div>
            <?php endforeach; ?>
          </div>
        </main>

      </div><!-- /.js-options-panel -->
    </form>
    </div>
    <?php
}

