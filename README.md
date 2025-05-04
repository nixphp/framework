
<div style="text-align: center">

![Logo](https://nixphp.github.io/docs/assets/nixphp-logo-small-square.png)

[![NixPHP Build & Test](https://github.com/nixphp/framework/actions/workflows/php.yml/badge.svg)](https://github.com/nixphp/framework/actions/workflows/php.yml)

</div>

---

# NixPHP

> **"As simple as possible, as flexible as necessary."**

**NixPHP** is a modern, lightweight PHP microframework designed for real-world projects:  
fast, minimal, extendable — and now fully embracing modern PHP standards like PSR-3, PSR-4, PSR-7, PSR-11 and PSR-18.

It builds on native PHP features and lets you stay in control:  
**Use only what you need — and extend freely when you want.**

> 🧩 NixPHP provides a minimal core with a clean plugin architecture.  
> Everything beyond routing and dispatching — such as sessions, views, forms, or database — is handled by optional plugins.  
> You get full control over what your app includes — and nothing more.

---

## ✨ Philosophy

- **Minimalist Core**: Only essential components by default.
- **PSR-First**: Native support for key PHP standards (PSR-3, PSR-4, PSR-7, PSR-11, PSR-18).
- **Extendable**: Easily plug in external libraries — Blade, Twig, Eloquent, Middleware, etc.
- **Transparent by Design**: No hidden magic, no complicated abstractions.
- **Native PHP Power**: PDO database, clean routing, lightweight templating.
- **Secure and Clear Structure**: Safe public directory (`public/`) separated from your app code.

---

## 📦 Core Features

- **✅ Plugin System**: Add reusable features via Composer
- **✅ Lightweight Routing**: Define routes with `[Controller::class, 'method']`
- **✅ Smart Dispatcher**: Automatic parameter and controller resolution
- **✅ PSR-3 Logging** (lightweight logger ready to use)
- **✅ PSR-4 Autoloading** (Composer)
- **✅ PSR-7 Request/Response Handling**
- **✅ PSR-11 Dependency Container** (for flexible dependency injection)
- **✅ PSR-18 HTTP Client** (via `nixphp/client`)
- **✅ Minimalist View System**: Block-based templating (via `nixphp/view`)
- **✅ PDO Database Connection** (via `nixphp/database`)
- **✅ Session Handling** (via `nixphp/session`)
- **✅ Form Memory Helpers** (via `nixphp/form`)
- **✅ Output Buffering** (centralized, better debugging)
- **✅ JSON Response Helper** (for easy API responses)
- **✅ Composer-Ready**: Easy installation and dependency management

---

## 📚 PSR Compliance Overview

| PSR | Description | Status                             |
|:---|:---|:---------------------------------------------|
| PSR-3 | Logger Interface | ✅ Integrated               |
| PSR-4 | Autoloading Standard | ✅ Native via Composer  |
| PSR-7 | HTTP Message Interface | ✅ Integrated         |
| PSR-11 | Container Interface | ✅ Integrated           |
| PSR-18 | HTTP Client Interface | ✅ Integrated         |


---

## ❓ Why not just use Laravel or Symfony?

Frameworks like Laravel and Symfony are fantastic — but they often come with a heavy stack of features, conventions, and dependencies you may not always need.

**NixPHP** offers a different approach:

- **No hidden complexity**: You see exactly what happens.
- **No forced patterns**: Use only what you need, when you need it.
- **No unnecessary overhead**: Keep performance and flexibility under your control.
- **Real extendability**: Bring your favorite libraries if needed — but stay light if you don't.

If you want full control without fighting against a "big framework" structure,  
**NixPHP** might be the perfect starting point for you.

---

# 📢 Installation

## Install via Composer

```bash
composer require nixphp/framework
```

This installs the **NixPHP core** — a minimal routing and dispatch layer.  
For additional features like views, forms or sessions, just install the corresponding plugins.

---

## Set up your project structure

NixPHP leaves the project organization completely up to you.  
A typical structure could look like this:

```
/app
    /Controllers
    /Models
    /Services
    config.php
    routes.php
/public
    index.php
bootstrap.php
composer.json
```

- **Plural names** for app folders
- **`public/`** as webroot for higher security
- **`bootstrap.php`** for autoloading and bootstrapping services, registering events, and so on...

---

## Creating your App

You typically...

- Create a `bootstrap.php` to initialize NixPHP
- Set up your `routes.php`
- Create a `public/index.php` as your web entry point (which includes bootstrap.php)

1. **Fill bootstrap.php**

```php
// /bootstrap.php

define('BASE_PATH', __DIR__);

require __DIR__ . '/../vendor/autoload.php';

use function NixPHP\app;

app()->run(); // Start the application
```

2. **Create a route**

```php
//File: app/routes.php

router()->add('GET', '/hello', [HelloController::class, 'index']);
```

3. **Create a controller**

```php
//File: app/Controllers/HelloController.php

namespace App\Controllers;

class HelloController
{
    public function index()
    {
        return view('hello', ['name' => 'World']);
    }
}
```

4. **Create a view**

```php
//File: app/views/hello.phtml

use function NixPHP\s; // Sanitize on output (provided through nixphp/view)

<h1>Hello, <?= s($name) ?>!</h1>
```

5. **Access your page**

Visit:
```
http://your-app.local/hello
```

You should see:

```
Hello, World!
```

---

## 🔌 Plugin Support

NixPHP includes a clean plugin system that allows you to extend your app modularly — without configuration.

Just install a plugin via Composer (e.g. `composer require vendor/my-plugin`) and it is automatically detected if it uses the correct package type:

```json
{
  "type": "nixphp-plugin"
}
```

A typical plugin might look like this:

```
my-plugin/
├── app/
│   ├── config.php
│   └── views/
│       └── errors/404.phtml
├── bootstrap.php
└── composer.json
```

- `config.php` is automatically merged.
- `views/` are added to the view search path.
- `bootstrap.php` runs automatically to register routes, events, etc.

You can build plugins exactly like you build an app — with full access to routing, events, and controllers.

> For example plugins, see the [Plugin Documentation](https://nixphp.github.io/docs/plugins/)

# 🔥 Ready to build?

Welcome to **NixPHP** —  
your minimalist, modern PHP playground. 🚀

---

# 🙌 License

MIT License.