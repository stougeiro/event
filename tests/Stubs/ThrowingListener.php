<?php

declare(strict_types=1);

namespace Tests\Stubs;

use STDW\Contract\Event\EventListenerInterface;
use RuntimeException;

class ThrowingListener implements EventListenerInterface
{
    public function __invoke(array $data): void
    {
        throw new RuntimeException('Listener failed');
    }
}
