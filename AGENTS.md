# AGENTS.md — Operating Manual for Agents on `bauhaus-tech-support`

**Read this file completely before touching anything in this repository.**

This is the master specification for *how* work is done here. It is vendor-neutral: it
must be understandable and executable by any coding agent or human, and it contains no
tool-specific or model-specific instructions.

> **`AGENTS.md` is the single entry point for agent instructions, and no
> vendor-specific instruction file may be created alongside it.** If your runtime looks
> for a different filename, create a **symlink or a one-line pointer file** that says
> `See AGENTS.md` — never a second copy of the rules. Two copies drift; drifted rules
> are worse than no rules.

---

## 1. The non-negotiable rules

These are the owner's standing instructions. They override convenience, speed, and
any default behavior of your runtime.

1. **TDD, always.** No production code is written before a failing test exists for it. (§4.1)
2. **Stop and ask when unclear.** Never guess on anything that changes the shape of the
   system. Provide context and options. (§5)
3. **Adversarial review after every meaningful change.** Run `docs/agents/implementation-reviewer.md`
   after each phase, each new functionality, and each moderate change. (§6)
4. **Act on the review.** Implement what you agree with; for anything you disagree with
   or that needs a decision, stop and ask. Never discard a finding silently. (§6.3)
5. **Commit often.** Small, atomic, working commits. (§7)
6. **Stop at every phase boundary.** Wait for the owner's explicit approval before
   starting the next phase. (§5.1)
7. **Small phases, always.** Decompose work into the smallest slices that are still
   independently testable. If a phase looks big — at planning time or halfway through —
   split it instead of pushing through. (§5.5)
8. **Challenge the owner.** Pressure-test every instruction before implementing it.
   Agreement must be earned; silence about a concern is a process failure. (§5.6)
9. **Strictly serial execution.** Never run two subtasks at the same time — not even
   when they touch disjoint files. (§5.2)
10. **Documentation is part of the work.** `README.md` (and `MANUAL_TESTS.md` when
    applicable) is updated **in the same commit** as the behavior it describes. (§9)
11. **One instruction file.** Agent instructions live in `AGENTS.md` only — never in a
    vendor-specific instruction file alongside it.
12. **No vendor or tooling attribution anywhere in the codebase.** (§4.5)

If any rule above conflicts with a request you receive mid-task, surface the conflict
to the owner instead of choosing for yourself.

---

## 2. Engineering standards

### 2.1 TDD — the mandatory loop

Every unit of production behavior follows **RED → GREEN → REFACTOR**:

1. **RED** — write the smallest test that expresses the next required behavior.
   Run it. **Watch it fail, and fail for the right reason.** A test that passes on
   first run is not a test — it is a tautology; fix it before proceeding.
2. **GREEN** — write the minimum production code that makes it pass. Nothing more.
3. **REFACTOR** — with tests green, improve names, remove duplication, extract
   abstractions. Re-run the full suite after each refactor.
4. **Commit** — the cycle's test + code + doc update go in together.

Consequences that are not optional:

- Commit history must show tests arriving with or before their implementation. The
  reviewer inspects this ordering, because this project declares TDD.
- **Bugs start with a failing test** that reproduces the bug, then the fix. A bug fix
  with no regression test is not a fix.
- Do not write tests after the fact to "cover" code that already exists, and do not
  present that as TDD. If a shortcut was taken, say so explicitly in the handoff.
- Prefer testing observable behavior through public interfaces over asserting on
  internals. Tests that mirror the implementation line by line block refactoring.
- Mock only what you own or what crosses a process boundary (network, clock, filesystem,
  payment gateway, LLM provider). Never write a test whose only assertion is that a mock
  was called, when a real assertion on the outcome is available.
- Test names state behavior, not method names:
  `it_rejects_a_ticket_assignment_to_an_inactive_agent`, not `test_assign()`.

**The one exemption — tooling and configuration.** Files that carry no observable behavior
of their own — `composer.json`, `package.json`, `phpcs.xml`, `phpstan.neon`,
`phpunit.xml.dist`, `.wp-env.json`, CI config, the plugin header block — are exempt from
"test first", because there is nothing to assert about them beyond the tools they
configure. Conditions on the exemption:

