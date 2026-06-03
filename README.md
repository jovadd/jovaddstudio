# Jovadd Studio — WordPress Theme

Custom minimal WordPress theme con Design System a token CSS. Progettato per essere leggero, semantico e facilmente personalizzabile per ogni progetto.

---

## Requisiti

| Requisito | Versione minima |
|-----------|----------------|
| WordPress | 6.0 |
| PHP | 8.0 |
| Browser | tutti i moderni (no IE) |

---

## Installazione

1. Clona o scarica la repository nella cartella dei temi WordPress:
   ```
   wp-content/themes/jovaddstudio/
   ```
2. Attiva il tema da **Aspetto → Temi** nel pannello di amministrazione WordPress.
3. Carica i font personalizzati (se previsti) nella cartella `assets/fonts/` — questa cartella è esclusa dal repository tramite `.gitignore`.

---

## Struttura del progetto

```
jovaddstudio/
├── assets/
│   ├── css/
│   │   ├── tokens.css       # Design tokens (colori, spazi, tipografia)
│   │   ├── framework.css    # Reset, layout, utility classes
│   │   ├── components.css   # Componenti riutilizzabili (btn, card, badge…)
│   │   └── admin.css        # Stili pannello admin
│   ├── fonts/               # Font locali (escluso da git)
│   └── js/
│       ├── main.js          # JS frontend (nav mobile, ecc.)
│       └── admin.js         # JS pannello admin
├── inc/
│   ├── breadcrumbs.php      # Funzione js_breadcrumbs() + JSON-LD
│   ├── helpers.php          # js_get_option() e utility
│   ├── maintenance.php      # Modalità manutenzione
│   ├── performance.php      # Hook di ottimizzazione frontend
│   ├── security.php         # Hook di sicurezza
│   └── admin/
│       ├── duplicate-post.php        # Duplica articoli e pagine
│       ├── options-page.php          # Pagina opzioni admin
│       ├── options-register.php      # Registrazione impostazioni
│       ├── options-assets.php        # CSS/JS admin
│       ├── ajax-fonts.php            # Download Google Fonts
│       ├── ajax-rename-theme.php     # Rinomina tema
│       ├── ajax-seo-audit.php        # Audit SEO on-demand
│       └── ajax-accessibility-audit.php  # Audit accessibilità
├── 404.php
├── footer.php
├── functions.php
├── header.php
├── index.php        # Blog / archivi / ricerca
├── page.php         # Pagine statiche
├── single.php       # Singolo articolo
├── maintenance.php
├── style.css        # Header tema WordPress
└── screenshot.png
```

---

## Design System

Il tema è basato su un sistema CSS a tre livelli che non vanno mai mescolati tra loro.

### 1. `tokens.css` — Variabili CSS

Contiene tutti i valori di design: colori, tipografia, spazi, ombre, border-radius, z-index e transizioni. **È l'unico file da modificare per personalizzare il tema su un nuovo progetto.**

```css
/* Esempio: cambiare il colore primario */
--color-primary: var(--brand-500);
--color-primary-hover: var(--brand-950);
```

**Scala tipografica fluida** (base `1rem = 10px`):

| Classe | Dimensione |
|--------|-----------|
| `.text-xs` | 1.1–1.2rem |
| `.text-s` | 1.3–1.4rem |
| `.text-m` | 1.5–1.6rem |
| `.text-l` | 1.8–2rem |
| `.text-xl` | 2.2–2.6rem |
| `.text-2xl` | 2.8–3.4rem |
| `.text-3xl` | 3.6–4.6rem |
| `.text-4xl` | 4.8–6.4rem |
| `.text-display` | 4.8–6.4rem + bold + tight |

---

### 2. `framework.css` — Layout e Utility

Reset, tipografia base e classi di utilità. **Non modificare questo file.**

#### Layout

```html
<!-- Contenitore standard (max 1440px) -->
<div class="container">

<!-- Contenitore testo (max 72ch) -->
<div class="container-s">

<!-- Griglia responsive -->
<div class="grid grid-3">   <!-- 3 col → 2 su tablet → 1 su mobile -->
<div class="grid grid-2">   <!-- 2 col → 1 su mobile piccolo -->

<!-- Col span -->
<div class="col-span-2">
```

#### Flex

```html
<div class="flex items-center justify-between gap-4">
<div class="flex flex-col flex-1 gap-4">
```

#### Spacing

```html
<!-- Padding -->
<div class="p-6">       <!-- padding su tutti i lati -->
<div class="px-6 py-4"> <!-- padding inline / block -->

<!-- Margin -->
<div class="mt-8 mb-4">
<div class="mx-auto">
```

#### Testo

