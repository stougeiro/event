<?php

use STDW\Event\EventManager;
use STDW\Contract\Event\EventListenerInterface;
use Tests\Stubs\ConcreteListener;

it('concrete listener implements EventListenerInterface', function () {
    $listener = new ConcreteListener();
    expect($listener)->toBeInstanceOf(EventListenerInterface::class);
});

it('concrete listener invokes correctly', function () {
    $listener = new ConcreteListener();
    $data = ['key' => 'value'];

    $listener($data);

    expect($listener->calls)->toHaveCount(1)
        ->and($listener->calls[0])->toBe($data);
});

it('concrete listener works with EventManager', function () {
    $em = new EventManager();
    $listener = new ConcreteListener();

    $em->listen('test.event', $listener);
    $em->dispatch('test.event', ['id' => 1]);

    expect($listener->calls)->toHaveCount(1)
        ->and($listener->calls[0])->toBe(['id' => 1]);
});

it('concrete listener handles multiple dispatches', function () {
    $listener = new ConcreteListener();

    $listener(['step' => 1]);
    $listener(['step' => 2]);
    $listener(['step' => 3]);

    expect($listener->calls)->toHaveCount(3);
});