- The phase file must **list explicitly** which files claim it. An unlisted file does not
  get the exemption.
- **A tooling file created *during* a phase must be added to that phase's list in the same
  commit that creates it**, with one sentence saying why it carries no behaviour. A phase
  file is written before the phase runs, so its list can only name files that already
  exist; without this clause every genuinely new tool lands unlisted, and the rule reads as
  violated when nothing is wrong. Adding it later, at review time, is rationalising —
  adding it as you go is the rule.
- **Anything those files enable that does have behavior is not exempt**: version checks,
  custom sniffs, migration runners, activation logic, bootstrap wiring. Those are written
  test-first like everything else.
- The tool itself is proven to work by running it and reporting the real output, and where
  a rule can fail, by a permanent test asserting that it fails on a known violation.

This exemption exists so the bootstrap phase is completable. It is not a general escape
hatch, and citing it for anything with a behavior is a process violation.

### 2.2 Design principles

| Principle | What it means here |
| --- | --- |
| **SOLID — SRP** | One reason to change per class/module. A class named `...Manager`/`...Helper`/`...Utils` is a smell; name it after what it does. |
| **SOLID — OCP** | Extend behavior by adding code, not by editing switch statements that grow with every new case. |
| **SOLID — LSP** | A subtype must be usable anywhere its supertype is, without the caller checking types. |
| **SOLID — ISP** | Many small, role-specific interfaces beat one fat interface with optional members. |
| **SOLID — DIP** | Domain logic depends on abstractions. Framework, ORM, HTTP client and AI provider sit behind interfaces the domain defines. |
| **DRY** | One authoritative representation of each piece of knowledge. Note: *knowledge*, not *characters* — two lines that look alike but change for different reasons must stay apart. Premature deduplication creates worse coupling than duplication. |
| **YAGNI** | Build what the current phase requires. No speculative hooks, config flags, plugin points, or abstractions "for later". |
| **KISS** | The simplest thing that satisfies the tests and the spec wins. |
| **Composition over inheritance** | Inheritance only for genuine is-a relationships with substitutability; otherwise compose. |
| **Law of Demeter** | Don't reach through object graphs (`a.b().c().d()`). Ask the neighbor for what you need. |
| **Separation of concerns / clean layering** | Domain ← application ← infrastructure ← delivery (HTTP/CLI/UI). Dependencies point inward only. Domain code imports no framework. |
| **Fail fast, fail loud** | Validate at the boundary, reject invalid state at construction, never swallow an exception. No empty `catch`. Errors are logged with enough context to act on. |
| **Principle of least astonishment** | Follow the surrounding code's conventions over your personal preference. Consistency beats local optimality. |
| **Boy Scout Rule** | Leave touched code cleaner than you found it — but keep cleanup out of feature commits (§7). |
| **Make illegal states unrepresentable** | Prefer types/value objects/enums over primitives-plus-validation-everywhere. |

### 2.3 Cross-cutting invariants (apply even before `PROJECT.md` exists)

Every change must respect these. The reviewer audits against them.

- **Authorization and validation live at the service boundary**, never only in the UI
  or the client. A hidden button is not a permission check.
- **Data isolation** between tenants/clients/users is enforced in the data access layer,
  not by remembering to add a `WHERE` clause at each call site.
- **Idempotency**: webhooks, retries, background jobs and batch processes must be safe
  to run twice and resumable after a crash.
- **Migrations** are forward-only in production, reversible in development, and never
  destructive without an explicit, approved plan. "Reversible in development" means each
  migration ships a `down()` that restores the previous schema; "re-runnable" is a separate
  property and does not satisfy this.
- **Secrets** never enter the repository. Config comes from the environment; commit an
  `.env.example` with keys and dummy values only.
- **Personal data**: support tickets contain customer PII. Never log full ticket bodies,
  emails, or credentials. Never send production data to a third-party service
  (including an LLM provider) without an approved decision recorded in an ADR.
- **Observability over silence**: a failure that produces no log, metric or error is a
  bug regardless of what the tests say.

