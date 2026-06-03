<?php
$sitename = get_bloginfo( 'name' );
$title    = js_get_option( 'special_404_title',    __( 'Pagina non trovata', 'jovaddstudio' ) );
$text     = js_get_option( 'special_404_text',     __( 'La pagina che stai cercando non esiste o è stata spostata.', 'jovaddstudio' ) );
$cta_txt  = js_get_option( 'special_404_cta_text', __( 'Torna alla home', 'jovaddstudio' ) );
$cta_url  = js_get_option( 'special_404_cta_url',  home_url( '/' ) );
$year     = date( 'Y' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex,nofollow">
  <title>404 — <?php echo esc_html( $sitename ); ?></title>
  <?php wp_head(); ?>
  <style>

  /* ── Dark page reset ── */
  body.err-page {
    background: var(--brand-900, #050508);
    color: #fff;
    justify-content: flex-start;
    align-items: stretch;
    padding: 0;
    overflow-x: hidden;
  }

  /* ── Film grain ── */
  body.err-page::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='250' height='250'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
    background-size: 180px;
    opacity: .028;
    pointer-events: none;
    z-index: 0;
    animation: grain .5s steps(2) infinite;
  }

  @keyframes grain {
    0%   { transform: translate(0, 0); }
    50%  { transform: translate(-1%, 1%); }
    100% { transform: translate(1%, -1%); }
  }

  /* ── Radial glow from accent color ── */
  body.err-page::after {
    content: '';
    position: fixed;
    inset: 0;
    background: radial-gradient(ellipse 75% 55% at 50% 38%, color-mix(in srgb, var(--color-accent, #4a6fa5) 14%, transparent) 0%, transparent 65%);
    pointer-events: none;
    z-index: 0;
  }

  /* ── Layout wrapper ── */
  .err-layout {
    position: relative;
    z-index: 1;
    flex: 1;
    display: grid;
    grid-template-rows: auto 1fr auto;
    min-height: 100dvh;
    padding: clamp(2.4rem, 4vw, 4.8rem) clamp(2.4rem, 6vw, 7.2rem);
  }

  /* ── Header ── */
  .err-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: clamp(4rem, 6vw, 8rem);
    animation: fadeUp .8s cubic-bezier(.16,1,.3,1) both;
  }

  .err-logo img {
    max-height: 30px;
    width: auto;
    filter: brightness(0) invert(1);
  }
  .err-logo-text {
    font-size: var(--text-s);
    font-weight: var(--weight-bold);
    letter-spacing: .02em;
    color: #fff;
  }

  .err-label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: .14em;
    color: rgba(255,255,255,.35);
    font-weight: var(--weight-medium);
  }
  .err-label::before {
    content: '';
    width: 24px;
    height: 1px;
    background: rgba(255,255,255,.25);
    display: block;
  }

  /* ── Main content ── */
  .err-main {
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding-block: clamp(4rem, 6vw, 8rem);
  }

  .err-eyebrow {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.1rem;
    font-weight: var(--weight-semibold);
    text-transform: uppercase;
    letter-spacing: .14em;
    color: var(--color-accent, #4a6fa5);
    margin: 0 0 clamp(1.6rem, 2vw, 2.8rem);
    animation: fadeUp .8s .12s cubic-bezier(.16,1,.3,1) both;
  }
  .err-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--color-accent, #4a6fa5);
    flex-shrink: 0;
    animation: pulse 2.4s ease-in-out infinite;
  }
  @keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: .3; transform: scale(.7); }
  }

  .err-divider {
    width: 0;
    height: 1px;
    background: rgba(255,255,255,.15);
    margin-bottom: clamp(2rem, 2.5vw, 3.2rem);
    animation: growLine 1s .22s cubic-bezier(.16,1,.3,1) both;
  }
  @keyframes growLine {
    to { width: clamp(40px, 8vw, 80px); }
  }

  .err-headline {
    font-size: clamp(4rem, 9vw, 12rem);
    font-weight: 900;
    line-height: 1;
    letter-spacing: -.03em;
    color: #fff;
    margin: 0 0 clamp(2rem, 2.5vw, 3.2rem);
    max-width: 18ch;
    animation: fadeUp .9s .28s cubic-bezier(.16,1,.3,1) both;
  }

  .err-text {
    font-size: clamp(1.4rem, 1.6vw, 1.8rem);
    color: rgba(255,255,255,.45);
    line-height: 1.7;
    max-width: 46ch;
    margin: 0 0 clamp(3.2rem, 4vw, 4.8rem);
    animation: fadeUp .9s .38s cubic-bezier(.16,1,.3,1) both;
  }

  .err-cta {
    display: inline-flex;
    align-items: center;
    gap: .8rem;
    padding: 1.2rem 2.8rem;
    background: var(--color-accent, #4a6fa5);
    color: #fff;
    font-size: 1.4rem;
    font-weight: var(--weight-semibold);
    letter-spacing: .02em;
    border-radius: 6px;
    text-decoration: none;
    align-self: flex-start;
    transition: background .2s cubic-bezier(.16,1,.3,1), transform .2s cubic-bezier(.16,1,.3,1);
    animation: fadeUp .9s .46s cubic-bezier(.16,1,.3,1) both;
  }
  .err-cta:hover {
    background: var(--color-accent-hover, #3b5987);
    transform: translateY(-2px);
  }
  .err-cta::after {
    content: '→';
  }

  /* ── Footer ── */
  .err-footer {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    padding-top: clamp(3.2rem, 4vw, 5.6rem);
    border-top: 1px solid rgba(255,255,255,.08);
    animation: fadeUp .8s .56s cubic-bezier(.16,1,.3,1) both;
  }

  .err-copyright {
    font-size: 1.1rem;
    color: rgba(255,255,255,.25);
    margin: 0;
    letter-spacing: .04em;
  }

  .err-made {
    font-size: 1.1rem;
    color: rgba(255,255,255,.25);
    margin: 0;
    text-align: right;
    letter-spacing: .04em;
  }
  .err-made a {
    color: rgba(255,255,255,.45);
    text-decoration: none;
    transition: color .2s;
  }
  .err-made a:hover { color: #fff; }

  /* ── Shared animation ── */
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(22px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* ── Mobile ── */
  @media (max-width: 600px) {
    .err-label  { display: none; }
    .err-footer { flex-direction: column; gap: 1rem; align-items: flex-start; }
    .err-made   { text-align: left; }
  }

  </style>
</head>
<body <?php body_class( 'err-page' ); ?>>
<?php wp_body_open(); ?>

<div class="err-layout">

  <header class="err-header">
    <div class="err-logo">
      <?php if ( has_custom_logo() ) : ?>
        <?php the_custom_logo(); ?>
      <?php else : ?>
        <span class="err-logo-text"><?php echo esc_html( $sitename ); ?></span>
      <?php endif; ?>
    </div>
    <span class="err-label"><?php esc_html_e( 'Errore', 'jovaddstudio' ); ?></span>
  </header>

  <main class="err-main" role="main">
    <p class="err-eyebrow">
      <span class="err-dot" aria-hidden="true"></span>
      404
    </p>
    <div class="err-divider" aria-hidden="true"></div>
    <h1 class="err-headline"><?php echo esc_html( $title ); ?></h1>
    <?php if ( $text ) : ?>
      <p class="err-text"><?php echo esc_html( $text ); ?></p>
    <?php endif; ?>
    <?php if ( $cta_txt && $cta_url ) : ?>
      <a href="<?php echo esc_url( $cta_url ); ?>" class="err-cta">
        <?php echo esc_html( $cta_txt ); ?>
      </a>
    <?php endif; ?>
  </main>

  <footer class="err-footer">
    <p class="err-copyright">
      &copy; <?php echo esc_html( $year . ' ' . $sitename ); ?>
    </p>
    <p class="err-made">
      Made with code by
      <a href="https://jovaddstudio.it" target="_blank" rel="noopener noreferrer">Jovadd Studio</a>
    </p>
  </footer>

</div>

<?php wp_footer(); ?>
</body>
</html>
