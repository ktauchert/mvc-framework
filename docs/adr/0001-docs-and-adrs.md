# ADR-0001: Docs and ADRs instead of architecture in AGENTS.md

- Status: Accepted
- Date: 2026-10-10

## Context

`AGENTS.md` was used as a dump for the target picture, folder tree, Laravel comparison, and routing. That file is for agent behaviour. Architecture should be discussed and versioned, not grown silently inside prompt text.

## Alternatives

1. Keep everything in `AGENTS.md` — convenient short term, mixed roles, no decision history.
2. One large `architecture.md` without ADRs — readable, but “why not Inertia?” disappears.
3. `docs/` plus one ADR per decision.

## Decision

- `AGENTS.md`: coach rules only, pointer to `docs/`.
- `docs/adr/`: one decision per file.
- No second architecture wiki that duplicates ADRs.

## Consequences

New directions start as a Proposed ADR, then accept or reject. Chat is discussion; the decision lands in the ADR.
