# PHASE-04 — Admin Settings Page

## Goal
A functional settings page under Settings → Acessibilidade BR with toggles for
both widgets, a left/right position selector, and a sign language dropdown.

## In scope

- R1: "Enable VLibras Sign Language Interpreter" checkbox
- R2: "Enable Accessibility Widget" (Sienna) checkbox
- R3: "Widget position" radio buttons: Left / Right (default: Right)
- R4: "Sign language" dropdown with one option: Libras
- R5: All fields rendered using WordPress Settings API
  (add_settings_section, add_settings_field)
- R6: Settings saved and read correctly (already tested via sanitize_settings)
- R7: Settings page accessible only to manage_options users

## Out of scope

- Additional sign languages (filter-ready, but only Libras populates v1)
- Customizer integration
- Onboarding/welcome screen

## Acceptance criteria

| # | Criterion | Verified by |
|---|-----------|-------------|
| A1 | Settings page shows all 4 fields with labels | Manual: visit Settings → Acessibilidade BR |
| A2 | Checkboxes toggle correctly (save, reload, verify) | Manual: toggle, save, reload page |
| A3 | Position radio defaults to Right | Manual: fresh install, check radio |
| A4 | Sign language dropdown shows Libras | Manual: inspect dropdown options |
| A5 | Settings page is not accessible to subscribers | Manual: login as subscriber, verify no menu item |
| A6 | Settings persist across page loads | Test: get_option returns saved values |