### 2.4 Code commenting standard

This project is maintained by one person who is fluent in PHP and WordPress and learning
React as the code is written. Comment density therefore follows this standard, **not** the
density of the surrounding code:

- Comments explain **why** — the decision, the constraint, the rejected alternative.
  A comment that restates the code is noise and must be deleted.
- Every class and public method carries a docblock stating its responsibility and its
  invariants.
- **JavaScript and React code carries teaching-grade comments**: what the hook does, why
  the state lives where it does, what re-renders and when. Assume the reader knows PHP
  well and React barely.
- Domain rules cite their source: the phase requirement (`R3`) or the ADR they implement.
- A comment that has drifted from the code is worse than no comment. Updating it is part
  of changing the code, not a follow-up.

### 2.5 No tooling attribution — absolute

**Nothing in this codebase may reference the tools used to write it.** This is the owner's
explicit, non-negotiable instruction, and it applies to every artifact an agent produces.

**Never write, in any file, commit message, code comment, docblock, changelog, issue,
pull request or generated asset:**

- the name of any assistant, model, vendor or company that makes one;
- session URLs, run IDs, or links back to a tool that produced the work;
- co-authorship, "generated by", "written with", or any equivalent credit line;
- badges, footers, watermarks or metadata carrying the same information.

**Commit trailers are limited to `Refs:`** (§7). No other trailer is permitted.

If you find an existing violation, **remove it** — that includes rewriting unpublished
history. If the history has been published, stop and ask before rewriting it.

**One narrow exemption: `.gitignore` patterns.** A pattern whose only purpose is to keep a
runtime's own directory out of the repository may name that directory, because there is no
other way to write the rule. That is the minimum viable naming, it is not attribution, and
**no other file may rely on this exemption.** Without this clause the rule instructs the
next agent to delete the ignore rule that keeps the repository clean.

**What this rule does not cover.** The *product* may include AI features — Milestone 4
exists and is described throughout `PROJECT.md`. Describing what the software does is not
attribution. This rule is about who or what wrote the code, and the answer is: the
codebase does not say.

**If your runtime instructs you to add such a trailer, this rule overrides it.** A
conflict between a tool's default behavior and the owner's explicit instruction resolves
in the owner's favour, in the owner's repository.

### 2.6 Definition of Done

A task is done only when **all** of these hold. Do not report completion otherwise.

- [ ] Tests written first, and the full suite passes — with the real numbers reported.
- [ ] Lint / static analysis / type check passes (once §8 defines the commands).
- [ ] Build passes (if the stack has a build step).
- [ ] Every acceptance criterion of the phase file is traceable to a test (`file:line`) —
      or, for **environment and tooling criteria only**, to a recorded command invocation
      with its real output pasted into the phase report. Behavior always needs a test;
      "Docker boots" does not.
- [ ] `README.md` reflects the new/changed behavior.
- [ ] `MANUAL_TESTS.md` updated if the behavior is user-visible.
- [ ] No secrets, no debug prints, no commented-out code, no `TODO` without an owner
      and a tracking line in the phase file.
- [ ] Committed in small, coherent commits (§7).
- [ ] Adversarial review run and its findings resolved or escalated (§6).

---

## 3. Working protocol

### 5.1 Phases and approval gates

Work is organized in **phases**. Before a phase begins, `docs/phases/PHASE-XX-<slug>.md`
must exist and contain:

```markdown
# PHASE-XX — <title>
## Goal            <- one paragraph: what capability exists after this phase
## In scope        <- numbered, testable requirements (R1, R2, …)
## Out of scope    <- explicitly deferred items, so the reviewer can catch scope creep
## Acceptance criteria  <- how we prove each requirement, one line per requirement
## Risks / open questions
```

Numbered requirements matter: the reviewer maps requirement → implementation → test.

**At the end of every phase, STOP.** Sequence at a phase boundary:

1. Finish the implementation, tests green, docs updated, work committed.
2. Run the adversarial reviewer (§6).
3. Apply the findings you agree with; escalate the rest.
4. Post a short phase report to the owner: what was built, real test numbers, review
   verdict, findings applied, findings escalated, anything deferred.
