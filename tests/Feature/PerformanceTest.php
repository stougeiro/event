<?php

use STDW\Event\EventManager;
use Tests\Stubs\SimpleListener;
use Tests\Stubs\AnotherListener;

it('performs well with many listeners and wildcard patterns', function () {
    $em = new EventManager();

    for ($i = 0; $i < 100; $i++) {
        $em->listen("event.{$i}", new SimpleListener());
    }
    $em->listen('event.*', new AnotherListener());

    $start = microtime(true);
    for ($i = 0; $i < 1000; $i++) {
        $em->dispatch("event." . ($i % 100), ['i' => $i]);
    }
    $elapsed = microtime(true) - $start;

    expect($elapsed)->toBeLessThan(1.0);
});

it('deduplicates correctly under heavy load', function () {
    $em = new EventManager();
    $listener = new SimpleListener();

    for ($i = 0; $i < 50; $i++) {
        $em->listen("item.{$i}", $listener);
    }
    $em->listen('item.*', $listener);

    $em->dispatch('item.0', ['test' => true]);
    expect($listener->calls)->toHaveCount(1);

    for ($i = 0; $i < 50; $i++) {
        $em->dispatch("item.{$i}", ['i' => $i]);
    }

    expect($listener->calls)->toHaveCount(51);
});

it('uses reasonable memory with many listeners', function () {
    $em = new EventManager();

    for ($i = 0; $i < 500; $i++) {
        $em->listen("app.{$i}", new SimpleListener());
    }

    $memory = memory_get_usage();

    for ($i = 0; $i < 500; $i++) {
        $em->dispatch("app.{$i}", ['i' => $i]);
    }

    $peak = memory_get_peak_usage() - $memory;

    expect($peak)->toBeLessThan(5 * 1024 * 1024);
});
