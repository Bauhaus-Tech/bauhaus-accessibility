# W-010 — Sienna 2.0.1 local-assets fork

## Requirement map

| Requirement | Production boundary | Proof |
|---|---|---|
| R1 | Public `Bauhaus-Tech/Sienna-Accessibility-Widget` fork | GitHub repository inspection |
| R2 | Fork font resolver and build asset copy | `test/local-assets.test.mjs` |
| R3 | Fork UMD release output | `npm test` |
| R4 | Fork README | Documentation review |
| R5 | Fork local UMD browser fixture | `docs/verification/phase-10-local-assets.md` |

## TDD evidence

- RED: `node test/local-assets.test.mjs` failed because the release UMD did not
  yet exist, proving the release boundary was not established.
- GREEN: `npm test` built all bundle formats, copied both local font files into
  `dist/fonts/`, and passed the test that rejects external font and locale URLs.

## Browser evidence

- A local Chromium run opened the built UMD at a loopback origin, increased
  font size, enabled high contrast, and enabled readable font. Its only network
  requests were the fixture, the UMD, and the local WOFF font.
- Detailed durable receipt: the fork's
  `docs/verification/phase-10-local-assets.md`.

## Review follow-up

- Fork commit `da6af85` adds a committed Playwright browser test. `npm test`
  passed with 2 tests and 0 failures in the unrestricted environment required
  for the loopback fixture server.
- The full upstream lint command still has five pre-existing errors and four
  warnings in untouched files. The owner approved a separate maintenance phase
  to address them.