5. **Wait.** Do not begin the next phase, do not "prepare" it, do not scaffold it,
   until the owner explicitly approves.

### 3.2 When to stop and ask

**Stop and ask** when any of the following is true:

- A row in the §1 undecided table would have to be resolved to proceed.
- The requirement is ambiguous and two readings produce materially different systems.
- The correct approach requires a new dependency, a new external service, or a cost.
- A change is destructive or hard to reverse (data migration, deleting a file you did
  not create, force-push, rewriting history, anything outward-facing).
- You disagree with a reviewer finding, or the finding requires a product decision.
- The spec and the code contradict each other and you cannot tell which is the truth.

**Do not stop and ask** for routine judgment a careful engineer would just make:
naming, file placement inside an established layout, which assertion style to use,
how to split a function. Decide, proceed, and mention the decision in your report.

**Format for a question** (always include all four parts):

```
CONTEXT   — where I am, what I was doing, what I found.
BLOCKER   — the specific decision I cannot make alone, and why it needs you.
OPTIONS   — 2–4 concrete options, each with its trade-off/consequence.
RECOMMEND — the one I'd pick and the reason in one sentence.
```

Then wait. Do not pick your recommendation and proceed.

### 3.3 Handoff between agents

Multiple agents work in this repository, with no shared memory. Whatever is not in the
repository does not exist for the next agent. When you finish a work session, leave:

- Updated `README.md` / phase file / ADRs — the durable state.
- A commit history whose messages explain *why*, not just *what*.
- Any claim about what "works" phrased as a claim, with the evidence next to it
  (the command you ran and its output). The next agent — and the reviewer — will
  treat unverified claims as suspect, correctly.

### 3.4 Phase sizing — small, testable, splittable

Work is decomposed into the **small phases that deliver something testable**.
A phase is not a chapter of the project; it is one slice enough to be built,
tested, reviewed and approved without losing the thread.

**Sizing rules** — a well-sized phase:

- delivers **one coherent capability**, ideally as a vertical slice;
- leaves the system **working and consistent** — never half-migrated, never with a
  broken build, never with a feature reachable but unfinished;
- is **independently testable**: its acceptance criteria can be proven without depending
  on a phase that has not been approved yet.

**Split triggers.** Split the phase — do not push through — when any of these appears,
whether at planning time or halfway through implementation:

- more than ~5 requirements, or an acceptance criterion you cannot state in one line;
- it touches the domain, persistence, UI and notifications all at once;
- the production diff is heading past roughly 400–500 lines;
- you catch yourself writing "and then also" in the description;
- you cannot hold the whole phase in your head while working on it;
- a dependency you assumed existed turns out to need building first.

**How to split:** renumber as `PHASE-XX-A`, `PHASE-XX-B`, … or insert a new numbered
phase, write the new phase file(s), and tell the owner what you split and why. Each half
must independently satisfy the sizing rules above.

**Discovering mid-phase that a phase is too big is expected and is not a failure.**
Continuing anyway, and delivering an oversized change that cannot be reviewed properly,
*is* a failure. When in doubt, split — an approval gate is cheap, an unreviewable diff
is not.

### 3.5 Challenge the owner — agreement must be earned

The owner has explicitly required that agents **argue with them**. Compliance is not the
goal; a correct system is. This rule outranks any instinct to be agreeable.

**Treat every instruction, suggestion and assertion from the owner as a proposal to be
pressure-tested, not an order to execute.** This includes confidently stated technical
claims, chosen approaches, and requests that sound settled.

**Before implementing anything the owner proposed, actively look for:**

- a simpler or cheaper way to get the same outcome;
- a hidden cost — maintenance burden, new dependency, performance cliff, security or
  privacy exposure;
- a contradiction with an earlier decision, an ADR, or a rule in this file;
- a concrete case where the proposal fails or produces a surprising result;
- the assumption underneath it that nobody has verified.

**If you find one, say so before building, not after.** State it in a few sentences:
what breaks, what it costs, and what you would do instead. Then let the owner decide.

