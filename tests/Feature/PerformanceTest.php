<?php

use STDW\Event\EventManager;
use Tests\Stubs\SimpleListener;
use Tests\Stubs\AnotherListener;

it('benchmarks pure dispatch throughput', function () {
    $em = new EventManager();
    for ($i = 0; $i < 100; $i++) {
        $em->listen("event.{$i}", new SimpleListener());
    }

    for ($i = 0; $i < 1000; $i++) {
        $em->dispatch("event." . ($i % 100), ['i' => $i]);
    }

    $iterations = 10000;
    $start = hrtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        $em->dispatch("event." . ($i % 100), ['i' => $i]);
    }
    $elapsed = (hrtime(true) - $start) / 1e6;

    $opsPerSecond = ($iterations / $elapsed) * 1000;

    expect($opsPerSecond)->toBeGreaterThan(1000);
});

it('benchmarks listener registration throughput', function () {
    $em = new EventManager();
    $iterations = 10000;

    $start = hrtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        $em->listen("event.{$i}", new SimpleListener());
    }
    $elapsed = (hrtime(true) - $start) / 1e6;

    $opsPerSecond = ($iterations / $elapsed) * 1000;

    expect($opsPerSecond)->toBeGreaterThan(1000);
});

it('scales linearly with listener count', function () {
    $results = [];
    $listenerCounts = [10, 100, 1000];

    foreach ($listenerCounts as $count) {
        $em = new EventManager();
        for ($i = 0; $i < $count; $i++) {
            $em->listen("event.{$i}", new SimpleListener());
        }

        $iterations = 1000;
        $start = hrtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $em->dispatch("event." . ($i % $count), ['i' => $i]);
        }
        $elapsed = (hrtime(true) - $start) / 1e6;
        $results[$count] = $elapsed;
    }

    $ratio100_10 = $results[100] / $results[10];
    $ratio1000_100 = $results[1000] / $results[100];

    expect($ratio100_10)->toBeLessThan(20)
        ->and($ratio1000_100)->toBeLessThan(20);
});

it('handles many overlapping wildcards efficiently', function () {
    $em = new EventManager();

    for ($i = 0; $i < 50; $i++) {
        $em->listen("module.{$i}.*", new SimpleListener());
    }
    $em->listen('test.wildcard.*', new AnotherListener());
    $em->listen('test.*', new SimpleListener());
    $em->listen('*', new AnotherListener());

    for ($i = 0; $i < 100; $i++) {
        $em->dispatch("test.wildcard.{$i}", ['i' => $i]);
    }

    $iterations = 1000;
    $start = hrtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        $em->dispatch("test.wildcard." . ($i % 100), ['i' => $i]);
    }
    $elapsed = (hrtime(true) - $start) / 1e6;

    $opsPerSecond = ($iterations / $elapsed) * 1000;

    expect($opsPerSecond)->toBeGreaterThan(500);
});

it('handles long hierarchical event names', function () {
    $em = new EventManager();
    $longName = 'app.module.submodule.service.entity.action';
    $listener = new SimpleListener();

    $em->listen($longName, $listener);

    $iterations = 10000;
    $start = hrtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        $em->dispatch($longName, ['i' => $i]);
    }
    $elapsed = (hrtime(true) - $start) / 1e6;

    $opsPerSecond = ($iterations / $elapsed) * 1000;

    expect($opsPerSecond)->toBeGreaterThan(1000);
});

it('uses reasonable memory with many listeners', function () {
    $em = new EventManager();

    for ($i = 0; $i < 500; $i++) {
        $em->listen("app.{$i}", new SimpleListener());
    }

    $memoryBefore = memory_get_usage();

    for ($i = 0; $i < 500; $i++) {
        $em->dispatch("app.{$i}", ['i' => $i]);
    }

    $memoryAfter = memory_get_usage();
    $peak = memory_get_peak_usage();

    expect($peak - $memoryBefore)->toBeLessThan(5 * 1024 * 1024);
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
