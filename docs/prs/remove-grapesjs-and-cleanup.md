# chore: Remove GrapesJS visual editor and related assets; switch Font Awesome to local fonts

## Summary
- Completely removed the GrapesJS visual editor from the application.
- Deleted routes, controller methods, Blade views, frontend assets, and tests tied to GrapesJS.
- Cleaned up Livewire references to the visual editor.
- Removed an unused Carbon/BuySellAds ad script.
- Kept Font Awesome usage by pointing `font-awesome.css` to local fonts (no dependency on grapesjs.com).

## Changes

- __Routes__
  - Removed GrapesJS routes from `routes/web.php`:
    - `/visual-editor/{pageid}`
    - `/visual-editor/getCombinedHtml/{pageid}`

- __Controllers__
  - Deleted GrapesJS methods in `app/Http/Controllers/SitePageController.php`:
    - `visualEditor()`
    - `getCombinedHtml()`

- __Views__
  - Deleted GrapesJS views:
    - `resources/views/sitepage/grapes-updated.blade.php`
    - `resources/views/sitepage/visual-editor.blade.php`
  - Updated Livewire page editor view to remove GrapesJS include:
    - `resources/views/livewire/site-page-editor.blade.php` (removed `@include('sitepage.visual-editor')` block)

- __Livewire Component__
  - `app/Livewire/SitePageEditor.php`:
    - Removed `isVisualEditorOpen` state.
    - Removed `openVisualModal()` and `visualEditor()` methods.
    - Cleaned up `closeModal()` accordingly.

- __Frontend assets__
  - Removed GrapesJS JS/CSS/IMG assets:
    - `public/js/grapes.min.js`
    - `public/js/grapes-visual-editor/` (entire dir)
    - `public/css/grapes*.css`
    - `public/img/grapes*`
  - Removed unused ad loader:
    - `public/js/carbon1b26.js`
  - Font Awesome now points to local fonts:
    - Updated `public/css/font-awesome.css` `@font-face` to use `/fonts/fontawesome-webfont.woff2` and added `font-display: swap`.

- __Tests__
  - Removed GrapesJS-specific test:
    - `tests/Unit/VisualEditorTest.php`
  - Verified no remaining test references to GrapesJS routes or views.

## Rationale
- GrapesJS is no longer desired in the product. Removing it avoids dead links, unused code, third‑party scripts, and maintenance overhead. Font Awesome remains supported without depending on GrapesJS-hosted fonts.

## Verification
- Searched codebase for `grapes` and `visual-editor` references; removed/directly addressed remaining occurrences.
- App compiles/loads without missing route/view errors from GrapesJS.
- Icons continue to render using local Font Awesome fonts, provided `font-awesome.css` is included by the base layout.

## Follow-ups (optional)
- Ensure base layout includes Font Awesome stylesheet if icons are needed globally, e.g.: `<link rel="stylesheet" href="/css/font-awesome.css">`.
- Run:
  - `php artisan optimize:clear`
  - `composer dump-autoload -o`
  - Test suite: `vendor/bin/phpunit`

## Risk
- Low. All removed pieces were GrapesJS-specific and unused after route/controller/view removal. Font Awesome kept with a local font path to avoid external dependencies.
