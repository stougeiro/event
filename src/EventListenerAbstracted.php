<?php declare(strict_types=1);

    namespace STDW\Event;

    use STDW\Contract\Event\EventListenerInterface;


    abstract class EventListenerAbstracted implements EventListenerInterface
    {
        final public function __construct()
        { }


        /**
         * @param array<string, mixed> $data 
         * @return void 
         */
        public abstract function __invoke(array $data): void;
    }
