<?php defined( 'ABSPATH' ) || exit; ?>

<div class="js-alert js-alert-info" style="margin-bottom:2rem;">
  <?php esc_html_e( 'Queste opzioni rafforzano la sicurezza di base. Non sostituiscono un WAF o un plugin dedicato (Wordfence, iThemes), ma eliminano le informazioni superflue esposte da WordPress.', 'jovaddstudio' ); ?>
</div>

<div class="js-section">
  <h2 class="js-section-title"><?php esc_html_e( 'Oscuramento WordPress', 'jovaddstudio' ); ?></h2>
  <?php
  js_field_toggle( 'sec_remove_generator',    __( 'Rimuovi meta generator',        'jovaddstudio' ), __( 'Nasconde <meta name="generator" content="WordPress x.x"> — non rivela la versione di WP ai bot', 'jovaddstudio' ) );
  js_field_toggle( 'sec_remove_version_query', __( 'Rimuovi versione da CSS/JS',   'jovaddstudio' ), __( 'Rimuove ?ver=6.x.x dagli asset WP core — nasconde la versione esatta installata', 'jovaddstudio' ) );
  js_field_toggle( 'sec_disable_file_edit',   __( 'Disabilita editor file in admin', 'jovaddstudio' ), __( 'Impedisce la modifica di tema e plugin dal pannello admin (equivale a DISALLOW_FILE_EDIT)', 'jovaddstudio' ) );
  ?>
</div>

<div class="js-section">
  <h2 class="js-section-title"><?php esc_html_e( 'Accesso & Autenticazione', 'jovaddstudio' ); ?></h2>
  <?php
  js_field_toggle( 'sec_disable_login_errors', __( 'Nascondi errori di login',     'jovaddstudio' ), __( 'Sostituisce "Password errata" / "Utente non trovato" con un messaggio generico', 'jovaddstudio' ) );
  js_field_toggle( 'sec_disable_user_enum',    __( 'Disabilita user enumeration',   'jovaddstudio' ), __( 'Blocca ?author=1 e rimuove /wp/v2/users dalla REST API pubblica', 'jovaddstudio' ) );
  ?>
</div>

<div class="js-section">
  <h2 class="js-section-title"><?php esc_html_e( 'Protocolli & Feed', 'jovaddstudio' ); ?></h2>
  <?php
  js_field_toggle( 'sec_disable_xmlrpc',   __( 'Disabilita XML-RPC',        'jovaddstudio' ), __( 'XML-RPC è un vettore comune di attacchi brute-force. Disabilitalo se non usi app mobile WordPress o Jetpack', 'jovaddstudio' ) );
  js_field_toggle( 'sec_disable_pingback', __( 'Disabilita Pingback',       'jovaddstudio' ), __( 'Previene l\'uso del sito come amplificatore DDoS tramite pingback XML-RPC', 'jovaddstudio' ) );
  js_field_toggle( 'sec_disable_feed',     __( 'Disabilita feed RSS/Atom',  'jovaddstudio' ), __( 'Redirige tutti i feed a 404. Utile per siti vetrina o landing page senza blog', 'jovaddstudio' ) );
  ?>
</div>
