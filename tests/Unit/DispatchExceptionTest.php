<?php

use STDW\Event\EventManager;
use Tests\Stubs\SimpleListener;
use Tests\Stubs\AnotherListener;
use Tests\Stubs\ThrowingListener;

it('exception in listener stops subsequent listeners', function () {
    $em = new EventManager();
    $before = new SimpleListener();
    $throwing = new ThrowingListener();
    $after = new AnotherListener();

    $em->listen('test.event', $before);
    $em->listen('test.event', $throwing);
    $em->listen('test.event', $after);

    try {
        $em->dispatch('test.event', ['id' => 1]);
    } catch (RuntimeException $e) {
        // Expected
    }

    expect($before->calls)->toHaveCount(1);
});

it('exception is propagated from listener', function () {
    $em = new EventManager();
    $em->listen('test.event', new ThrowingListener());

    $em->dispatch('test.event', ['id' => 1]);
})->throws(RuntimeException::class, 'Listener failed');

it('first listener executes before exception', function () {
    $em = new EventManager();
    $first = new SimpleListener();
    $second = new ThrowingListener();

    $em->listen('test.event', $first);
    $em->listen('test.event', $second);

    try {
        $em->dispatch('test.event', ['data' => 'test']);
    } catch (RuntimeException $e) {
        // Expected
    }

    expect($first->calls)->toHaveCount(1)
        ->and($first->calls[0])->toBe(['data' => 'test']);
});

it('wildcard listener executes before throwing listener', function () {
    $em = new EventManager();
    $wildcard = new AnotherListener();
    $throwing = new ThrowingListener();

    $em->listen('test.*', $wildcard);
    $em->listen('test.event', $throwing);

    try {
        $em->dispatch('test.event', ['id' => 1]);
    } catch (RuntimeException $e) {
        // Expected
    }

    expect($wildcard->calls)->toHaveCount(1);
});
