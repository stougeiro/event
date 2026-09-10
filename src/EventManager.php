<?php declare(strict_types=1);

    namespace STDW\Event;

    use STDW\Contract\Event\EventManagerInterface;
    use STDW\Contract\Event\EventListenerInterface;


    class EventManager implements EventManagerInterface
    {
        /** @var array<string, array<EventListenerInterface>>
         */
        protected array $events = [];

        /** @var array<string, bool>
         */
        protected array $listeners = [];


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

            if (isset($this->listeners[$id])) {
                throw new EventException("Listener '{$id}' is already registered");
            }

            $this->events[$event][] = $listener;
            $this->listeners[$id] = true;
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
            return preg_match('/^[a-zA-Z0-9.\*]+$/', $event) === 1;
        }

        /**
         * @param string $event 
         * @return array<EventListenerInterface> 
         */
        protected function resolveListeners(string $event): array
        {
            $resolved = [];

            foreach ($this->events as $pattern => $list) {

                if ($pattern === $event) {
                    array_push($resolved, ...$list);

                    continue;
                }

                if (str_contains($pattern, '*')) {
                    $regex = '/^' . str_replace('\*', '.+', preg_quote($pattern, '/')) . '$/';

                    if (preg_match($regex, $event)) {
                        array_push($resolved, ...$list);
                    }
                }
            }

            return $resolved;
        }
    }
