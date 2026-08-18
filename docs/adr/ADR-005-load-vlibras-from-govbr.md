# ADR-005 — Load VLibras from gov.br

## Status

Accepted.

## Context

The plugin previously distributed a local copy of the VLibras widget script and
loaded its remaining assets from `vlibras.gov.br`. A WordPress.org review raised
obfuscation and licensing concerns about distributing that copy.

The VLibras documentation identifies `https://vlibras.gov.br/app/vlibras-plugin.js`
as the official widget script. The existing integration also passes the configured
left or right position to the widget initializer.

## Options considered

1. Continue distributing the local script and document its source and licence.
2. Load the official gov.br script while preserving the existing root-path and
   position initializer.
3. Use the documented minimal initializer, which would remove the plugin's
   configured side behavior.

## Decision

Choose option 2. When VLibras is enabled, enqueue its official script directly
from gov.br and retain the existing root path and left/right position configuration.
Remove the local VLibras bundle.

## Consequences

- The plugin no longer distributes an obfuscated third-party VLibras script.
- VLibras depends on the availability of the government service whenever enabled.
- The plugin's installation documentation must disclose the external script and
  runtime-asset requests.
- The configured left/right widget placement remains available.
