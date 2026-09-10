<?php

declare(strict_types=1);

namespace Tests\Stubs;

use STDW\Contract\Event\EventListenerInterface;

class SimpleListener implements EventListenerInterface
{
    /** @var array<int, array<string, mixed>> */
    public array $calls = [];

    public function __invoke(array $data): void
    {
        $this->calls[] = $data;
    }
}
