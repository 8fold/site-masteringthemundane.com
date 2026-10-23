# Architecture and information placement

## Information Hierarchy & Purpose

Choose the home by purpose, using this order as a default from more volatile to less volatile information:

1. **Readable operational code and executable configuration** that performs a system or operator goal: application, CLI, build, and deployment behavior. Names, boundaries, imports, and attributes should make local reasoning and navigation straightforward. This code defines what the system currently does; it does not by itself establish that the behavior is correct.
2. **Colocated operational explanation** for a decision, constraint, or step that the code cannot express: concise comments, examples, or fixtures near the code they explain. Keep examples and fixtures in nearby files when embedding them would make the code harder to understand.
3. **Readable executable verification**: tests and other checks that exercise behavior or enforce a technical contract. They are executable specifications and evidence about intended behavior, but they can be stale or wrong; a failure calls for checking the requirement and observed behavior, not automatically treating the assertion as authoritative.
4. **Colocated verification explanation and fixtures** for the rationale, boundary cases, or setup that verification code cannot express clearly. Keep fixtures near their owning checks, but use a separate fixture file when inlining it would obscure either the check or the data.
5. **Standalone prose** for information that cannot reasonably live in the earlier homes: shared philosophy and definitions, timeless guidance, and procedures spanning systems or audiences. Choose the relevant document and link to it where another audience needs to find it.

Use one maintained home for each fact or procedure: put it in the first suitable home and link to it rather than paraphrasing it elsewhere. A home is suitable when it can explain the information without obscuring its purpose: script-specific setup belongs with that script, while shared philosophy, definitions, and cross-system procedures may belong in prose. Different audiences and discoverability justify links, not duplicate copies.

Update information when its meaning or the contract changes, not to mirror incidental implementation detail. Cross-layer edits during a pure refactoring are a signal to investigate coupling or duplication; a behavior change can legitimately require changes to verification and operational procedures. Volatility is a heuristic, not an absolute ranking. These placements are defaults: explain why an earlier home is unsuitable when making an exception, and propose a better placement rather than silently moving established information.

Manual test protocols remain a specific exception: human-executed procedures for behavior automation cannot yet reach (for example hosted third-party UI or real webhook delivery) live in `/tests-manual/`. They are committed, reviewed, kept current, and removed once automated tests in `/tests/` can reach the behavior.
