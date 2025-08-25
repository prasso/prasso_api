# Make Site Page Editor Description a Multiline Textarea in Filament

## Summary
- Converted the `description` field to a taller multiline textarea on the Site Admin edit page at `/site-admin/site-pages/{id}/edit`.
- Improves usability when editing longer page content.

## Changes
- Updated Filament form schema to explicitly render a larger textarea for `description`.
- This only affects the Site Admin panel (Filament) and does not change database schema or API behavior.

## Files Modified
- `app/Filament/Resources/SitePageResource.php`
  - `Forms\Components\Textarea::make('description')` now uses `->rows(12)` and retains `->columnSpanFull()`.

## Rationale
- The existing input could appear too small for longer content, causing poor editing experience. Setting `rows(12)` makes the field clearly multiline.

## How to Test
1. Log in to the Site Admin panel.
2. Navigate to: `http://localhost:8000/site-admin/site-pages/13/edit` (or any page record).
3. Confirm the `Description` field renders as a multiline textarea with ample height.
4. Optionally clear caches if you don’t see the change immediately:
   - `php artisan optimize:clear`

## Notes
- Livewire modal editor (`resources/views/sitepage/create-or-edit.blade.php`) already used a `<textarea>` for `description`; this PR aligns the Filament editor with that UX.
- No database or migration changes.
- No breaking changes expected.

## Screenshots (Optional)
- N/A
