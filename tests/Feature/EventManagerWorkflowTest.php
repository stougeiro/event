<?php

use STDW\Event\EventManager;
use Tests\Stubs\SimpleListener;
use Tests\Stubs\AnotherListener;
use Tests\Stubs\DispatchCounter;

beforeEach(function () {
    DispatchCounter::reset();
});

it('handles complete workflow: listen → dispatch → verify', function () {
    $em = new EventManager();
    $listener = new SimpleListener();

    $em->listen('user.registered', $listener);

    $em->dispatch('user.registered', ['user_id' => 1, 'name' => 'John']);
    $em->dispatch('user.registered', ['user_id' => 2, 'name' => 'Jane']);

    expect($listener->calls)->toHaveCount(2)
        ->and($listener->calls[0])->toBe(['user_id' => 1, 'name' => 'John'])
        ->and($listener->calls[1])->toBe(['user_id' => 2, 'name' => 'Jane']);
});

it('handles wildcard subscription matching multiple events', function () {
    $em = new EventManager();
    $listener = new SimpleListener();

    $em->listen('order.*', $listener);

    $em->dispatch('order.created', ['order_id' => 1]);
    $em->dispatch('order.paid', ['order_id' => 1]);
    $em->dispatch('order.shipped', ['order_id' => 1]);

    expect($listener->calls)->toHaveCount(3);
});

it('handles mixed exact and wildcard listeners', function () {
    $em = new EventManager();
    $exact = new SimpleListener();
    $wildcard = new AnotherListener();

    $em->listen('invoice.created', $exact);
    $em->listen('invoice.*', $wildcard);

    $em->dispatch('invoice.created', ['invoice_id' => 100]);

    expect($exact->calls)->toHaveCount(1)
        ->and($wildcard->calls)->toHaveCount(1);
});

it('handles multiple events with different listeners', function () {
    $em = new EventManager();
    $logger = new SimpleListener();
    $notifier = new AnotherListener();

    $em->listen('payment.completed', $logger);
    $em->listen('payment.completed', $notifier);

    $em->dispatch('payment.completed', ['amount' => 50.00]);

    expect($logger->calls)->toHaveCount(1)
        ->and($notifier->calls)->toHaveCount(1);
});

it('loads from file and dispatches correctly', function () {
    $em = new EventManager();
    $em->load(__DIR__ . '/../Fixtures/events.php');

    $em->dispatch('post.created', ['title' => 'New Post']);

    expect(DispatchCounter::$count)->toBe(1);
});

it('isolates event data between dispatches', function () {
    $em = new EventManager();
    $listener = new SimpleListener();

    $em->listen('click.track', $listener);

    $em->dispatch('click.track', ['button' => 'submit']);
    $em->dispatch('click.track', ['button' => 'cancel']);

    expect($listener->calls[0])->toBe(['button' => 'submit'])
        ->and($listener->calls[1])->toBe(['button' => 'cancel']);
});
