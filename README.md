# Dotkernel API

Based on Enrico Zimuel’s Zend Expressive API – Skeleton example, Dotkernel API runs on Laminas and Mezzio components and implements standards like PSR-3, PSR-4, PSR-7, PSR-11 and PSR-15.

This repository holds the Markdown sources for the documentation published at [docs.dotkernel.org](https://docs.dotkernel.org/api-documentation/).

## Writing style

Documentation prose uses **one sentence per line**, relying on the renderer to wrap it.
A sentence stays on one line however long it gets, rather than being hard-wrapped to a column width.

The reason is the diff: a one-sentence-per-line source shows reviewers the sentence that changed, instead of a whole reflowed paragraph.

Code blocks and table rows are exempt, since neither can be broken across lines.

## Checking it

`markdownlint` has no rule for this, so the CI documentation linting cannot catch it.
This repository ships its own checker instead:

```shell
php tools/one-sentence-per-line.php docs/book/v7/some-page.md
php tools/one-sentence-per-line.php --all
```

To have it run automatically, enable the bundled hooks once per clone:

```shell
git config core.hooksPath .githooks
```

That directory holds two hooks:

- `pre-commit` runs the check above against the Markdown you staged, so a pre-existing violation elsewhere never blocks an unrelated commit.
- `prepare-commit-msg` appends the `Signed-off-by` trailer this repository's DCO check requires, using your configured git identity.

Setting `core.hooksPath` makes git ignore `.git/hooks`, so move any hook you keep there into `.githooks` as well.
Use `git commit --no-verify` to bypass the checks for a single commit.
