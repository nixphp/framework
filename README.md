# PHPico

> **"As simple as possible, as flexible as necessary."**

**PHPico** is a lightweight, functional PHP microframework created with a clear goal:  
to offer everything you need to start real-world projects, while staying fast, transparent, and minimal.

It is built on native PHP features, follows modern best practices, and lets you extend it freely without getting in your way.

---

## ✨ Philosophy

- **Minimalist**: Only the essentials are included by default.
- **Flexible**: You can easily plug in external tools like Blade, Twig, Eloquent, or PSR-7 components if needed.
- **Transparent**: No hidden magic, no complex abstractions. Everything is visible and understandable.
- **Native-first**: Uses native PHP strengths like PDO, simple routing, clean templating, and PSR-4 autoloading.
- **Secure**: Clear separation between application and public directories.

---

## 📦 Features

- **PSR-4 Autoloading** (via Composer)
- **Lightweight Routing**: Map routes to `[Controller::class, 'method']`
- **Automatic Dispatching**: Controllers and method parameters are automatically resolved.
- **Smart Response Handling**: Returns HTML or JSON automatically based on the content.
- **Simple View System**: Template inheritance, blocks, and layouts — no Blade or Twig needed (but possible to integrate).
- **PDO Database Connection**: Configurable and ready to use.
- **Form Memory Helpers**: Restore user input easily after validation errors.
- **Output Buffering**: Managed centrally for better debugging.
- **Clear Folder Structure**: Safe and public-friendly (`public/`, `app/`, `vendor/`, etc.)
- **Composer-Ready**: Just install and go.

---

## ❓ Why not just use Laravel or Symfony?

Frameworks like Laravel and Symfony are fantastic — but they often come with a heavy stack of features, conventions, and dependencies you may not always need.

**PHPico** offers a different approach:

- **No hidden complexity**: You see exactly what happens.
- **No forced patterns**: Use only what you need, when you need it.
- **No unnecessary overhead**: Keep performance and flexibility under your control.
- **Real extendability**: Bring your favorite libraries if needed — but stay light if you don't.

If you want full control without fighting against a "big framework" structure,  
**PHPico** might be the perfect starting point for you.

---

## 🧠 Design Principles

- **Don't reinvent the wheel**: Extend only where necessary.
- **Stay lightweight**: No forced ORMs, templating engines, or service containers.
- **Stay readable**: Code should be easy to understand even after months.
- **Stay optional**: Want PSR-7? Middleware? Dependency Injection? You can add it when needed — but you don't have to.

---

## 🎯 In short

**PHPico** is a modern, minimal microframework for those who love  
**clarity, speed, and true PHP craftsmanship** — without unnecessary overhead.

It gives you **everything you need to build real applications**,  
and **nothing you don't**.

---

## 📂 Project Structure

```
/app
    /Controllers
    /Models
    /Views
    config.php
    routes.php
/public
    index.php
bootstrap.php
composer.json
```

- All core app folders are in **plural** form (Controllers, Models, views).
- The `public/` folder is the webroot.
- `bootstrap.php` handles initialization and autoloading.

---

## 🚀 Quick Example

```php
// Define a route
router()->add('GET', '/hello', [HelloController::class, 'index']);

// Create a controller
class HelloController
{
    public function index()
    {
        return view('hello', ['name' => 'World']);
    }
}
```

---

## 🛠 Planned Extensions

| Feature | Description |
|:---|:---|
| PSR-7 Request/Response support | Native, or via external library like `nyholm/psr7` |
| Middleware support | Before/after route hooks |
| Route groups | Prefixing and shared options for routes |
| Optional DI Container | For cleaner dependency management |
| Simple Logging | Debug and error logging made easy |

---

# 📢 Installation

Coming soon. (If you are reading this early: clone and set up manually.)

---

## 🚀 First Steps

1. **Create a route**

```php
//File: app/routes.php

router()->add('GET', '/hello', [HelloController::class, 'index']);
```

2. **Create a controller**

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

3. **Create a view**

```php
//File: app/views/hello.phtml

<h1>Hello, <?= htmlspecialchars($name) ?>!</h1>
```

4. **Access your page**

Visit:
```
http://your-app.local/hello
```

You should see:

```
Hello, World!
```

---

# 🙌 License

MIT License.

---

# 🔥 That's it!

Ready to build something awesome?  
Welcome to **PHPico**. 🚀
