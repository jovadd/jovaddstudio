<?php
defined( 'ABSPATH' ) || exit;

$sitename = get_bloginfo( 'name' );
$headline = js_get_option( 'maintenance_headline' ) ?: 'Qualcosa di nuovo sta arrivando';
$text     = js_get_option( 'maintenance_text' )     ?: '';
$year     = gmdate( 'Y' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex,nofollow">
  <title><?php echo esc_html( $sitename ); ?></title>
  <?php wp_head(); ?>
  <style>

  /* ── Dark page reset ── */
  body.mnt-page {
    background: var(--brand-900, #050508);
    color: #fff;
    justify-content: flex-start;
    align-items: stretch;
    padding: 0;
    overflow-x: hidden;
  }

  /* ── Film grain ── */
  body.mnt-page::before {
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

  /* ── Radial glow from brand color ── */
  body.mnt-page::after {
    content: '';
    position: fixed;
    inset: 0;
    background: radial-gradient(ellipse 75% 55% at 50% 38%, color-mix(in srgb, var(--color-accent, #4a6fa5) 14%, transparent) 0%, transparent 65%);
    pointer-events: none;
    z-index: 0;
  }

  /* ── Layout wrapper ── */
  .mnt-layout {
    position: relative;
    z-index: 1;
    flex: 1;
    display: grid;
    grid-template-rows: auto 1fr auto;
    min-height: 100dvh;
    padding: clamp(2.4rem, 4vw, 4.8rem) clamp(2.4rem, 6vw, 7.2rem);
  }

  /* ── Header ── */
  .mnt-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: clamp(4rem, 6vw, 8rem);
    animation: fadeUp .8s cubic-bezier(.16,1,.3,1) both;
  }

  .mnt-logo img {
    max-height: 30px;
    width: auto;
    filter: brightness(0) invert(1);
  }
  .mnt-logo-text {
    font-size: var(--text-s);
    font-weight: var(--weight-bold);
    letter-spacing: .02em;
    color: #fff;
  }

  .mnt-agency {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: .14em;
    color: rgba(255,255,255,.35);
    font-weight: var(--weight-medium);
  }
  .mnt-agency::before {
    content: '';
    width: 24px;
    height: 1px;
    background: rgba(255,255,255,.25);
    display: block;
  }

  /* ── Main content ── */
  .mnt-main {
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding-block: clamp(4rem, 6vw, 8rem);
  }

  .mnt-eyebrow {
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
  .mnt-dot {
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

  .mnt-divider {
    width: 0;
    height: 1px;
    background: rgba(255,255,255,.15);
    margin-bottom: clamp(2rem, 2.5vw, 3.2rem);
    animation: growLine 1s .22s cubic-bezier(.16,1,.3,1) both;
  }
  @keyframes growLine {
    to { width: clamp(40px, 8vw, 80px); }
  }

  .mnt-headline {
    font-size: clamp(4rem, 9vw, 12rem);
    font-weight: 900;
    line-height: 1;
    letter-spacing: -.03em;
    color: #fff;
    margin: 0 0 clamp(2rem, 2.5vw, 3.2rem);
    max-width: 14ch;
    animation: fadeUp .9s .28s cubic-bezier(.16,1,.3,1) both;
  }

  .mnt-text {
    font-size: clamp(1.4rem, 1.6vw, 1.8rem);
    color: rgba(255,255,255,.45);
    line-height: 1.7;
    max-width: 46ch;
    margin: 0;
    animation: fadeUp .9s .38s cubic-bezier(.16,1,.3,1) both;
  }

  /* ── Footer ── */
  .mnt-footer {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    padding-top: clamp(3.2rem, 4vw, 5.6rem);
    border-top: 1px solid rgba(255,255,255,.08);
    animation: fadeUp .8s .48s cubic-bezier(.16,1,.3,1) both;
  }

  .mnt-copyright {
    font-size: 1.1rem;
    color: rgba(255,255,255,.25);
    margin: 0;
    letter-spacing: .04em;
  }

  .mnt-made {
    font-size: 1.1rem;
    color: rgba(255,255,255,.25);
    margin: 0;
    text-align: right;
    letter-spacing: .04em;
  }
  .mnt-made a {
    color: rgba(255,255,255,.45);
    text-decoration: none;
    transition: color .2s;
  }
  .mnt-made a:hover { color: #fff; }

  /* ── Shared animation ── */
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(22px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* ── Mobile ── */
  @media (max-width: 600px) {
    .mnt-agency { display: none; }
    .mnt-footer { flex-direction: column; gap: 1rem; align-items: flex-start; }
    .mnt-made   { text-align: left; }
  }

  </style>
</head>
<body <?php body_class( 'mnt-page' ); ?>>
<?php wp_body_open(); ?>

<div class="mnt-layout">

  <header class="mnt-header">
    <div class="mnt-logo">
      <?php if ( has_custom_logo() ) : ?>
        <?php the_custom_logo(); ?>
      <?php else : ?>
        <span class="mnt-logo-text"><?php echo esc_html( $sitename ); ?></span>
      <?php endif; ?>
    </div>
    <span class="mnt-agency">Jovadd Studio</span>
  </header>

  <main class="mnt-main">
    <p class="mnt-eyebrow">
      <span class="mnt-dot" aria-hidden="true"></span>
      <?php esc_html_e( 'In arrivo', 'jovaddstudio' ); ?>
    </p>
    <div class="mnt-divider" aria-hidden="true"></div>
    <h1 class="mnt-headline"><?php echo esc_html( $headline ); ?></h1>
    <?php if ( $text ) : ?>
      <p class="mnt-text"><?php echo nl2br( esc_html( $text ) ); ?></p>
    <?php endif; ?>
  </main>

  <footer class="mnt-footer">
    <p class="mnt-copyright">
      &copy; <?php echo esc_html( $year . ' ' . $sitename ); ?>
    </p>
    <p class="mnt-made">
      Made with code by
      <a href="https://jovaddstudio.it" target="_blank" rel="noopener noreferrer">Jovadd Studio</a>
    </p>
  </footer>

</div>

<?php wp_footer(); ?>
</body>
</html>
