<?php

use STDW\Event\EventException;
use STDW\Event\EventManager;
use Tests\Stubs\SimpleListener;
use Tests\Stubs\AnotherListener;

it('registers a listener for an event', function () {
    $em = new EventManager();
    $listener = new SimpleListener();

    $em->listen('post.created', $listener);

    $em->dispatch('post.created', ['id' => 1]);
    expect($listener->calls)->toHaveCount(1);
});

it('registers multiple listeners for the same event', function () {
    $em = new EventManager();
    $simple = new SimpleListener();
    $another = new AnotherListener();

    $em->listen('post.created', $simple);
    $em->listen('post.created', $another);

    $em->dispatch('post.created', ['id' => 1]);
    expect($simple->calls)->toHaveCount(1)
        ->and($another->calls)->toHaveCount(1);
});

it('overwrites same class listener for same event', function () {
    $em = new EventManager();
    $first = new SimpleListener();
    $second = new SimpleListener();

    $em->listen('post.created', $first);
    $em->listen('post.created', $second);

    $em->dispatch('post.created', ['id' => 1]);
    expect($first->calls)->toHaveCount(0)
        ->and($second->calls)->toHaveCount(1);
});

it('allows same class for different events', function () {
    $em = new EventManager();
    $simple = new SimpleListener();

    $em->listen('post.created', $simple);
    $em->listen('user.created', $simple);

    $em->dispatch('post.created', ['id' => 1]);
    $em->dispatch('user.created', ['id' => 2]);

    expect($simple->calls)->toHaveCount(2);
});

it('throws exception on invalid event name', function () {
    $em = new EventManager();
    $listener = new SimpleListener();

    $em->listen('invalid event name', $listener);
})->throws(EventException::class, "Invalid event name 'invalid event name'");

it('accepts dots in event name', function () {
    $em = new EventManager();
    $listener = new SimpleListener();

    $em->listen('app.module.event', $listener);

    $em->dispatch('app.module.event', []);
    expect($listener->calls)->toHaveCount(1);
});

it('accepts colons in event name', function () {
    $em = new EventManager();
    $listener = new SimpleListener();

    $em->listen('user:created', $listener);

    $em->dispatch('user:created', []);
    expect($listener->calls)->toHaveCount(1);
});

it('accepts hyphens in event name', function () {
    $em = new EventManager();
    $listener = new SimpleListener();

    $em->listen('user-created', $listener);

    $em->dispatch('user-created', []);
    expect($listener->calls)->toHaveCount(1);
});

it('accepts wildcard at the end', function () {
    $em = new EventManager();
    $listener = new SimpleListener();

    $em->listen('post.*', $listener);

    $em->dispatch('post.created', []);
    expect($listener->calls)->toHaveCount(1);
});

it('accepts wildcard with colon separator', function () {
    $em = new EventManager();
    $listener = new SimpleListener();

    $em->listen('user:*', $listener);

    $em->dispatch('user:created', []);
    expect($listener->calls)->toHaveCount(1);
});

it('accepts global wildcard *', function () {
    $em = new EventManager();
    $listener = new SimpleListener();

    $em->listen('*', $listener);

    expect(true)->toBeTrue();
});
