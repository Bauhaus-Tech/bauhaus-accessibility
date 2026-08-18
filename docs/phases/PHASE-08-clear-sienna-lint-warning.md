# PHASE-08 — Clear the Sienna lint warning

## Goal

The project-wide PHP lint command completes successfully without changing the
runtime behavior of the locally bundled Sienna widget.

## In scope

1. R1: Resolve the current PHP coding-standard warning for the local Sienna
   bundle read in `SiennaWidget` and prove the full lint command succeeds.

## Out of scope

- Changing Sienna widget behavior, its assets, or its runtime source.
- Refactoring unrelated Sienna code.
- Changing the VLibras integration.

## Acceptance criteria

| # | Criterion | Verified by |
|---|---|---|
| A1 | `composer lint` completes successfully with no errors or warnings. | Recorded command output in the phase evidence |

## Risks / open questions

- The local bundle read is intentional. Any coding-standard suppression must be
  limited to that operation and explain why an HTTP request is not appropriate.
