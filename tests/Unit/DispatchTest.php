<?php

use STDW\Event\EventException;
use STDW\Event\EventManager;
use Tests\Stubs\SimpleListener;
use Tests\Stubs\AnotherListener;

it('throws exception on wildcard dispatch', function () {
    $em = new EventManager();
    $em->dispatch('post.*', []);
})->throws(EventException::class, 'Wildcard events cannot be dispatched');

it('dispatches to exact match listeners', function () {
    $em = new EventManager();
    $listener = new SimpleListener();

    $em->listen('post.created', $listener);
    $em->dispatch('post.created', ['title' => 'Hello']);

    expect($listener->calls)->toHaveCount(1)
        ->and($listener->calls[0])->toBe(['title' => 'Hello']);
});

it('dispatches to wildcard listeners', function () {
    $em = new EventManager();
    $listener = new SimpleListener();

    $em->listen('post.*', $listener);
    $em->dispatch('post.created', ['id' => 1]);
    $em->dispatch('post.updated', ['id' => 2]);

    expect($listener->calls)->toHaveCount(2);
});

it('does not dispatch to non-matching patterns', function () {
    $em = new EventManager();
    $listener = new SimpleListener();

    $em->listen('user.*', $listener);
    $em->dispatch('post.created', ['id' => 1]);

    expect($listener->calls)->toHaveCount(0);
});

it('deduplicates listener from exact and wildcard', function () {
    $em = new EventManager();
    $listener = new SimpleListener();

    $em->listen('post.created', $listener);
    $em->listen('post.*', $listener);

    $em->dispatch('post.created', ['id' => 1]);

    expect($listener->calls)->toHaveCount(1);
});

it('dispatches to both exact and wildcard different listeners', function () {
    $em = new EventManager();
    $simple = new SimpleListener();
    $another = new AnotherListener();

    $em->listen('post.created', $simple);
    $em->listen('post.*', $another);

    $em->dispatch('post.created', ['id' => 1]);

    expect($simple->calls)->toHaveCount(1)
        ->and($another->calls)->toHaveCount(1);
});

it('passes data correctly to listeners', function () {
    $em = new EventManager();
    $listener = new SimpleListener();

    $em->listen('order.placed', $listener);

    $data = ['order_id' => 42, 'total' => 99.90, 'items' => ['A', 'B']];
    $em->dispatch('order.placed', $data);

    expect($listener->calls[0])->toBe($data);
});

it('does not dispatch when no listeners registered', function () {
    $em = new EventManager();

    $em->dispatch('nonexistent.event', ['data' => 1]);

    expect(true)->toBeTrue();
});

it('dispatches to global wildcard listener', function () {
    $em = new EventManager();
    $listener = new SimpleListener();

    $em->listen('*', $listener);

    $em->dispatch('post.created', ['id' => 1]);
    $em->dispatch('user.deleted', ['id' => 2]);
    $em->dispatch('order.placed', ['id' => 3]);

    expect($listener->calls)->toHaveCount(3);
});

it('deduplicates listener from global wildcard and specific event', function () {
    $em = new EventManager();
    $listener = new SimpleListener();

    $em->listen('*', $listener);
    $em->listen('post.created', $listener);

    $em->dispatch('post.created', ['id' => 1]);

    expect($listener->calls)->toHaveCount(1);
});

it('dispatches to global wildcard and specific listeners together', function () {
    $em = new EventManager();
    $global = new SimpleListener();
    $specific = new AnotherListener();

    $em->listen('*', $global);
    $em->listen('post.created', $specific);

    $em->dispatch('post.created', ['id' => 1]);

    expect($global->calls)->toHaveCount(1)
        ->and($specific->calls)->toHaveCount(1);
});

it('deduplicates across multiple overlapping wildcards', function () {
    $em = new EventManager();
    $listener = new SimpleListener();

    $em->listen('post.created', $listener);
    $em->listen('post.*', $listener);
    $em->listen('*', $listener);

    $em->dispatch('post.created', ['id' => 1]);

    expect($listener->calls)->toHaveCount(1);
});

it('dispatches to multiple different wildcards matching same event', function () {
    $em = new EventManager();
    $first = new SimpleListener();
    $second = new AnotherListener();

    $em->listen('post.*', $first);
    $em->listen('*', $second);

    $em->dispatch('post.created', ['id' => 1]);

    expect($first->calls)->toHaveCount(1)
        ->and($second->calls)->toHaveCount(1);
});
