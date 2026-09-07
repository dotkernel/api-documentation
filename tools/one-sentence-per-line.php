<?php

/**
 * Checks that Markdown prose keeps one sentence per line.
 *
 * markdownlint has no rule for this, so it cannot be caught by the CI
 * documentation linting. See CONTRIBUTING notes in README.md.
 *
 * Usage:
 *   php tools/one-sentence-per-line.php docs/book/v7/some-page.md [...]
 *   php tools/one-sentence-per-line.php --all      every page under docs/book
 *   php tools/one-sentence-per-line.php --staged   staged Markdown, as the hook runs it
 *
 * Exit code 0 when clean, 1 when a line holds more than one sentence.
 */

declare(strict_types=1);

/** Abbreviations whose trailing dot does not end a sentence. */
const ABBREVIATIONS = [
    'e.g.',
    'i.e.',
    'etc.',
    'vs.',
    'cf.',
    'approx.',
    'Mr.',
    'Mrs.',
    'Ms.',
    'Dr.',
    'St.',
    'Inc.',
    'Ltd.',
];

/**
 * @return list<string>
 */
function collectMarkdownFiles(string $directory): array
{
    $files    = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
    );

    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'md') {
            $files[] = $file->getPathname();
        }
    }

    sort($files);

    return $files;
}

/**
 * Markdown files staged for commit, added or modified.
 *
 * @return list<string>
 */
function collectStagedFiles(): array
{
    $command = 'git diff --cached --name-only --diff-filter=ACM -z -- "*.md"';
    $output  = shell_exec($command);

    if (! is_string($output) || $output === '') {
        return [];
    }

    return array_values(array_filter(explode("\0", $output), static fn (string $p): bool => $p !== ''));
}

/**
 * The staged content of a file, which is what the commit will contain and may
 * differ from the working tree when only some hunks were added.
 *
 * @return list<string>
 */
function readStagedLines(string $path): array
{
    $output = shell_exec('git show :' . escapeshellarg($path) . ' 2>/dev/null');

    if (! is_string($output)) {
        return [];
    }

    return explode("\n", rtrim($output, "\n"));
}

/**
 * True when the offset sits inside an inline code span, i.e. an odd number of
 * backticks precedes it on the line.
 */
function insideCodeSpan(string $line, int $offset): bool
{
    return substr_count(substr($line, 0, $offset), '`') % 2 === 1;
}

/**
 * True when the sentence-ending dot at $offset closes a known abbreviation.
 */
function endsAbbreviation(string $line, int $offset): bool
{
    $before = substr($line, 0, $offset + 1);

    foreach (ABBREVIATIONS as $abbreviation) {
        if (str_ends_with($before, $abbreviation)) {
            return true;
        }
    }

    return false;
}

/**
 * @param list<string> $lines
 * @return list<array{line: int, snippet: string}>
 */
function findViolations(array $lines): array
{
    $violations = [];
    $inFence    = false;

    foreach ($lines as $index => $line) {
        $trimmed = ltrim($line);

        // Fenced code blocks, opened and closed by ``` or ~~~.
        if (str_starts_with($trimmed, '```') || str_starts_with($trimmed, '~~~')) {
            $inFence = ! $inFence;
            continue;
        }

        if ($inFence) {
            continue;
        }

        // Table rows: cells are prose but wrapping them is not possible.
        if (str_starts_with($trimmed, '|')) {
            continue;
        }

        // Ordered list markers ("1. Do the thing") are not sentence ends.
        $line = (string) preg_replace('/^(\s*)\d+\.(\s)/', '$1  $2', $line);

        // A sentence end: . ! or ? after a word character, then space, then
        // the start of something that looks like a new sentence.
        $pattern = '/(?<=[a-z0-9\)\]"])[.!?]\s+(?=["A-Z])/';

        if (preg_match_all($pattern, $line, $matches, PREG_OFFSET_CAPTURE) === 0) {
            continue;
        }

        foreach ($matches[0] as [$match, $offset]) {
            if (insideCodeSpan($line, $offset) || endsAbbreviation($line, $offset)) {
                continue;
            }

            $start   = max(0, $offset - 40);
            $snippet = trim(substr($line, $start, 90));

            $violations[] = [
                'line'    => $index + 1,
                'snippet' => ($start > 0 ? '...' : '') . $snippet . '...',
            ];
        }
    }

    return $violations;
}

$arguments = array_slice($argv, 1);

if ($arguments === []) {
    fwrite(STDERR, "Usage: php tools/one-sentence-per-line.php <file.md> [...] | --all | --staged\n");
    exit(1);
}

$staged = $arguments === ['--staged'];

$paths = match (true) {
    $staged                 => collectStagedFiles(),
    $arguments === ['--all'] => collectMarkdownFiles(__DIR__ . '/../docs/book'),
    default                 => $arguments,
};

if ($paths === []) {
    exit(0);
}

$total = 0;

foreach ($paths as $path) {
    if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'md') {
        continue;
    }

    $lines = $staged ? readStagedLines($path) : (is_file($path) ? file($path, FILE_IGNORE_NEW_LINES) : []);

    if ($lines === []) {
        continue;
    }

    foreach (findViolations($lines) as $violation) {
        printf("%s:%d: more than one sentence on this line\n", $path, $violation['line']);
        printf("    %s\n", $violation['snippet']);
        $total++;
    }
}

if ($total > 0) {
    printf("\n%d line(s) hold more than one sentence.\n", $total);
    print("Dotkernel docs use one sentence per line, so a diff shows the edited sentence\n");
    print("rather than a reflowed paragraph. Put each sentence on its own line.\n");
    exit(1);
}

print("One sentence per line: OK\n");
exit(0);
