<?php

declare(strict_types=1);

namespace Tests\Stubs;

use STDW\Event\EventListenerAbstracted;

class ConcreteListener extends EventListenerAbstracted
{
    /** @var array<int, array<string, mixed>> */
    public array $calls = [];

    public function __invoke(array $data): void
    {
        $this->calls[] = $data;
    }
}
