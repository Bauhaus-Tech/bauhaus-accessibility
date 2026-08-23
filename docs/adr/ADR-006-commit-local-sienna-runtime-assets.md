# ADR-006 — Commit local Sienna runtime assets

## Status

Accepted.

## Context

WordPress.org requires plugin JavaScript, fonts, and other static dependencies
to be packaged locally unless they are part of an approved external service.
The Sienna toolbar is a local client-side feature, so its UMD bundle and
OpenDyslexic font files must ship with the plugin.

The repository normally does not commit generated artifacts outside
`assets/build/`. The Sienna fork builds a distributable UMD and local fonts that
are required at runtime by an installed plugin.

## Options considered

1. Designate the reproducible Sienna runtime distribution as committed plugin
   assets.
2. Put the distribution in `assets/build/` and make the runtime load it there.
3. Do not distribute the assets and load them remotely.

## Decision

Choose option 1. Commit the reproducible UMD at
`assets/js/sienna-accessibility.umd.js` and its required font files at
`assets/js/fonts/`. Build them from the public Bauhaus Sienna fork and record
the source revision and verification in the relevant phase evidence.

## Consequences

- The plugin remains self-contained for Sienna runtime assets.
- The public fork remains the editable source of truth; the plugin stores only
  the runtime distribution it needs.
- Updating the runtime requires rebuilding from the documented fork source,
  running the distribution checks, and committing the resulting UMD and fonts
  together.
