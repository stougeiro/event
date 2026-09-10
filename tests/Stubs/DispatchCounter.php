<?php

declare(strict_types=1);

namespace Tests\Stubs;

use STDW\Contract\Event\EventListenerInterface;

class DispatchCounter implements EventListenerInterface
{
    public static int $count = 0;

    /** @var array<int, array<string, mixed>> */
    public static array $allCalls = [];

    public function __invoke(array $data): void
    {
        self::$count++;
        self::$allCalls[] = $data;
    }

    public static function reset(): void
    {
        self::$count = 0;
        self::$allCalls = [];
    }
}
