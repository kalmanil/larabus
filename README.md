# 🚍 Larabus Framework

**Multi-app Laravel framework for managing multiple websites from a single codebase.**

![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)
![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)
![License](https://img.shields.io/badge/License-MIT-green.svg)

## 🎯 **What is Larabus?**

Larabus is a Laravel-based framework that allows you to manage multiple websites/applications from a single codebase. Perfect for:

- **Multi-tenant applications**
- **Multiple client websites** 
- **Different presentation layers** (site/api/cms)
- **MVVM architecture** implementation
- **Scalable hosting** with minimal duplication

## ⚡ **Key Features**

- ✅ **Centralized codebase** - All apps in `apps/` folder
- ✅ **Minimal domain setup** - Just config + entry point per site
- ✅ **Dynamic loading** - Routes and views loaded based on domain
- ✅ **Laravel 12 compatible** - Full Laravel ecosystem support
- ✅ **MVVM ready** - Models don't know about views
- ✅ **Easy deployment** - Deploy once, manage multiple sites

## 🚀 **Quick Installation**

### Option 1: Composer (Recommended)

```bash
composer create-project kalmanil/larabus my-project
cd my-project
```

### Option 2: Git Clone

```bash
git clone https://github.com/kalmanil/larabus.git
cd larabus
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

## 📁 **Project Structure**

```
your-hosting/
├── 🚍 larabus/                    # Central framework (deploy once)
│   ├── apps/                      # All application code
│   │   ├── site1/
│   │   │   ├── routes.php
│   │   │   └── resources/views/
│   │   └── site2/
│   │       ├── routes.php
│   │       └── resources/views/
│   └── [standard Laravel structure]
├── 🌐 domain1.com/                # Minimal domain folder
│   ├── config.php                 # Domain configuration
│   ├── index.php                  # Entry point → larabus
│   └── .htaccess                  # URL rewriting
└── 🌐 domain2.com/                # Another domain
    ├── config.php
    ├── index.php
    └── .htaccess
```

## 🎯 **How It Works**

1. **Request** hits `domain1.com/index.php`
2. **Domain config** loads from `config.php` into `$_ENV` variables  
3. **Larabus** takes control via central framework
4. **LarabusServiceProvider** reads domain settings and loads:
   - Routes from `apps/{app}/routes.php`
   - Views from `apps/{app}/resources/views/`
5. **Laravel** processes request with correct app context

## 🏗️ **Adding New Sites**

### 1. Create App Structure
```bash
mkdir larabus/apps/mynewsite
mkdir larabus/apps/mynewsite/resources/views
```

### 2. Add Routes
```php
<?php // larabus/apps/mynewsite/routes.php
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/', function () {
        return view($_ENV['DOMAIN_VIEW_TEMPLATE'], [
            'siteName' => $_ENV['DOMAIN_SITE_TITLE'],
            'domain' => request()->getHost(),
            'app' => $_ENV['DOMAIN_APP_NAME'],
        ]);
    });
});
```

### 3. Create Domain Folder
```php
<?php // mynewsite.com/config.php
return [
    'app_name' => 'mynewsite',
    'site_title' => 'My New Site',
    'view_template' => 'welcome',
    'theme_color' => '#ff6b6b'
];
```

```php
<?php // mynewsite.com/index.php
$domainConfig = require __DIR__ . '/config.php';
foreach ($domainConfig as $key => $value) {
    $_ENV['DOMAIN_' . strtoupper($key)] = $value;
}
require __DIR__ . '/../larabus/public/index.php';
```

## 🌐 **Deployment Guide**

### Shared Hosting Setup

1. **Upload larabus** to your hosting account (outside public_html)
2. **Create domain folders** in public_html for each domain:
   ```
   public_html/
   ├── domain1.com/        # Point domain1.com here
   │   ├── config.php
   │   ├── index.php  
   │   └── .htaccess
   └── domain2.com/        # Point domain2.com here
       ├── config.php
       ├── index.php
       └── .htaccess
   ```
3. **Configure domains** to point to their respective folders
4. **Set permissions** (755 for folders, 644 for files)

### VPS/Dedicated Server

1. **Clone/deploy** larabus to `/var/www/larabus/`
2. **Create virtual hosts** for each domain pointing to domain folders
3. **Configure Nginx/Apache** with proper document roots
4. **Set up SSL** certificates for each domain

## 📦 **Package Development**

### Local Development with Symlink

```bash
# In your Laravel project
composer config repositories.larabus path "../larabus"
composer require "kalmanil/larabus:dev-main"
```

### Publishing to Packagist

1. Tag your release: `git tag v1.0.0`
2. Push tags: `git push --tags`
3. Submit to [Packagist.org](https://packagist.org)

## 🎨 **MVVM Implementation**

Larabus supports MVVM pattern where models don't know about views:

```php
// In your app routes
Route::get('/user/{id}', function($id) {
    $user = User::find($id);
    
    // ViewModel assigned dynamically based on context
    $viewModel = app(UserViewModelFactory::class)
        ->create($_ENV['DOMAIN_APP_NAME'], $user);
    
    return view($_ENV['DOMAIN_VIEW_TEMPLATE'], compact('viewModel'));
});
```

## 🤝 **Contributing**

1. Fork the repository
2. Create feature branch: `git checkout -b feature/amazing-feature`
3. Commit changes: `git commit -m 'Add amazing feature'`
4. Push branch: `git push origin feature/amazing-feature`
5. Open Pull Request

## 📄 **License**

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 **Author**

**Kalmanil** - [GitHub](https://github.com/kalmanil)

---

⭐ **Star this repo** if Larabus helps you manage multiple websites efficiently!