<?php

require_once __DIR__ . '/../../src/helpers.php';

use STDW\Event\EventManager;
use Tests\Stubs\SimpleListener;

it('events() returns an EventManager instance', function () {
    $em = events();
    expect($em)->toBeInstanceOf(EventManager::class);
});

it('events() returns the same instance on multiple calls', function () {
    $first = events();
    $second = events();
    expect($first)->toBe($second);
});

it('event() dispatches to listeners registered via events()', function () {
    $listener = new SimpleListener();
    events()->listen('helper.test', $listener);

    event('helper.test', ['key' => 'value']);

    expect($listener->calls)->toHaveCount(1)
        ->and($listener->calls[0])->toBe(['key' => 'value']);
});

it('event() dispatches to wildcard listeners', function () {
    $listener = new SimpleListener();
    events()->listen('helper.*', $listener);

    event('helper.created', ['id' => 1]);
    event('helper.updated', ['id' => 2]);

    expect($listener->calls)->toHaveCount(2);
});

it('events() and event() share the same instance', function () {
    $em = events();
    $listener = new SimpleListener();

    $em->listen('shared.test', $listener);
    event('shared.test', ['test' => true]);

    expect($listener->calls)->toHaveCount(1);
});

it('event() dispatches empty data by default', function () {
    $listener = new SimpleListener();
    events()->listen('empty.test', $listener);

    event('empty.test');

    expect($listener->calls)->toHaveCount(1)
        ->and($listener->calls[0])->toBe([]);
});
