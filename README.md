# PHPico

> **"As simple as possible, as flexible as necessary."**

**PHPico** is a modern, lightweight PHP microframework designed for real-world projects:  
fast, minimal, extendable — and now fully embracing modern PHP standards like PSR-7, PSR-11, PSR-18, and PSR-3.

It builds on native PHP features and lets you stay in control:  
**Use only what you need — and extend freely when you want.**

---

## ✨ Philosophy

- **Minimalist Core**: Only essential components by default.
- **PSR-First**: Native support for key PHP standards (PSR-7, PSR-11, PSR-18, PSR-3).
- **Extendable**: Easily plug in external libraries — Blade, Twig, Eloquent, Middleware, etc.
- **Transparent by Design**: No hidden magic, no complicated abstractions.
- **Native PHP Power**: PDO database, clean routing, lightweight templating.
- **Secure and Clear Structure**: Safe public directory (`public/`) separated from your app code.

---

## 📦 Core Features

- **✅ PSR-4 Autoloading** (Composer)
- **✅ Lightweight Routing**: Define routes with `[Controller::class, 'method']`
- **✅ Smart Dispatcher**: Automatic parameter and controller resolution
- **✅ Native PSR-7 Request/Response Handling** (integrated or extendable)
- **✅ PSR-18 HTTP Client** (ready for easy API communication)
- **✅ PSR-11 Dependency Container** (for flexible dependency injection)
- **✅ PSR-3 Logging** (lightweight logger ready to use)
- **✅ Minimalist View System**: Block-based templating (no Blade/Twig needed)
- **✅ PDO Database Connection**
- **✅ Session Handling** (with a simple Session object)
- **✅ Form Memory Helpers** (preserve input after validation)
- **✅ Output Buffering** (centralized, better debugging)
- **✅ JSON Response Helper** (for easy API responses)
- **✅ Composer-Ready**: Easy installation and dependency management

---

## 📚 PSR Compliance Overview

| PSR | Description | Status |
|:---|:---|:---|
| PSR-4 | Autoloading Standard | ✅ Native via Composer |
| PSR-7 | HTTP Message Interface | ✅ Integrated |
| PSR-11 | Container Interface | ✅ Available |
| PSR-18 | HTTP Client Interface | ✅ Available |
| PSR-3 | Logger Interface | ✅ Available |

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

- **Plural names** for app folders
- **`public/`** as webroot for maximum security
- **`bootstrap.php`** for initialization and autoloading

---

## 🚀 Quick Example

```php
// Define a route (app/routes.php)
router()->add('GET', '/hello', [HelloController::class, 'index']);



// Create a controller (app/Controllers/HelloController.php)
class HelloController
{
    public function index()
    {
        return render('hello', ['name' => 'World']);
    }
}
```

```php
// Simple JSON response everywhere
//... 
return json(['success' => true, 'message' => 'Hello, API!']);
```

---

# 📢 Installation

Coming soon. (Until then: clone and set up manually.)

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

# 🔥 Ready to build?

Welcome to **PHPico** —  
your minimalist, modern PHP playground. 🚀
