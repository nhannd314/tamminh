<?php

use App\Support\PartialJson;

test('unfinished json is read up to what has arrived', function (string $json, mixed $expected) {
    expect(PartialJson::decode($json))->toBe($expected);
})->with([
    'complete' => ['{"a":[1,2],"b":"x"}', ['a' => [1, 2], 'b' => 'x']],
    'string being written' => ['{"summary":"Khuôn mặt trò', ['summary' => 'Khuôn mặt trò']],
    'nested list' => ['{"blocks":[{"title":"Trán","items":[{"label":"A","text":"Rộng', ['blocks' => [['title' => 'Trán', 'items' => [['label' => 'A', 'text' => 'Rộng']]]]]],
    'key being written' => ['{"tags":["Kim"],"rad', ['tags' => ['Kim']]],
    'key without value' => ['{"tags":["Kim"],"radar": ', ['tags' => ['Kim']]],
    'number may be incomplete' => ['{"score":8', []],
    'number followed by more' => ['{"score":82,"ver', ['score' => 82]],
    'cut inside an escape' => ['{"a":"x\\', ['a' => 'x']],
    'escaped characters' => ['{"a":"\"Kim\" đầy', ['a' => '"Kim" đầy']],
    'nothing yet' => ['', null],
]);
