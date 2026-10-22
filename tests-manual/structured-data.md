# Converted-page structured data

Use the saved MAMP HTTPS preview without changing its DocumentRoot. Confirm the
nine canonical routes render normally with the approved titles and descriptions.

- Each page has exactly one `application/ld+json` script whose text exactly
  matches its private `public_content/<route>/structured-data.json`.
- Decode the script: WebPage name/description match that route's metadata; URLs
  use `https://mastering-the-mundane.com` plus the route, not the preview host.
- Only Coaching contains Service. Resource pages contain no Article, invented
  dates, ratings or service claims. All graphs identify the same site/provider.
- Direct `/structured-data.json` and route-relative `structured-data.json` HTTP
  requests return 404. The JSON artifacts are not public downloads.
- `composer compile-content` followed by `composer check-content` verifies 18
  artifacts. Metadata or generator changes require regeneration; build checks
  detect stale JSON without writing. Runtime reads saved JSON without graph
  regeneration and rejects missing/invalid artifacts with a path and command.
- The Time book detail and Books catalog use generated WebPage graphs, with no
  Book/Offer assertions. `/time/` remains legacy. The orphaned resource
  `article.html` JSON-LD-only files have been removed without redirects.

Automated tests cover script escaping (including `</script>`), exact embedding,
scope, metadata consistency, deterministic output, freshness and invalid inputs.
These checks do not imply search-engine eligibility or ranking improvements.
