<?php declare(strict_types=1);

    use STDW\Event\EventManager;


    if ( ! function_exists('events'))
    {
        /** @return EventManager 
         */
        function events(): EventManager
        {
            static $em;
            $em ??= new EventManager();

            return $em;
        }
    }

    if ( ! function_exists('event'))
    {
        /**
         * @param string $event
         * @param array<string, mixed> $data
         * @return void
         */
        function event(string $event, array $data = []): void
        {
            events()->dispatch($event, $data);
        }
    }
