<?php

use STDW\Event\EventException;
use STDW\Event\EventManager;
use Tests\Stubs\SimpleListener;
use Tests\Stubs\AnotherListener;
use Tests\Stubs\DispatchCounter;

beforeEach(function () {
    DispatchCounter::reset();
});

it('loads listeners from file', function () {
    $em = new EventManager();
    $em->load(__DIR__ . '/../Fixtures/events.php');

    $em->dispatch('post.created', ['id' => 1]);

    expect(DispatchCounter::$count)->toBe(1);
});

it('loads and dispatches wildcard listeners from file', function () {
    $em = new EventManager();
    $em->load(__DIR__ . '/../Fixtures/events.php');

    $em->dispatch('post.deleted', ['id' => 99]);

    expect(DispatchCounter::$count)->toBe(1);
});

it('deduplicates same listener from exact and wildcard in loaded file', function () {
    $em = new EventManager();
    $em->load(__DIR__ . '/../Fixtures/events.php');

    $em->dispatch('post.created', ['id' => 1]);

    expect(DispatchCounter::$count)->toBe(1);
});

it('throws exception on non-existent file', function () {
    $em = new EventManager();
    $em->load('/nonexistent/file.php');
})->throws(EventException::class, "Event file '/nonexistent/file.php' not found");

it('throws exception when file does not return array', function () {
    $em = new EventManager();
    $em->load(__DIR__ . '/../Fixtures/invalid_events_not_array.php');
})->throws(EventException::class, 'must return an array');

it('throws exception when listener does not implement interface', function () {
    $em = new EventManager();
    $em->load(__DIR__ . '/../Fixtures/invalid_events_bad_listener.php');
})->throws(EventException::class, 'must implement EventListenerInterface');
