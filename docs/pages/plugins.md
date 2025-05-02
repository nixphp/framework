# Plugins

NixPHP includes a clean and lightweight plugin system that allows you to extend the framework with zero configuration.

Plugins can provide additional configuration, templates (views), and custom logic via a `bootstrap.php` file. Once a plugin is installed via Composer, it is automatically detected and integrated.

---

## Plugin Structure

A NixPHP plugin mimics the structure of a full app:

```
your-plugin/
├── app/
│   ├── config.php         // Plugin-specific configuration
│   └── views/             // Plugin-specific templates
│       └── example.phtml
├── bootstrap.php          // Bootstrap logic (routes, events, services, etc.)
└── composer.json
```

- `app/config.php` is merged into the global config.
- `app/views/` is added to the view resolver.
- `bootstrap.php` is automatically executed when the plugin is discovered.

---

## 🛠 Example `composer.json`

Below is a minimal but complete `composer.json` for a NixPHP plugin:

```json
{
  "name": "vendor/NixPHP-plugin-example",
  "description": "Skeleton for your first plugin when using NixPHP",
  "type": "NixPHP-plugin",
  "license": "MIT",
  "authors": [
    {
      "name": "Your Name",
      "email": "your@mail.com"
    }
  ],
  "require": {
    "php": ">=8.3",
    "fkde/NixPHP": "dev-main"
  },
  "autoload": {
    "psr-4": {
      "MyPlugin\\": "app/"
    }
  },
  "minimum-stability": "stable",
  "prefer-stable": true
}
```

> ✅ Important:
> - `"type": "NixPHP-plugin"` is required for discovery.
> - The namespace (e.g. `MyPlugin\\`) must match your plugin classes location.

Run:

```bash
composer dump-autoload
```

To ensure your classes are properly registered.

---

## Automatic Discovery

Plugins are discovered via Composer using the package `"type": "NixPHP-plugin"`. Once installed, NixPHP will:

- Load `bootstrap.php`
- Merge `app/config.php`
- Register all `app/views/` templates

No manual registration is needed.

---

## Accessing Plugin Metadata

```php
plugin()->getMeta('viewPaths');
plugin()->getMeta('configPaths');
plugin()->getMeta('bootstraps');
```

For internal use or debugging only – no need to register anything yourself.

---

## Example Plugin: Error Views

```plaintext
my-error-plugin/
├── app/
│   └── views/
│       └── errors/
│           ├── 404.phtml
│           └── 500.phtml
└── bootstrap.php
```

Usage:

```php
render('errors.404');
```

---

## Example Plugin: Routing to a Controller

**Structure:**

```
my-hello-plugin/
├── app/
│   └── Controllers/
│       └── HelloController.php
├── bootstrap.php
└── composer.json
```

**HelloController.php:**

```php
namespace MyHelloPlugin\Controllers;

use Psr\Http\Message\ResponseInterface;
use function NixPHP\response;

class HelloController
{
    public function index(): ResponseInterface
    {
        return response('Hello from the plugin controller!');
    }
}
```

**bootstrap.php:**

```php
use MyHelloPlugin\Controllers\HelloController;
use function NixPHP\route;

route()->add('GET', '/plugin-hello', [HelloController::class, 'index']);
```

Visit: `http://yourapp.local/plugin-hello`

---

## View Resolution Order

1. App `app/views/`
2. Plugins `plugin/app/views/`
3. Framework `core/Resources/views/`

---

## Config Merge Order

1. App `app/config.php`
2. Plugins `app/config.php`
3. Framework `src/config.php`

---

## Summary

- Plugins mimic the structure of the main app (`app/config.php`, `app/views/`, `Controllers/`)
- Auto-loaded by Composer if `"type": "NixPHP-plugin"` is set
- Can register routes, logic, templates, config – with no extra steps
- Can be overridden by the app cleanly
- `plugin()` helper gives access to metadata for advanced use