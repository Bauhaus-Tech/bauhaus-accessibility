# W-008 — Clear the Sienna lint warning

## Requirement map

| Requirement | Production boundary | Proof |
|---|---|---|
| R1 | Local Sienna bundle read in `SiennaWidget` | `composer lint` |

## Quality-gate evidence

- RED: `composer lint` reported 0 errors and 1 warning at
  `includes/Frontend/SiennaWidget.php:82`. The warning incorrectly recommended
  an HTTP request for a local bundled asset, and the command exited with status 1.
- GREEN: the same command completed successfully after adding a suppression only
  to the local file read, with a comment explaining why an HTTP request is not
  appropriate.

### Green command output

Command: `composer lint`

```text

```

Exit status: 0. The command emitted no output.

- Regression: `composer test` passed with 33 tests and 56 assertions.
- Static analysis: `composer analyse` passed with no errors.
