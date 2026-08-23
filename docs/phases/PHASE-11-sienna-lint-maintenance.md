# PHASE-11 — Sienna fork lint maintenance

## Goal

Resolve the inherited lint errors and warnings in the public Bauhaus-Tech
Sienna fork without changing the toolbar's observable behavior.

## In scope

1. R1: Fix the five ESLint errors and four warnings reported by `npm run lint`.
2. R2: Preserve the local-assets build and browser behavior established in
   Phase 10.
3. R3: Publish the clean lint result and regression-test evidence.

## Out of scope

- New toolbar features or visual redesign.
- Replacing the WordPress plugin's current Sienna bundle.
- Dependency upgrades beyond changes required to resolve lint configuration.

## Acceptance criteria

| # | Criterion | Verified by |
|---|---|---|
| A1 | `npm run lint` completes with no errors or warnings. | Fork lint command |
| A2 | Local asset and browser tests still pass. | `npm test` |
| A3 | The clean fork state is published to Bauhaus-Tech. | GitHub repository inspection |

## Risks / open questions

- Existing TypeScript suppressions may reveal typing issues when corrected;
  each correction requires a focused regression proof.
