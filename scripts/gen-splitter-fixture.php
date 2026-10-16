<?php

/**
 * Generates app/tests/Fixtures/post-splitter-overlimit-parity.json by running the REAL server
 * splitter (App\Services\Posts\PostSplitter) on inputs that contain over-limit paragraphs.
 * The expected sections in the fixture are therefore the server's own answers; the TS preview
 * (section-split.ts) and a Pest test are both checked against them.
 *
 * Run inside the shoutrrr:dev image (see scripts/gen-splitter-fixture.ps1).
 */

require '/var/www/html/vendor/autoload.php';

use App\Enums\Platform;
use App\Services\Posts\PostSplitter;

$words = static fn (int $n, string $w = 'word'): string => trim(str_repeat("{$w} ", $n));

$cases = [
    ['name' => 'a 318-char single paragraph threads into two X posts', 'platform' => 'x', 'limit' => 280,
        'segments' => ['LOCAL TEST, NOT FOR PUBLISHING. Pay only for leads you can call. Y5Media 360 finds buyers who are actively looking for what you sell, verifies every contact, and delivers them to your inbox. AED 250 per verified lead. No retainers, no ad budget, no guesswork. Book a 15-minute call and we will show you a sample batch.']],
    ['name' => 'a 399-char run of words splits on word boundaries', 'platform' => 'x', 'limit' => 280,
        'segments' => [$words(80)]],
    ['name' => 'a short paragraph then an over-limit one', 'platform' => 'x', 'limit' => 280,
        'segments' => ["Short intro.\n".$words(60)]],
    ['name' => 'an over-limit paragraph then a short one', 'platform' => 'x', 'limit' => 280,
        'segments' => [$words(60)."\nTail line."]],
    ['name' => 'two over-limit paragraphs', 'platform' => 'x', 'limit' => 280,
        'segments' => [$words(60)."\n".$words(60, 'more')]],
    ['name' => 'a single 300-char word is hard-split by characters', 'platform' => 'x', 'limit' => 280,
        'segments' => [str_repeat('a', 300)]],
    ['name' => 'emoji count as two UTF-16 units on X', 'platform' => 'x', 'limit' => 280,
        'segments' => [str_repeat('😀', 150)]],
    ['name' => 'exactly at the limit stays whole', 'platform' => 'x', 'limit' => 280,
        'segments' => [str_repeat('b', 280)]],
    ['name' => 'one over the limit splits', 'platform' => 'x', 'limit' => 280,
        'segments' => [$words(56, 'abcd').' z']],
    ['name' => 'bluesky 300 splits a long paragraph', 'platform' => 'bluesky', 'limit' => 300,
        'segments' => [$words(70)]],
    ['name' => 'separate segments split independently', 'platform' => 'x', 'limit' => 280,
        'segments' => [$words(60), 'Second segment.']],
];

$splitter = new PostSplitter;
$out = [];
foreach ($cases as $case) {
    $result = $splitter->split($case['segments'], Platform::from($case['platform']), true, $case['limit']);
    $out[] = $case + ['expected' => $result->sections];
}

file_put_contents('/out/post-splitter-overlimit-parity.json', json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
foreach ($out as $c) {
    echo $c['name'], ' -> ', count($c['expected']), ' sections (', implode(' + ', array_map(static fn ($s) => Platform::from($c['platform'])->measure($s), $c['expected'])), ")\n";
}
