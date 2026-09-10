<?php declare(strict_types=1);

    namespace STDW\Event;

    use STDW\Contract\Event\EventManagerInterface;
    use STDW\Contract\Event\EventListenerInterface;


    class EventManager implements EventManagerInterface
    {
        /** @var array<string, array<string, EventListenerInterface>>
         */
        protected array $events = [];


        public function __construct()
        { }


        /**
         * @param string $file 
         * @return void 
         */
        public function load(string $file): void
        {
            if ( ! file_exists($file)) {
                throw new EventException("Event file '{$file}' not found");
            }

            $events = include $file;

            if ( ! is_array($events)) {
                throw new EventException("Event file '{$file}' must return an array");
            }

            foreach ($events as $event => $listeners) {
                $listeners = is_array($listeners) ? $listeners : [$listeners];

                foreach ($listeners as $listener) {
                    if ( ! is_string($listener)) {
                        throw new EventException("Listener must be a class name string");
                    }

                    if ( ! is_subclass_of($listener, EventListenerInterface::class)) {
                        throw new EventException("Listener '{$listener}' must implement EventListenerInterface");
                    }

                    $this->listen($event, new $listener);
                }
            }
        }

        /**
         * @param string $event 
         * @param EventListenerInterface $listener 
         * @return void 
         */
        public function listen(string $event, EventListenerInterface $listener): void
        {
            if ( ! $this->isValidEventName($event)) {
                throw new EventException("Invalid event name '{$event}'");
            }

            $id = get_class($listener);

            $this->events[$event][$id] = $listener;
        }

        /**
         * @param string $event 
         * @param array<string, mixed> $data 
         * @return void 
         */
        public function dispatch(string $event, array $data): void
        {
            if (str_contains($event, '*')) {
                throw new EventException("Wildcard events cannot be dispatched");
            }

            $listeners = $this->resolveListeners($event);

            foreach ($listeners as $listener) {
                $listener($data);
            }
        }


        /**
         * @param string $event 
         * @return bool 
         */
        protected function isValidEventName(string $event): bool
        {
            return preg_match('/^[a-zA-Z0-9]+([.:\-][a-zA-Z0-9]+)*(?:[.:\-]\*)?$/', $event) === 1;
        }

        /**
         * @param string $event 
         * @return array<string, EventListenerInterface> 
         */
        protected function resolveListeners(string $event): array
        {
            $resolved = [];

            foreach ($this->events as $pattern => $list) {

                if ($pattern === $event) {
                    foreach ($list as $id => $listener) {
                        $resolved[$id] = $listener;
                    }

                    continue;
                }

                if (str_contains($pattern, '*')) {
                    $regex = '/^' . str_replace('\*', '.+', preg_quote($pattern, '/')) . '$/';

                    if (preg_match($regex, $event)) {
                        foreach ($list as $id => $listener) {
                            $resolved[$id] = $listener;
                        }
                    }
                }
            }

            return $resolved;
        }
    }
