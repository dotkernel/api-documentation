---
name: upgrade-plan
description: >
  Use when asked to plan, prepare or draft a new upgrade page for the Dotkernel documentation — for
  example "plan the 7.1 to 7.2 upgrade page", "what changed between releases", or "list the pull
  requests for the next upgrade guide". Produces a plan file in .claude/ that lists the pull
  requests of a release, split into important and optional updates, based on the release notes,
  commits and code of the source repository. Planning only: it does not create the page.
---

# Upgrade page plan

Produce `.claude/PLAN-UPGRADE-<to>.md` for a new `docs/book/v<major>/upgrading/UPGRADE-<to>.md` page.
This skill only plans.
Do not create the page, edit `mkdocs.yml` or edit any other file until the user approves the plan and asks for it.

## Inputs

- `from` and `to`: the release tags, for example `7.0.0` and `7.1.0`. Ask if either is missing.
- `repo`: defaults to `dotkernel/api`. Other Dotkernel repositories work the same way.
- Check the tag exists with `gh release list -R <repo>` before going further.

## Steps

1. **Gather.** Run `bash .claude/skills/upgrade-plan/gather.sh <from> <to> [repo] > <scratchpad>/upgrade-<to>.md` and read the output.
   It prints the release notes, commit and file counts, the `composer.json` diff, the files touched by each pull request, and the commits on the release branch after the tag.
2. **Read the code.** Never classify from titles.
   For every pull request that touches `src/`, `config/`, `bin/`, `composer.json`, migrations or entities, read its diff with `gh pr diff <n> -R <repo>`.
   With many pull requests, hand this step to a subagent and keep only its conclusions.
3. **Classify** each pull request as important or optional using the rules below.
4. **Cross-check** and record the findings in the plan:
    - pull requests that must be read together, for example one removes a file and a later one recreates it elsewhere
    - titles that disagree with the code, such as a class named differently in the title and the diff
    - release-note oddities: a changelog entry missing most pull requests, or dated differently from the release
    - which branch the release comes from, since a minor release may come from the previous branch
5. **Scope.** Read the last section of the gather output.
   If it says the release was superseded, state that later commits belong to the named newer release and point to its plan; list no unreleased commits.
   Otherwise list the commits on the release branch after the tag as out of scope and unreleased.
6. **Write the plan** to `.claude/PLAN-UPGRADE-<to>.md`, using the layout below.
7. **Lint and report.**
   Run `npx --yes markdownlint-cli2 --config ~/.claude/markdownlint.jsonc ".claude/PLAN-UPGRADE-<to>.md"` and fix every issue.
   Tell the user the counts, the headline items, and anything from step 4 that needs a decision.

## Classification rules

Important: the change affects a project that has copied the skeleton.

- PHP version constraint, or any change to `require` or `conflict` in `composer.json` that reaches runtime
- major version bumps of runtime dependencies
- configuration files added, removed, renamed or with changed keys
- database schema: entity columns, enums, DBAL types, migrations
- entity, repository, service or interface signature changes
- new or changed middleware, pipeline or routes, and any change of response, header or error behavior
- security fixes and security headers
- changes to Composer scripts or the post-install script

Optional: skipping it does not change how the application runs.

- CI workflows and GitHub Action bumps, Renovate, Qodana, code coverage
- README, changelog and other documentation, API collections
- dev-only dependencies with no effect on project code, and comment or type-only cleanups
- config `.dist` clarifications

When unsure, classify as important and say why in the description.
State the condition where a change only matters sometimes, for example "only when using PostgreSQL".

## Plan layout

Follow `.claude/PLAN-UPGRADE-7.1.md` if it exists, otherwise this order:

1. Goal, including the target file path and the page used as the template.
2. Sources checked: release dates, target branch, commit and file counts.
3. Findings that shape the page: the headline changes and the step 4 cross-checks.
4. Pull requests: an important table and an optional table with `PR | Description`, each PR as a full URL, most impactful first.
   Escape `|` inside table cells as `\|`.
5. Out of scope: unreleased commits, or a note that the release was superseded and by which one.
6. Page outline: the newest existing `UPGRADE-*.md` is the template, with the same headings, one sentence per line and the same bullet marker.
   Details has `### Important updates` and `### Optional updates`, and the FAQ covers PHP versions, migrations, moved config, and whether optional updates are required.
7. Files to change on execution: the new page, the nav entry in `mkdocs.yml` (newest first), and `upgrading.md` only if it links the other version pages.
8. Verification: markdownlint, a check that every PR URL from the release notes appears exactly once, `mkdocs build --strict` if available, and a check of versions against `composer.json` at the tag.

## Rules

- Every claim in the plan and in later FAQ answers must come from a diff or the release notes, not from memory.
- Link pull requests by full URL, as the existing upgrade pages do.
- Major releases (for example 6.x to 7.0) need migration guidance in the FAQ and not just a pull request list; say so in the plan.
- Never publish or share the plan outside the repository.
