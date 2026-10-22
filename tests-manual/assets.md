# Public asset delivery

Use the saved MAMP HTTPS preview without changing its DocumentRoot.

- Shared pages link `/assets/css/reading.min.css?v=<xxh3>`; legacy error pages
  link `/assets/css/main.min.css?v=<xxh3>`. Both stylesheets render normally.
- Versioned CSS and font responses have `Cache-Control: public, max-age=31536000,
  immutable`. Unversioned CSS and fonts have `Cache-Control: no-cache`.
- `/css/reading.css`, `/css/main.css?v=1.1`, `/ui/mark.svg` and
  `/media/poster.png` redirect once to the equivalent `/assets/` URL. Query
  strings survive. The redirected response serves the expected file.
- Social image declarations use `/assets/media/poster.png`. The legacy Time
  source uses `/assets/media/time-cover.png`; no cover returns to the detail page.
- Existing `/favicons/` links, manifest icon URLs and browser configuration icon
  resolve without redirects or changes to branding.
- Check narrow-screen layout, native popover navigation and local-first font
  behavior after the stylesheet URL change; no third-party asset requests.
- `composer minify` is repeat-stable, and `composer check-assets` fails for a
  stale generated stylesheet rather than updating it silently.
