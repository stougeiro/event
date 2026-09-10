![phpstan-level](https://img.shields.io/badge/PHPStan-Level%209-brightgreen)
![pest-php](https://img.shields.io/badge/Tests-%20Passed-brightgreen)

# Event

Lightweight event dispatcher with wildcard pattern matching and automatic deduplication. Dispatch string-based events, register invokable listeners, and load configurations from PHP files — all with zero cache overhead.

## ✨ Features

- **String-based event dispatching**  
  Events are simple strings, no classes or enums required.

- **Wildcard pattern matching**  
  Register listeners on `post.*` to catch all `post.created`, `post.updated`, etc.

- **Automatic deduplication**  
  Same listener instance on `post.created` and `post.*` executes only once per dispatch.

- **File-based listener loading**  
  Load listener mappings from PHP config files for clean bootstrapping.

- **High performance**  
  Native merge by class name, no cache layers, no auxiliary structures.

- **Framework-agnostic**  
  Works with any PHP 8.2+ project.

- **Global helpers**  
  `event()` and `events()` for quick dispatch without instantiation.

---

## 📦 Installation

```bash
composer require stougeiro/event
```

---

## 📐 Event Name Rules

Event names must follow a strict pattern for consistency and fast regex matching.

### Structure

```
segment.separated.by.dots
```

**Segments**: alphanumeric characters only (`a-z`, `A-Z`, `0-9`)  
**Separators**: `.`, `:`, or `-`  
**Wildcard**: `*` allowed only at the end  

### Valid Names

| Name | Description |
|---|---|
| `post.created` | Standard dot notation |
| `user:registered` | Colon separator |
| `order-item.placed` | Hyphen separator |
| `app.module.event` | Multiple segments |
| `post.*` | Wildcard at end |
| `user:*` | Wildcard with colon |

### Invalid Names

| Name | Reason |
|---|---|
| `post created` | Space not allowed |
| `.post.created` | Cannot start with separator |
| `post.created.` | Cannot end with separator |
| `post..created` | Double separator |
| `post.*.detail` | Wildcard not at end |

---

## 🚀 Usage

Two paths: **instance** or **global helper**.

### Path 1: Instance

```php
use STDW\Event\EventManager;

$em = new EventManager();

$em->listen('order.created', new SendEmailListener());
$em->dispatch('order.created', ['id' => 42]);
```

### Path 2: Global helpers

```php
events()->listen('order.created', new SendEmailListener());
event('order.created', ['id' => 42]);
```

| Function | Returns | Use |
|---|---|---|
| `events()` | `EventManager` | Register listeners, load config |
| `event($name, $data)` | `void` | Dispatch shorthand |

```php
// Register
events()->listen('user.*', new AuditListener());

// Or load from file
events()->load(__DIR__ . '/events.php');

// Dispatch
event('user.created', ['action' => 'created']);
```

Both share the same `EventManager` instance — listeners registered via `events()` are triggered by `event()`.

### Wildcard listeners

```php
events()->listen('user.*', new AuditListener());

event('user.created', ['action' => 'created']);
event('user.deleted', ['action' => 'deleted']);
// Both trigger the same listener
```

### Deduplication: same listener, multiple events

```php
$logger = new LoggingListener();

events()->listen('post.created', $logger);
events()->listen('post.*', $logger);

event('post.created', ['title' => 'Hello']);
// LoggingListener executes ONCE, not twice
```

### Load from file

```php
// events.php
return [
    'order.created' => [SendEmailListener::class],
    'order.paid'    => [UpdateInventoryListener::class],
    'order.*'       => [AuditListener::class],
];

// bootstrap
events()->load(__DIR__ . '/events.php');
event('order.created', ['id' => 42]);
```

---

## 🧠 Why?

Event systems don't need to be complex. This library provides a dispatcher that combines string-based events, wildcard matching, and automatic deduplication — without cache layers, queues, or heavy abstractions.

By using class names as merge keys, duplicate listeners are eliminated naturally at registration and resolution time. The result is a predictable, high-performance dispatcher that works in any PHP 8.2+ environment.

---

## 🤝 Contributions

Contributions are welcome.
Feel free to open issues or submit pull requests.

<br><br>

[<img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" width="170"/>](https://www.buymeacoffee.com/stougeiro)
