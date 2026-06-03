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
     Font autocomplete
     ---------------------------------------------------------- */

  let fontsCache = null;

  async function loadFontsList() {
    if ( fontsCache ) return fontsCache;

    const body = new FormData();
    body.append( 'action', 'js_get_fonts_list' );
    body.append( 'nonce',  jsAdminData.nonce );

    const res  = await fetch( jsAdminData.ajaxUrl, { method: 'POST', body } );
    const json = await res.json();

    if ( json.success ) {
      fontsCache = json.data.fonts;
      return fontsCache;
    }
    return [];
  }

  // Load fonts cache when clicking the button
  const loadFontsBtn = document.querySelector( '.js-load-fonts-btn' );
  if ( loadFontsBtn ) {
    loadFontsBtn.addEventListener( 'click', async function () {
      const status = document.querySelector( '.js-fonts-cache-status' );
      this.disabled = true;
      if ( status ) status.textContent = 'Caricamento…';

      const fonts = await loadFontsList();

      if ( fonts.length ) {
        if ( status ) status.textContent = fonts.length + ' font disponibili.';
      } else {
        if ( status ) status.textContent = 'Errore. Verifica l\'API Key.';
      }
      this.disabled = false;
    } );
  }

  // Autocomplete for each font search input
  document.querySelectorAll( '.js-font-autocomplete-wrap' ).forEach( wrap => {
    const input       = wrap.querySelector( '.js-font-name-input' );
    const suggestions = wrap.querySelector( '.js-font-suggestions' );
    if ( ! input || ! suggestions ) return;

    let focused = -1;

    function showSuggestions( query ) {
      suggestions.innerHTML = '';
      suggestions.hidden    = true;
      if ( ! query || ! fontsCache ) return;

      const q       = query.toLowerCase();
      const results = fontsCache.filter( f => f.family.toLowerCase().includes( q ) ).slice( 0, 12 );

      if ( ! results.length ) return;

      results.forEach( ( font, i ) => {
        const li = document.createElement( 'li' );
        li.textContent = font.family;
        li.dataset.family = font.family;

        const cat = document.createElement( 'span' );
        cat.className   = 'js-font-category-tag';
        cat.textContent = font.category;
        li.appendChild( cat );

        li.addEventListener( 'mousedown', e => {
          e.preventDefault();
          selectFont( font.family );
        } );
        suggestions.appendChild( li );
      } );

      focused           = -1;
      suggestions.hidden = false;
    }

    function selectFont( family ) {
      input.value         = family;
      suggestions.hidden  = true;
      // Also update hidden input
      const picker       = input.closest( '.js-font-picker' );
      const hiddenInput  = picker && picker.querySelector( '.js-font-family-hidden' );
      if ( hiddenInput ) hiddenInput.value = family;
    }

    function navigateSuggestions( dir ) {
      const items = suggestions.querySelectorAll( 'li' );
      if ( ! items.length ) return;
      items.forEach( el => el.classList.remove( 'is-focused' ) );
      focused = ( focused + dir + items.length ) % items.length;
      items[ focused ].classList.add( 'is-focused' );
    }

    input.addEventListener( 'input', async () => {
      if ( ! fontsCache ) await loadFontsList();
      showSuggestions( input.value.trim() );
    } );

    input.addEventListener( 'focus', async () => {
      if ( ! fontsCache ) await loadFontsList();
      if ( input.value ) showSuggestions( input.value.trim() );
    } );

    input.addEventListener( 'blur',    () => { setTimeout( () => { suggestions.hidden = true; }, 150 ); } );
    input.addEventListener( 'keydown', e => {
      if ( e.key === 'ArrowDown' )  { e.preventDefault(); navigateSuggestions( 1 ); return; }
      if ( e.key === 'ArrowUp' )    { e.preventDefault(); navigateSuggestions( -1 ); return; }
      if ( e.key === 'Enter' && ! suggestions.hidden ) {
        e.preventDefault();
        const focused_el = suggestions.querySelector( '.is-focused' );
        if ( focused_el ) selectFont( focused_el.dataset.family );
        return;
      }
      if ( e.key === 'Escape' ) suggestions.hidden = true;
    } );
  } );

  /* ----------------------------------------------------------
     Font download
     ---------------------------------------------------------- */

  document.querySelectorAll( '.js-font-download-btn' ).forEach( btn => {
    btn.addEventListener( 'click', async function () {
      const picker      = this.closest( '.js-font-picker' );
      const nameInput   = picker.querySelector( '.js-font-name-input' );
      const roleInput   = picker.querySelector( '.js-font-role' );
      const weightBoxes = picker.querySelectorAll( '.js-font-weight-check:checked' );
      const status      = picker.querySelector( '.js-font-status' );

      const fontName = nameInput && nameInput.value.trim();
      if ( ! fontName ) {
        setStatus( status, jsAdminData.strings.error_empty, 'error' );
        return;
      }

      const weights = Array.from( weightBoxes ).map( el => el.value );
      setStatus( status, jsAdminData.strings.downloading, 'loading' );
      this.disabled = true;

      const body = new FormData();
      body.append( 'action',      'js_download_font' );
      body.append( 'nonce',       jsAdminData.nonce );
      body.append( 'font_family', fontName );
      body.append( 'font_role',   roleInput ? roleInput.value : 'heading' );
      body.append( 'weights',     JSON.stringify( weights ) );

      try {
        const res  = await fetch( jsAdminData.ajaxUrl, { method: 'POST', body } );
        const json = await res.json();

        if ( json.success ) {
          const msg = '✓ ' + json.data.message;
          setStatus( status, msg, 'success' );

          // Update installed badge
          const installed   = picker.querySelector( '.js-font-installed' );
          const nameBadge   = picker.querySelector( '.js-installed-font-name' );
          const hiddenInput = picker.querySelector( '.js-font-family-hidden' );
          if ( installed )   installed.hidden = false;
          if ( nameBadge )   nameBadge.textContent = fontName;
          if ( hiddenInput ) hiddenInput.value = fontName;
        } else {
          setStatus( status, '✗ ' + ( json.data && json.data.message ? json.data.message : 'Errore.' ), 'error' );
        }
      } catch {
        setStatus( status, '✗ ' + jsAdminData.strings.error_network, 'error' );
      } finally {
        this.disabled = false;
      }
    } );
  } );

  /* ----------------------------------------------------------
     Font remove
     ---------------------------------------------------------- */

  document.querySelectorAll( '.js-font-remove' ).forEach( btn => {
    btn.addEventListener( 'click', async function () {
      const role   = this.dataset.role;
      const picker = this.closest( '.js-font-picker' );

      const body = new FormData();
      body.append( 'action',    'js_remove_font' );
      body.append( 'nonce',     jsAdminData.nonce );
      body.append( 'font_role', role );

      await fetch( jsAdminData.ajaxUrl, { method: 'POST', body } );

      // Reset UI
      const installed   = picker.querySelector( '.js-font-installed' );
      const nameBadge   = picker.querySelector( '.js-installed-font-name' );
      const nameInput   = picker.querySelector( '.js-font-name-input' );
      const hiddenInput = picker.querySelector( '.js-font-family-hidden' );
      if ( installed )   installed.hidden = true;
      if ( nameBadge )   nameBadge.textContent = '';
      if ( nameInput )   nameInput.value = '';
      if ( hiddenInput ) hiddenInput.value = '';
    } );
  } );

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
     Helpers
     ---------------------------------------------------------- */

  function setStatus( el, text, type ) {
    if ( ! el ) return;
    el.textContent = text;
    el.className   = 'js-font-status is-' + type;
  }

} )();
