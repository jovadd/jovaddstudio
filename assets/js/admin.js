/* ============================================================
   admin.js — Jovadd Studio Options Page
   ============================================================ */
( function () {
  'use strict';

  /* ----------------------------------------------------------
     Rinomina tema — aggiorna Theme Name in style.css
     ---------------------------------------------------------- */

  const renameBtn   = document.getElementById( 'js-rename-theme-btn' );
  const renameInput = document.getElementById( 'js-theme-name-input' );

  if ( renameBtn && renameInput ) {
    renameBtn.addEventListener( 'click', async function () {
      const name   = renameInput.value.trim();
      const status = this.closest( '.js-sidebar-project' ).querySelector( '.js-rename-status' );

      if ( ! name ) { status.textContent = 'Inserisci un nome.'; return; }

      this.disabled = true;
      status.textContent = '';

      const body = new FormData();
      body.append( 'action',     'js_rename_theme' );
      body.append( 'nonce',      jsAdminData.nonce );
      body.append( 'theme_name', name );

      try {
        const res  = await fetch( jsAdminData.ajaxUrl, { method: 'POST', body } );
        const json = await res.json();

        if ( json.success ) {
          status.textContent = '✓ Rinominato';
          status.className   = 'js-rename-status is-success';
          // Aggiorna il titolo nel browser senza ricaricare
          document.title = document.title.replace( /^[^–]+/, json.data.name + ' ' );
          setTimeout( () => { status.textContent = ''; status.className = 'js-rename-status'; }, 3000 );
        } else {
          status.textContent = '✗ ' + ( json.data?.message || 'Errore' );
          status.className   = 'js-rename-status is-error';
        }
      } catch {
        status.textContent = '✗ Errore di rete';
        status.className   = 'js-rename-status is-error';
      } finally {
        this.disabled = false;
      }
    } );

    // Invio con Enter nel campo
    renameInput.addEventListener( 'keydown', e => {
      if ( e.key === 'Enter' ) { e.preventDefault(); renameBtn.click(); }
    } );
  }

  /* ----------------------------------------------------------
     Toast auto-dismiss
     ---------------------------------------------------------- */

  const toast = document.querySelector( '.js-toast.is-visible' );
  if ( toast ) {
    setTimeout( () => {
      toast.classList.add( 'is-fading' );
      setTimeout( () => {
        toast.hidden = true;
        // Remove ?settings-updated from URL without reload
        const url = new URL( location.href );
        url.searchParams.delete( 'settings-updated' );
        history.replaceState( null, '', url.toString() );
      }, 370 );
    }, 3000 );
  }

  /* ----------------------------------------------------------
     Tab system — hash-based routing
     ---------------------------------------------------------- */

  const tabs   = Array.from( document.querySelectorAll( '.js-tab-btn' ) );
  const panels = Array.from( document.querySelectorAll( '.js-tab-panel' ) );

  function activateTab( id ) {
    let found = false;

    tabs.forEach( btn => {
      const active = btn.dataset.tab === id;
      btn.classList.toggle( 'is-active', active );
      btn.setAttribute( 'aria-selected', active ? 'true' : 'false' );
    } );

    panels.forEach( panel => {
      const active = panel.dataset.panel === id;
      panel.hidden  = ! active;
      if ( active ) found = true;
    } );

    if ( found ) {
      history.replaceState( null, '', '#' + id );
    }
  }

  tabs.forEach( btn => btn.addEventListener( 'click', () => activateTab( btn.dataset.tab ) ) );

  // Restore from hash or activate first tab
  const hash = location.hash.slice( 1 );
  if ( hash && document.querySelector( '[data-panel="' + hash + '"]' ) ) {
    activateTab( hash );
  } else if ( tabs.length ) {
    activateTab( tabs[0].dataset.tab );
  }

  /* ----------------------------------------------------------
     WP Media picker (OG Image)
     ---------------------------------------------------------- */

  document.querySelectorAll( '.js-media-select' ).forEach( btn => {
    btn.addEventListener( 'click', function () {
      const picker  = this.closest( '.js-media-picker' );
      const valEl   = picker.querySelector( '.js-media-value' );
      const prevEl  = picker.querySelector( '.js-media-preview' );
      const phEl    = picker.querySelector( '.js-media-preview-placeholder' );
      const removeBtn = picker.querySelector( '.js-media-remove' );

      const frame = wp.media( {
        title:    jsAdminData.strings.select_image,
        button:   { text: jsAdminData.strings.use_image },
        multiple: false,
        library:  { type: 'image' },
      } );

      frame.on( 'select', function () {
        const attachment = frame.state().get( 'selection' ).first().toJSON();
        const url = attachment.url;
        valEl.value = url;

        if ( prevEl ) {
          prevEl.src    = url;
          prevEl.hidden = false;
        } else if ( phEl ) {
          const img    = document.createElement( 'img' );
          img.src      = url;
          img.className = 'js-media-preview';
          phEl.replaceWith( img );
        }

        if ( removeBtn ) removeBtn.hidden = false;
      } );

      frame.open();
    } );
  } );

  document.querySelectorAll( '.js-media-remove' ).forEach( btn => {
    btn.addEventListener( 'click', function () {
      const picker  = this.closest( '.js-media-picker' );
      const valEl   = picker.querySelector( '.js-media-value' );
      const prevEl  = picker.querySelector( '.js-media-preview' );
      valEl.value   = '';
      if ( prevEl ) prevEl.src = '';
      this.hidden   = true;
    } );
  } );

  /* ----------------------------------------------------------
     Header CTA — show/hide conditional field groups
     ---------------------------------------------------------- */

  const ctaTypeSelect = document.getElementById( 'jso_header_cta_type' );

  function syncCtaGroups() {
    if ( ! ctaTypeSelect ) return;
    const val = ctaTypeSelect.value;
    document.querySelectorAll( '.js-cta-group' ).forEach( g => {
      // 'both' → show all groups; otherwise show only the matching group
      g.hidden = val !== 'both' && g.dataset.ctaGroup !== val;
    } );
  }

  if ( ctaTypeSelect ) {
    ctaTypeSelect.addEventListener( 'change', syncCtaGroups );
    syncCtaGroups(); // run on load
  }

  /* ----------------------------------------------------------
     SEO Audit
     ---------------------------------------------------------- */

  document.querySelectorAll( '.js-audit-btn' ).forEach( btn => {
    btn.addEventListener( 'click', async function () {
      const row     = this.closest( '.js-audit-row' );
      const results = row.querySelector( '.js-audit-results' );
      const dot     = row.querySelector( '.js-audit-seo-dot' ) || row.querySelector( '.js-audit-status-dot' );
      const url     = this.dataset.url;
      const nonce   = this.dataset.nonce;

      this.disabled    = true;
      this.textContent = jsAdminData.strings.audit_running;
      results.hidden   = true;

      const fd = new FormData();
      fd.append( 'action', 'js_seo_audit' );
      fd.append( 'nonce',  nonce );
      fd.append( 'url',    url );

      try {
        const res  = await fetch( jsAdminData.ajaxUrl, { method: 'POST', body: fd } );
        const json = await res.json();

        if ( json.success ) {
          const r = json.data.results;
          results.innerHTML = buildAuditTable( r );
          results.hidden    = false;

          // Overall status dot
          const statuses = Object.values( r ).map( c => c.status );
          const overall  = statuses.includes( 'error' ) ? 'error'
                         : statuses.includes( 'warning' ) ? 'warning' : 'ok';
          dot.className  = 'js-audit-status-dot is-' + overall;
          dot.title      = overall === 'ok' ? 'Tutto ok' : overall === 'warning' ? 'Attenzione' : 'Errori trovati';
        } else {
          results.innerHTML = '<p class="js-audit-error">✗ ' + ( json.data?.message || 'Errore' ) + '</p>';
          results.hidden    = false;
          dot.className     = 'js-audit-status-dot is-error';
        }
      } catch {
        results.innerHTML = '<p class="js-audit-error">✗ Errore di rete.</p>';
        results.hidden    = false;
        dot.className     = 'js-audit-status-dot is-error';
      } finally {
        this.disabled    = false;
        this.textContent = jsAdminData.strings.audit_rerun;
      }
    } );
  } );

  function buildAuditTable( r ) {
    const icon = s => s === 'ok'      ? '<span class="js-audit-icon is-ok">✓</span>'
                    : s === 'error'   ? '<span class="js-audit-icon is-error">✗</span>'
                    : s === 'warning' ? '<span class="js-audit-icon is-warning">!</span>'
                    :                   '<span class="js-audit-icon is-info">·</span>';

    const row = ( label, status, msg, detail = '' ) =>
      `<tr class="js-audit-check is-${status}">
        <td class="js-audit-check-icon">${icon( status )}</td>
        <td class="js-audit-check-label">${label}</td>
        <td class="js-audit-check-msg">${msg}${detail ? '<span class="js-audit-detail">' + detail + '</span>' : ''}</td>
      </tr>`;

    // H1 detail
    const h1Detail = r.h1.texts.length ? ' — ' + r.h1.texts.map( t => '<em>' + esc( t ) + '</em>' ).join( ', ' ) : '';

    // Headings detail
    const hDetail = Object.entries( r.headings.counts ).map( ( [k, v] ) => `${k.toUpperCase()}:${v}` ).join( ' ' );

    // Images detail
    const imgDetail = r.images.total > 0 ? ` (totale: ${r.images.total})` : '';

    // Semantic detail
    const semDetail = Object.keys( r.semantic.found ).length
      ? ' — ' + Object.keys( r.semantic.found ).map( t => `<code>&lt;${t}&gt;</code>` ).join( ' ' )
      : '';

    // OG detail
    const ogMissing = r.og.missing.length ? ' — mancanti: ' + r.og.missing.map( k => `<code>${esc(k)}</code>` ).join( ', ' ) : '';

    return `<table class="js-audit-table">
      <tbody>
        ${row( 'Title',           r.title.status,            esc( r.title.message ),            r.title.value ? ' — <em>' + esc( r.title.value ) + '</em>' : '' )}
        ${row( 'H1',              r.h1.status,               esc( r.h1.message ),               h1Detail )}
        ${row( 'Heading order',   r.headings.status,         esc( r.headings.message ),         hDetail ? ' — ' + hDetail : '' )}
        ${row( 'Immagini alt',    r.images.status,           esc( r.images.message ),           imgDetail )}
        ${row( 'Meta description',r.meta_description.status, esc( r.meta_description.message ), r.meta_description.value ? ' — <em>' + esc( r.meta_description.value ) + '</em>' : '' )}
        ${row( 'Canonical',       r.canonical.status,        esc( r.canonical.message ),        r.canonical.value ? ' — <code>' + esc( r.canonical.value ) + '</code>' : '' )}
        ${row( 'Open Graph',      r.og.status,               esc( r.og.message ),               ogMissing )}
        ${row( 'Tag semantici',   r.semantic.status,         esc( r.semantic.message ),         semDetail )}
        ${row( 'Structured data', r.structured_data.status,  esc( r.structured_data.message ),  '' )}
      </tbody>
    </table>`;
  }

  function esc( str ) {
    return String( str )
      .replace( /&/g, '&amp;' )
      .replace( /</g, '&lt;' )
      .replace( />/g, '&gt;' )
      .replace( /"/g, '&quot;' );
  }

  /* ----------------------------------------------------------
     Accessibility Audit
     ---------------------------------------------------------- */

  document.querySelectorAll( '.js-a11y-btn' ).forEach( btn => {
    btn.addEventListener( 'click', async function () {
      const row     = this.closest( '.js-audit-row' );
      const results = row.querySelector( '.js-a11y-results' );
      const dot     = row.querySelector( '.js-audit-a11y-dot' );
      const url     = this.dataset.url;
      const nonce   = this.dataset.nonce;

      this.disabled    = true;
      this.textContent = jsAdminData.strings.audit_running;
      results.hidden   = true;

      const fd = new FormData();
      fd.append( 'action', 'js_a11y_audit' );
      fd.append( 'nonce',  nonce );
      fd.append( 'url',    url );

      try {
        const res  = await fetch( jsAdminData.ajaxUrl, { method: 'POST', body: fd } );
        const json = await res.json();

        if ( json.success ) {
          const r = json.data.results;
          results.innerHTML = buildA11yTable( r );
          results.hidden    = false;

          const statuses = Object.values( r ).map( c => c.status );
          const overall  = statuses.includes( 'error' )   ? 'error'
                         : statuses.includes( 'warning' ) ? 'warning' : 'ok';
          if ( dot ) {
            dot.className = 'js-audit-status-dot js-audit-a11y-dot is-' + overall;
            dot.title     = 'Accessibilità: ' + ( overall === 'ok' ? 'ok' : overall === 'warning' ? 'attenzione' : 'errori' );
          }
        } else {
          results.innerHTML = '<p class="js-audit-error">✗ ' + ( json.data?.message || 'Errore' ) + '</p>';
          results.hidden    = false;
          if ( dot ) dot.className = 'js-audit-status-dot js-audit-a11y-dot is-error';
        }
      } catch {
        results.innerHTML = '<p class="js-audit-error">✗ Errore di rete.</p>';
        results.hidden    = false;
        if ( dot ) dot.className = 'js-audit-status-dot js-audit-a11y-dot is-error';
      } finally {
        this.disabled    = false;
        this.textContent = 'Accessibilità';
      }
    } );
  } );

  function buildA11yTable( r ) {
    const icon = s => s === 'ok'      ? '<span class="js-audit-icon is-ok">✓</span>'
                    : s === 'error'   ? '<span class="js-audit-icon is-error">✗</span>'
                    : s === 'warning' ? '<span class="js-audit-icon is-warning">!</span>'
                    :                   '<span class="js-audit-icon is-info">·</span>';

    const row = ( label, status, msg, detail = '' ) =>
      `<tr class="js-audit-check is-${status}">
        <td class="js-audit-check-icon">${icon( status )}</td>
        <td class="js-audit-check-label">${label}</td>
        <td class="js-audit-check-msg">${msg}${detail ? '<span class="js-audit-detail">' + detail + '</span>' : ''}</td>
      </tr>`;

    // Contrast issues detail
    let contrastDetail = '';
    if ( r.contrast.issues && r.contrast.issues.length ) {
      contrastDetail = r.contrast.issues.slice( 0, 4 ).map( i =>
        `<code>${esc(i.tag)}</code> ${i.text ? '"' + esc( i.text ) + '"' : ''} — ${i.ratio}:1 (min 4.5)`
      ).join( '<br>' );
      if ( r.contrast.issues.length > 4 ) contrastDetail += `<br>… e altri ${ r.contrast.issues.length - 4 }`;
    } else if ( r.contrast.note ) {
      contrastDetail = '<em>' + esc( r.contrast.note ) + '</em>';
    }

    // Images detail
    const imgDetail = r.images_alt.decorative > 0
      ? `${r.images_alt.decorative} decorative (alt vuoto — corretto)`
      : '';

    // Links detail
    const linksDetail = ( r.links.bad > 0 || r.links.empty > 0 )
      ? [ r.links.empty > 0 ? `${r.links.empty} vuoti` : '', r.links.bad > 0 ? `${r.links.bad} non descrittivi` : '' ].filter(Boolean).join(', ')
      : '';

    const heading = '<div class="js-audit-section-label">♿ Accessibilità</div>';

    return heading + `<table class="js-audit-table">
      <tbody>
        ${row( 'Lingua',         r.lang.status,        esc( r.lang.message ) )}
        ${row( 'Skip link',      r.skip_link.status,   esc( r.skip_link.message ) )}
        ${row( 'Alt immagini',   r.images_alt.status,  esc( r.images_alt.message ),  imgDetail )}
        ${row( 'Label moduli',   r.form_labels.status, esc( r.form_labels.message ) )}
        ${row( 'Bottoni',        r.buttons.status,     esc( r.buttons.message ) )}
        ${row( 'Link',           r.links.status,       esc( r.links.message ),       linksDetail )}
        ${row( 'iframe title',   r.iframes.status,     esc( r.iframes.message ) )}
        ${row( 'Video caption',  r.videos.status,      esc( r.videos.message ) )}
        ${row( 'Tabindex',       r.tabindex.status,    esc( r.tabindex.message ) )}
        ${row( 'Tabelle',        r.tables.status,      esc( r.tables.message ) )}
        ${row( 'Contrasto',      r.contrast.status,    esc( r.contrast.message ),    contrastDetail )}
      </tbody>
    </table>`;
  }

  /* ----------------------------------------------------------
     Meta description inline editor
     ---------------------------------------------------------- */

  function updateMetaCounter( ta ) {
    const footer  = ta.closest( '.js-meta-edit' );
    const counter = footer.querySelector( '.js-meta-counter' );
    const len     = ta.value.length;
    counter.textContent = len + ' / 160';
    counter.className   = 'js-meta-counter' + ( len === 0 || ( len >= 50 && len <= 160 ) ? ' is-ok' : ' is-warn' );
  }

  document.querySelectorAll( '.js-meta-toggle' ).forEach( btn => {
    btn.addEventListener( 'click', function () {
      const form = this.closest( '.js-audit-row' ).querySelector( '.js-meta-edit' );
      form.hidden = ! form.hidden;
      if ( ! form.hidden ) {
        const ta = form.querySelector( '.js-meta-textarea' );
        updateMetaCounter( ta );
        ta.focus();
      }
    } );
  } );

  document.querySelectorAll( '.js-meta-textarea' ).forEach( ta => {
    ta.addEventListener( 'input', function () { updateMetaCounter( this ); } );
  } );

  document.querySelectorAll( '.js-meta-save' ).forEach( btn => {
    btn.addEventListener( 'click', async function () {
      const form   = this.closest( '.js-meta-edit' );
      const ta     = form.querySelector( '.js-meta-textarea' );
      const msg    = form.querySelector( '.js-meta-save-msg' );
      const toggle = this.closest( '.js-audit-row' ).querySelector( '.js-meta-toggle' );

      this.disabled = true;
      msg.textContent = '';

      const fd = new FormData();
      fd.append( 'action',           'js_save_meta_description' );
      fd.append( 'nonce',            this.dataset.nonce );
      fd.append( 'post_id',          this.dataset.postId );
      fd.append( 'meta_description', ta.value );

      try {
        const res  = await fetch( jsAdminData.ajaxUrl, { method: 'POST', body: fd } );
        const json = await res.json();
        if ( json.success ) {
          msg.textContent = '✓ Salvato';
          msg.className   = 'js-meta-save-msg is-ok';
          toggle.classList.toggle( 'has-value', ta.value.length > 0 );
        } else {
          msg.textContent = '✗ ' + ( json.data?.message || 'Errore' );
          msg.className   = 'js-meta-save-msg is-error';
        }
      } catch {
        msg.textContent = '✗ Errore di rete';
        msg.className   = 'js-meta-save-msg is-error';
      } finally {
        this.disabled = false;
        setTimeout( () => { msg.textContent = ''; }, 3000 );
      }
    } );
  } );

  /* ----------------------------------------------------------
     Helpers
     ---------------------------------------------------------- */

} )();