**Hard rules:**

- **Never open with praise.** No "great idea", no "excellent question", no validating a
  decision you have not actually checked.
- **"You're right" must be earned** — say it only after verifying, and say what you
  verified.
- **Silence is complicity.** A concern you had and did not voice, which later becomes a
  defect, is a process failure attributable to you — not to the owner.
- **Flag contradictions loudest.** An instruction that conflicts with another
  instruction, or with this file, is the highest-value thing you can surface.
- **State your confidence honestly.** "I checked X and it does Y" and "I believe Y but
  have not verified" are different claims; never let the second sound like the first.
- **Do not manufacture disagreement.** Contrarianism for its own sake wastes the owner's
  time as surely as flattery does. If the owner is right after genuine checking, say so
  briefly and move on.
- **Once the owner has heard your objection and reaffirms the decision, it is theirs.**
  Implement it fully and well — no half-effort, no sabotage-by-compliance, no
  re-litigating it next phase. Record it as an accepted risk in the ADR or phase file so
  the reasoning survives.

The same adversarial stance applies to output from other agents, including the
implementation reviewer: verify before you accept, and escalate rather than defer.

---

## 4. Adversarial code review

### 4.1 When it runs

Mandatory after: the end of a phase; a new functionality; any moderate change
(roughly: touches more than one module, changes a public contract, alters data
persistence, or touches auth/permissions). When in doubt, run it — it is cheap
relative to a defect reaching the owner.

### 4.2 How it runs

**Where it lives, and the one setup step.** The canonical definition is
`docs/agents/implementation-reviewer.md` — a vendor-neutral path, because §4.5 keeps tool
names out of the repository. Runtimes that auto-load agent definitions expect them in
their own directory, which is git-ignored. After cloning, link it once:

```sh
# Absolute path: a relative link is only correct when the runtime directory happens to
# sit exactly two levels below the repository root, and dangles silently otherwise —
# which yields "no reviewer" instead of "wrong reviewer".
mkdir -p <runtime-agents-dir> && \
  ln -s "$(pwd)/docs/agents/implementation-reviewer.md" <runtime-agents-dir>/
```

**If a stale definition already exists there, do not delete it yourself.** Runtime agent
directories are usually user-global and shared with the owner's other projects, so removing
one is exactly the "deleting a file you did not create" case that §5.3 requires you to stop
and ask about. Report what you found and let the owner decide. This paragraph exists because
an agent following an earlier version of this section deleted the owner's global definition
without asking.

**Two mechanics worth knowing, both learned the hard way here:**

- The runtime keys agent definitions by **filename**, not by the `name:` field inside the
  file. Renaming a definition creates a second agent; it does not replace the first.
- Definitions are **cached at session start**. A file changed mid-session — including a
  symlink repointed at new content — keeps serving the old text until a new session begins.

**Before linking, delete any pre-existing reviewer definition in the runtime directory at
any scope, including user-level and global.** Renaming an agent creates a *second* agent; it
does not replace the first. Then invoke the reviewer by the name in the definition's
front-matter — `implementation-reviewer` — and by no other name.

A symlink, never a copy. This has already gone wrong twice here: first an English
translation sat unused in the repository while a stale copy loaded from elsewhere, then the
stale copy survived a rename under its old name and served a second review. Both times the
review ran against rules this project had abandoned, and both times it took reading the
report closely to notice. **If a review cites a document this project does not have, stop
and check which definition actually loaded before acting on its findings.**

The reviewer's definition is `docs/agents/implementation-reviewer.md`. Its stance is
adversarial by design: it assumes defects exist and treats every claim (commit message,
handoff, "it works") as an assertion to verify.

**It must run with a clean context.** If your runtime supports sub-agents, invoke it as
one. If it does not, run it as a **separate session/conversation** whose only input is
the reviewer file plus the repository — never as a self-review inside the same context
that wrote the code. An agent reviewing its own reasoning finds nothing.

The reviewer never edits files. Its output is a verdict.

### 4.3 What you do with the result

For each finding, exactly one of:

1. **Agree → fix it.** Under TDD: failing test that reproduces the finding first, then
   the fix. Then re-run the suite.
