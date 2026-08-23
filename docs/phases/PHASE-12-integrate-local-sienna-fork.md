# PHASE-12 — Integrate the local Sienna fork

## Goal

Replace the plugin's CDN-referencing Sienna 2.2.333 bundle with the published
Bauhaus 2.0.1 fork build and remove all Sienna remote-asset rewriting.

## In scope

1. Replace the plugin UMD and font assets from fork commit `c8111e7`.
2. Remove `CDN_BASE` and runtime bundle rewriting from `SiennaWidget`.
3. Prove plugin distribution and browser execution make no remote Sienna asset
   requests.
4. Link the public source and build instructions in `readme.txt`.

## Out of scope

- VLibras service requests.
- New toolbar features or preserving unavailable 2.2.333-only features.

## Acceptance criteria

| # | Criterion | Verified by |
|---|---|---|
| A1 | No Sienna CDN URL remains in plugin PHP or UMD assets. | Plugin tests and repository scan |
| A2 | Sienna font assets are packaged locally with the plugin. | Distribution test |
| A3 | The enabled toolbar works without remote Sienna requests. | Local browser test |
| A4 | `readme.txt` identifies the public Bauhaus source and build instructions. | Documentation review |

## Risks / open questions

- The 2.0.1 toolbar has fewer features than the current 2.2.333 UMD. This is
  an approved compatibility trade-off based on the client's existing v1.1 use.
- The fork distribution files are generated, but ADR-006 explicitly designates
  the reproducible runtime UMD and font files as committed plugin assets.
