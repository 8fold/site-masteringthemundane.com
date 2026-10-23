# Contributing

Contributions are **welcome** and will be **credited**. We accept contributions via pull requests to the GitHub project repository.

## Issue triage (maintainer policy)

Use these issue labels as qualitative impact categories, ordered from highest to
lowest priority. This is a maintainer triage aid, not a numeric score or an
automatic queue ordering. Apply every label supported by the issue's scope;
categories may overlap.

Security and accessibility are always the highest priority within the
applicable scope of the project. Therefore, some packages are low-level and
intended for developers and give the flexibility to them to surround the
package within a security layer.

If a label in this rubric is missing from the repository, open an issue
requesting it rather than substituting an unrelated label.

1. **Security/Accessibility** (label `security/accessibility`)
2. **Broken** — doesn't work as promised (label `broken`)
3. **Annoying** — doesn't work as promised but has a workaround, or could be
   improved (label `annoying`)
4. **Refactor/Re-engineer** (label `refactor/re-engineer`; see
   [Change classification](#change-classification))
5. **New by policy** — a capability that someone with policy authority over the
   project (a maintainer) has proposed or adopted on record in the issue, such
   as an internal governance or product decision (e.g. "only 200 users at the
   $2 tier"), or one required by an external policy or legal requirement,
   whoever raises it (label `new-by-policy`)
6. **New user request** — a capability requested by someone without policy
   authority and not yet adopted by a maintainer (label `new-user-request`)

The `new-user-request` and `new-by-policy` labels form an escalation path: a
maintainer who champions a user request records that adoption in the issue and
adds `new-by-policy`, keeping `new-user-request` as the record of outside
demand. Authorship alone does not escalate; a maintainer's idea is
`new-by-policy` only once it is proposed or adopted as policy on record.

An issue may have more than one label when each label fits its demonstrated
scope and impact. For example, a policy-required capability with security
implications may be labeled `new-by-policy` and `security/accessibility`.

Consider the combined impact of applicable labels, not only the highest-ranked
category. Do not add unsupported labels to inflate priority. The ordering is
qualitative: no numeric weights, formula, or deterministic sort order is
defined.

The `wontfix` and `tracking` labels describe issue disposition, not impact
categories, so they do not change the priority ordering above. `wontfix` means
there are no current plans to address the issue. `tracking` means the issue
records work whose implementation or next steps are not yet settled; update or
close it when a clear path forward is recorded.

Use `documentation` for issues focused on writing, reviewing, organizing, or
publishing documentation, including contributor policy and recorded design
rationale. It is a work-focus label, not an impact category or a priority
modifier; combine it with applicable impact and disposition labels. It supports
documentation-focused sessions without changing the triage ordering. A code or
operational issue does not need this label merely because its acceptance
criteria include updating documentation.

This label rubric is the canonical triage policy for this repository. Other
repositories may link here when adopting it or maintain their own policy when
their needs differ.

## Change classification

A *refactoring* changes the internal structure of software to make it easier to
understand and cheaper to modify without changing its observable behavior; the
test suite passes before and after, unmodified. A *re-engineering* changes the
internal structure while also changing observable behavior or explicit
developer-facing contracts (public APIs, URLs, configuration).

## Engineering philosophy

There are a lot of philosphical things tied in to the way we strive to do software development. Some you are most likely familiar with, others maybe not so much.

**You ain't gonna need it (YAGNI):** If someone hasn't asked for it, someone's not paying for it, and it's not needed to finish what has been asked and paid for, then don't develop that thing. (There are some extreme corner cases here, but they are extreme and rare.) (Drawback: Exploration and innovation often require doing things people haven't asked for and aren't paying for.)

**Don't repeat yourself (DRY):** If you are about to copy and paste that bit of code, reconsider. If you have a method that does exactly what you need it to, creating two means there's a possibility one will get out of sync or miss out on that update you make in a few months. (Drawback: DRY can sometimes lead to abstractions that would be easier to maintain if they remained more concrete.)

**Keep it simple, stupid:** (I could really live without the "stupid" piece.) Write the simplest code that could work. (This one actually comes with a lot of caveats, but it does hold true quite often.)

**Make it SOLID:** This is actually a set of principles in and of itself:

- *Single responsibility principle:* Each thing you write should have one responsibility. Specifically classes.
- *Open/closed principle:* Objects should be open for extension, but closed to modification.
- *Liskov substitution principle:* Object should be replaceable by subtypes without affecting the correctness of the program.
- *Interface segregation principle:* Prefer client-specific interfaces over generic catch-all interfaces.
- *Dependency inversion principle:* Admittedly, don't really know this one well enough to say we follow it or not. (Most likely not as well as we could.)

**Solutions over scapegoats:** There are times when these principles will not be followed or someone will make a mistake. Berating the offender doesn't get any of us any closer to improving the execution of these principles.

**The Community Principle:** If you create or acquire it you help maintain it; time, food, money, and so on.

## Code promotion

Capabilities should be implemented at a low, concrete level before being promoted to higher-levels and made more abstract.

## Code access

If the language being used allows access levels on variables, constants, methods, and so on, it is preferred that the access level begins at the most strict and becomes less restrictive out of necessity. (A desire to create a test for the thing, does not constitute a necessity.)

This reduces the size of the public API, which makes the class easier to consume by developers.

Further, it is preferred that class properties remain private, always. This means pre- and post-processing for setting and getting properties can be added without being a breaking change to developers.

## Commit workflow

A commit is limited to a single refactoring or a single re-engineering — never both — though it may touch multiple files to complete the change. A pull request may contain multiple commits. Commit messages favor the Conventional Commits specification. The refactoring and re-engineering definitions above are canonical.

## AI-assisted contributions

AI-assisted contributions follow the repository-wide requirements in [AGENTS.md](../AGENTS.md), including startup, validation, security, and pull-request handling.
