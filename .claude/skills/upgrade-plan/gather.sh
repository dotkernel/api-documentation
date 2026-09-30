#!/usr/bin/env bash
# Collect the raw facts for an upgrade plan between two releases of a Dotkernel repository.
# Usage: gather.sh <from-tag> <to-tag> [owner/repo]   (default repo: dotkernel/api)
# Output: Markdown on stdout. Needs gh (authenticated) and jq.
set -euo pipefail

FROM="${1:?usage: gather.sh <from-tag> <to-tag> [owner/repo]}"
TO="${2:?usage: gather.sh <from-tag> <to-tag> [owner/repo]}"
REPO="${3:-dotkernel/api}"

for tool in gh jq; do
    command -v "$tool" >/dev/null || { echo "missing required tool: $tool" >&2; exit 1; }
done

echo "# Raw data: $REPO $FROM -> $TO"
echo

echo "## Release $TO"
gh release view "$TO" -R "$REPO" --json tagName,publishedAt,targetCommitish,body \
    -q '"- published: \(.publishedAt)\n- target branch: \(.targetCommitish)\n\n\(.body)"'
BRANCH="$(gh release view "$TO" -R "$REPO" --json targetCommitish -q .targetCommitish)"
echo

echo "## Compare $FROM...$TO"
gh api "repos/$REPO/compare/$FROM...$TO" --jq '"- commits: \(.ahead_by)\n- changed files: \(.files | length)"'
echo

echo "## composer.json diff"
echo '```diff'
gh api "repos/$REPO/compare/$FROM...$TO" --jq '.files[] | select(.filename == "composer.json") | .patch' || true
echo '```'
echo

echo "## Files per pull request in the $TO release notes"
gh release view "$TO" -R "$REPO" --json body -q .body | grep -o 'pull/[0-9]*' | sort -t/ -k2 -n -u | cut -d/ -f2 |
while read -r pr; do
    gh pr view "$pr" -R "$REPO" --json number,title,mergedAt,files \
        -q '"### #\(.number) \(.title)\n- merged: \(.mergedAt)\n- files: \([.files[].path] | join(", "))\n"'
done

echo "## Commits on branch $BRANCH after tag $TO (unreleased)"
gh api "repos/$REPO/compare/$TO...$BRANCH" \
    --jq '"- ahead: \(.ahead_by)", (.commits[] | "- \(.sha[0:7]) \(.commit.message | split("\n")[0])")' 2>/dev/null ||
    echo "- (branch $BRANCH not comparable)"
