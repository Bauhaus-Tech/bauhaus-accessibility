# PHASE-10 — Sienna 2.0.1 local-assets fork

## Goal

Create a public, maintainable fork of the available Sienna 2.0.1 source under
the Bauhaus-Tech organization. Its distributable browser bundle must load its
fonts and locale data from files packaged with the fork, allowing WordPress
sites to run the toolbar without third-party asset requests.

## In scope

1. R1: Create the public GitHub fork
   `Bauhaus-Tech/Sienna-Accessibility-Widget` from the upstream public 2.0.1
   source while retaining the upstream MIT licence and provenance.
2. R2: Change the fork source and build output so toolbar fonts and locale data
   are available locally and no browser request for those assets targets an
   external host.
3. R3: Add automated proof that the built UMD bundle has no external font or
   locale URLs and that the required local asset files are present in the
   distributable output.
4. R4: Document the public source location, MIT attribution, local-assets
   policy, and reproducible build/release commands in the fork README.
5. R5: Use a local browser check to prove the retained 2.0.1 toolbar opens and
   its font-size, contrast, and readable-font controls work with local assets.

## Out of scope

- Replacing the Sienna 2.2.333 bundle in the WordPress plugin.
- Preserving features that exist only in the unavailable 2.2.333 source.
- Renaming the fork, its public API, or its user-facing toolbar during this
  compatibility-focused phase.
- Publishing the fork to npm or WordPress.org.

## Acceptance criteria

| # | Criterion | Verified by |
|---|---|---|
| A1 | A public Bauhaus-Tech fork exposes readable 2.0.1 source, build tooling, and the upstream MIT licence. | GitHub repository inspection and README review |
| A2 | A release build packages every font and locale file the toolbar needs and contains no external URL for those resources. | Fork build test |
| A3 | The fork README lets a maintainer reproduce the UMD output and understand its local-asset policy. | Documentation review |
| A4 | A local browser session opens the toolbar and proves font size, contrast, and readable font controls work without external asset requests. | Browser-test receipt in the fork |

## Risks / open questions

- The configured GitHub command-line credentials are currently invalid, so the
  owner must reauthenticate before the public repository can be created.
- The upstream 2.0.1 build uses a different feature set from the plugin's
  existing 2.2.333 UMD bundle; replacement remains a later, separately approved
  phase.
- Upstream build dependencies may need security maintenance in a later phase;
  this phase changes only what local asset delivery requires.

## Amendment 1

The upstream 2.0.1 source bundles its locale JSON modules directly into the
UMD rather than requesting locale files at runtime. The owner approved keeping
that single local representation: R2 and A2 therefore require bundled locale
data with no external locale URL, while the readable-font files are copied into
the distributable output as local files. Emitting separate locale files is out
of scope because it would duplicate the source of truth without a runtime need.