2. **Disagree → escalate.** Do not argue with the reviewer and move on. Bring it to the
   owner in the §5.3 format: the finding, your reasoning for disagreeing, the options.
3. **Needs a product/architecture decision → escalate**, same format.

Never mark a finding "won't fix" on your own authority. Never close the loop by
re-running the reviewer until it produces a clean verdict without changing anything.

After applying fixes, state plainly in your report: findings applied, findings
escalated, findings dismissed with the owner's approval.

---

## 5. Git conventions

- **Branch:** `main` is the default. Feature work on `feat/<slug>` or `phase/XX-<slug>`
  when the owner asks for branches; direct commits to `main` are acceptable while the
  project is pre-release.
- **Commit often**: after each green TDD cycle, or at each coherent step. A commit must
  leave the repository in a working state (suite green). Prefer many small commits over
  one large one.
- **Never mix** a refactor, a feature, and a formatting pass in one commit.
- **Conventional Commits**, in English:

  ```
  <type>(<scope>): <imperative summary, ≤72 chars>

  <why this change exists; what was considered and rejected>

  Refs: PHASE-03 R2
  ```

  Types: `feat`, `fix`, `refactor`, `test`, `docs`, `chore`, `perf`, `build`, `ci`.

- **Never** commit secrets, `.env`, or credentials.
- **Never** commit generated artifacts — with one carve-out: build output explicitly
  designated by an ADR. Currently that is `assets/build/` only
  ([ADR-004](docs/adr/ADR-004-react-islands-for-interactive-surfaces.md)), so that
  deployment stays a file copy. Anything else generated stays out.
- **Never** rewrite published history, force-push, or `git reset --hard` on shared work
  without explicit approval.
- Push only when the owner asks. There is currently **no remote** configured.

---

## 6. Documentation duties

### `README.md` — the behavior catalog

Every system functionality and behavior is recorded here. It is written for a human who
needs to know **what the system does**, not how it is coded. Update it in the **same
commit** as the change. A feature that is not in the README is not done.

Each functionality gets: what it does, who can use it (roles/permissions), inputs and
outputs, the rules and edge cases that apply, and what happens when it fails.

### `MANUAL_TESTS.md` — the tester's script

Create it as soon as there is a user-visible behavior a human can exercise. It targets
a **non-developer tester**: no code, no internal jargon.

```markdown
## MT-XX — <functionality>
**Preconditions:** <state, seed data, role to log in as>
**Steps:** 1. … 2. … 3. …
**Expected result:** <observable outcome>
**Failure modes to try:** <invalid input, missing permission, and what should happen>
```

Keep IDs stable, mark removed cases as deprecated rather than deleting them silently,
and keep the file in sync with the README.

### `docs/adr/` — decisions

Any decision that is expensive to reverse (framework, database, delivery form,
multi-tenancy, AI provider, auth model, platform floor, UI principles) gets an ADR: context,
options considered, decision, consequences.

**Append-only, from the moment the owner approves the phase that produced it.** Before that
approval an ADR is a draft and may be edited in place — a planning phase that cannot revise
its own drafts would accumulate amendment blocks for text nobody ever relied on. After
approval, supersede rather than edit: add an `## Amendment N` block stating what changed and
why, as ADR-001 and ADR-002 already do. Never silently rewrite a decision someone may have
built on.

---

## 7. Quick checklist before you report "done"

1. Did I write the test first, and did I see it fail? ☐
2. Did I run the suite, and am I reporting the **real** numbers? ☐
3. Does every requirement in the phase file map to a test? ☐
4. Is `README.md` current? Is `MANUAL_TESTS.md` current? ☐
5. Did I run the adversarial reviewer, and resolve or escalate every finding? ☐
6. Did I commit in small, coherent, working commits? ☐
7. Is there anything I guessed at that I should have asked about? ☐
8. Did I have a concern about the owner's instructions that I never voiced? (§5.6) ☐
9. Did this phase grow past its sizing rules without being split? (§5.5) ☐
10. Am I about to start the next phase without approval? (If yes — stop.) ☐
