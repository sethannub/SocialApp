import type { PlatformName } from '@/types/compose';

/**
 * Pure, DOM-free mirror of the server-side `PostSplitter` packing algorithm.
 * The composer's inline thread markers and the server publish path MUST agree on
 * where one section ends and the next begins; keeping the logic here (free of
 * tiptap/ProseMirror imports) lets a parity test exercise it against the PHP
 * implementation. See `tests/Fixtures/post-splitter-parity.json`.
 */

/**
 * Client-side mirror of the active platform's length measure. X counts UTF-16
 * code units (JS string length); the others approximate with code points. The
 * server's grapheme/byte count remains authoritative.
 */
export function measure(text: string, platform: PlatformName): number {
    // oxlint-disable-next-line no-misused-spread -- intentional code-point count
    return platform === 'x' ? text.length : [...text].length;
}

export interface Section {
    /** Indices (into the input paragraphs) of the paragraphs in this section. */
    paraIndices: number[];
    /** The section's text — its paragraphs joined by a single newline. */
    text: string;
}

/**
 * Paragraph-aware greedy split. Each paragraph either joins the current section
 * (when `current + "\n" + para` fits in `limit`) or starts a fresh one. A
 * paragraph that alone exceeds `limit` becomes a single overflowing section — we
 * never split mid-paragraph, so markers always sit on paragraph boundaries.
 *
 * Paragraphs are joined with a single "\n" to match the canonical base text
 * (blocks serialize newline-separated) and the server-side `PostSplitter`, so
 * the preview's section boundaries are exactly what gets published.
 */
export function packSections(
    paragraphs: string[],
    platform: PlatformName,
    limit: number,
): Section[] {
    const sections: Section[] = [];

    let cur = '';
    let curParas: number[] = [];

    for (let i = 0; i < paragraphs.length; i++) {
        const para = paragraphs[i] ?? '';
        const joined = cur === '' ? para : `${cur}\n${para}`;

        if (cur === '' || measure(joined, platform) <= limit) {
            cur = joined;
            curParas.push(i);

            continue;
        }

        sections.push({ paraIndices: curParas, text: cur });
        cur = para;
        curParas = [i];
    }

    sections.push({ paraIndices: curParas, text: cur });

    return sections;
}

/**
 * SocialApp change: exact port of the server's `PostSplitter::chunk()` (with
 * `splitParagraph()` and `hardSplit()`), so the preview shows the sections that
 * will actually be published. Unlike `packSections`, a paragraph that alone
 * exceeds `limit` is broken on word boundaries, and a single word longer than
 * `limit` by characters — exactly as the server does. Held to the server's own
 * output by `tests/Fixtures/post-splitter-overlimit-parity.json`.
 */
export function chunkSegment(
    segment: string,
    platform: PlatformName,
    limit: number,
): string[] {
    // limit <= 0 means "unknown" on the client; the server always has a real limit.
    if (limit <= 0 || measure(segment, platform) <= limit) {
        return [segment];
    }

    const sections: string[] = [];
    let current = '';
    let hasCurrent = false;

    for (const paragraph of segment.split('\n')) {
        // A paragraph that cannot stand on its own is broken further. Flush
        // whatever is buffered first so section order is preserved.
        if (measure(paragraph, platform) > limit) {
            if (hasCurrent) {
                sections.push(current);
                current = '';
                hasCurrent = false;
            }
            sections.push(...splitParagraph(paragraph, platform, limit));

            continue;
        }

        const candidate = hasCurrent ? `${current}\n${paragraph}` : paragraph;
        if (!hasCurrent || measure(candidate, platform) <= limit) {
            current = candidate;
            hasCurrent = true;

            continue;
        }

        sections.push(current);
        current = paragraph;
        hasCurrent = true;
    }

    if (hasCurrent) {
        sections.push(current);
    }

    return sections.length === 0 ? [''] : sections;
}

/** Port of `PostSplitter::splitParagraph()`: greedy words, then hard-split. */
function splitParagraph(
    paragraph: string,
    platform: PlatformName,
    limit: number,
): string[] {
    const trimmed = paragraph.trim();
    // PHP preg_split('/\s+/', '') returns [''], so an empty paragraph yields one empty word.
    // PCRE \s (no /u flag) is ASCII whitespace only - JS \s would also match NBSP etc.
    const words = trimmed === '' ? [''] : trimmed.split(/[ \t\n\v\f\r]+/);
    const chunks: string[] = [];
    let current = '';

    for (const word of words) {
        const candidate = current === '' ? word : `${current} ${word}`;
        if (measure(candidate, platform) <= limit) {
            current = candidate;

            continue;
        }
        if (current !== '') {
            chunks.push(current);
            current = '';
        }
        if (measure(word, platform) > limit) {
            chunks.push(...hardSplit(word, platform, limit));
        } else {
            current = word;
        }
    }

    if (current !== '') {
        chunks.push(current);
    }

    return chunks.length === 0 ? [''] : chunks;
}

/** Port of `PostSplitter::hardSplit()`: by code point, like PHP `mb_str_split`. */
function hardSplit(
    word: string,
    platform: PlatformName,
    limit: number,
): string[] {
    const pieces: string[] = [];
    let buffer = '';

    // oxlint-disable-next-line no-misused-spread -- intentional code-point split
    for (const char of [...word]) {
        if (measure(buffer + char, platform) > limit) {
            pieces.push(buffer);
            buffer = char;

            continue;
        }
        buffer += char;
    }

    if (buffer !== '') {
        pieces.push(buffer);
    }

    return pieces;
}

/**
 * Split the structured author segments into the section strings the composer
 * previews — the TS analogue of the server `PostSplitter::split(...).sections`.
 * Each segment is trimmed; empties are dropped (falling back to one empty
 * section), then chunked per platform limit.
 *
 * SocialApp change: uses `chunkSegment` (server-exact) instead of `packSections`,
 * so over-limit paragraphs are split the way they will be published.
 */
export function previewSections(
    segments: string[],
    platform: PlatformName,
    limit: number,
): string[] {
    const clean = segments
        .map((segment) => segment.trim())
        .filter((segment) => segment !== '');
    const use = clean.length > 0 ? clean : [''];

    return use.flatMap((segment) =>
        segment === '' ? [''] : chunkSegment(segment, platform, limit),
    );
}