```html
<p class="text-muted text-s clamp-3">   <!-- grigio, piccolo, max 3 righe -->
<h1 class="text-display uppercase">     <!-- titolo hero -->
<span class="font-bold tracking-wide">
```

#### Utility visive

```html
<div class="rounded-m shadow-s border bg-surface">
<img class="w-full h-full object-cover aspect-video">
<span class="truncate">           <!-- overflow ellipsis su una riga -->
<span class="clamp-2">           <!-- max 2 righe con ellipsis -->
<span class="sr-only">           <!-- visibile solo a screen reader -->
```

---

### 3. `components.css` — Componenti

#### Button

```html
<a href="#" class="btn">Primario</a>
<a href="#" class="btn btn-outline">Outline</a>
<a href="#" class="btn btn-ghost">Ghost</a>

<!-- Dimensioni -->
<a href="#" class="btn btn-s">Piccolo</a>
<a href="#" class="btn btn-l">Grande</a>
```

#### Card

La card non ha padding proprio: l'immagine di copertina è full-bleed, il contenuto usa `.card-body`.

```html
<article class="card">
  <a href="#" class="block aspect-video overflow-hidden">
    <img class="w-full h-full object-cover" src="..." alt="...">
  </a>
  <div class="card-body">
    <span class="badge badge-primary">Categoria</span>
    <h2>Titolo</h2>
    <p>Excerpt…</p>
  </div>
</article>

<!-- Varianti -->
<div class="card card-flat">    <!-- senza ombra -->
<div class="card card-raised">  <!-- ombra più pronunciata -->
```

#### Badge

```html
<span class="badge">Neutro</span>
<span class="badge badge-primary">Categoria</span>
<span class="badge badge-success">Pubblicato</span>
<span class="badge badge-error">Bozza</span>
<span class="badge badge-warning">In revisione</span>
```

#### Alert

```html
<div class="alert alert-info">Messaggio informativo</div>
<div class="alert alert-success">Operazione riuscita</div>
<div class="alert alert-error">Errore</div>
<div class="alert alert-warning">Attenzione</div>
```

#### Form

```html
<div class="form-group">
  <label class="form-label">Nome</label>
  <input type="text" class="input" placeholder="Mario Rossi">
  <span class="form-hint">Testo di aiuto</span>
</div>
```

---

## Template

### `index.php`
Template di fallback per: **blog**, **ricerca** (`is_search()`), **archivi di categoria**, **archivi di tag**, **archivi autore** e qualsiasi altro archivio senza template dedicato.

- Mostra un header contestuale con titolo e contatore risultati per le ricerche
- Griglia responsive a 3 colonne di card articolo
- Ogni card include: immagine, categoria, titolo, excerpt, tag, autore, pulsante "Scopri di più"
- Paginazione

### `single.php`
Template per i **singoli articoli**.

- Breadcrumb con JSON-LD `BreadcrumbList`
- Hero a due colonne: titolo grande (`.text-display`) + metadati a sinistra, immagine a destra
- Contenuto articolo in `.container-s`
- Sezione "Leggi anche" con articoli correlati dalla stessa categoria (max 3, solo se presenti)

### `page.php`
Template per le **pagine statiche**.

- Titolo `<h1>` + contenuto in `.container` full-width
- Nessun breadcrumb né sidebar

---

## Funzionalità Admin

### Duplica articoli e pagine
Nel listato articoli e pagine di WordPress compare il link **"Duplica"** nelle azioni riga. Crea una bozza con:
- Stessi contenuto, excerpt e attributi
- Tutti i meta del post originale
- Categorie, tag e tassonomie personalizzate

### Pagina Opzioni
Accessibile da **Aspetto → Opzioni Tema**. Include tab per:
- Impostazioni generali
- Tipografia (con Google Fonts downloader)
- SEO
- Accessibilità
- Manutenzione
- e altri

### Breadcrumb
La funzione `js_breadcrumbs()` è disponibile in qualsiasi template:
```php
<?php js_breadcrumbs(); ?>
```
Genera automaticamente HTML semantico (`<nav>` + `<ol>`) e il markup `JSON-LD BreadcrumbList` per i rich results di Google.

---

## Responsive

| Breakpoint | Layout griglia |
|------------|---------------|
| > 1023px | `.grid-3` → 3 colonne |
| 768–1023px | `.grid-3` → 2 colonne |
| ≤ 767px | `.grid-3` → 1 colonna |
| ≤ 479px | `.grid-2` → 1 colonna |

---

## Licenza

Questo tema è distribuito sotto licenza **GNU General Public License v2 or later (GPL-2.0+)**.

Puoi usarlo, modificarlo e ridistribuirlo liberamente nel rispetto dei termini della licenza.
Maggiori informazioni: https://www.gnu.org/licenses/gpl-2.0.html

© Giovanni Caserta / Jovadd
