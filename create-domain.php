<?php

/**
 * Larabus Domain Creator
 * 
 * Creates new domain folder with all necessary files
 */

if ($argc < 2) {
    echo "🚍 Larabus Domain Creator\n";
    echo "Usage: php create-domain.php <domain_name> [app_name] [site_title] [theme_color]\n";
    echo "Example: php create-domain.php mynewsite.com mynewsite 'My New Site' '#ff6b6b'\n";
    exit(1);
}

$domainName = $argv[1];
$appName = $argv[2] ?? str_replace(['.com', '.net', '.org', '.'], '', $domainName);
$siteTitle = $argv[3] ?? ucfirst($appName);
$themeColor = $argv[4] ?? '#6366f1';

echo "🚍 Creating domain: {$domainName}\n";
echo "📱 App name: {$appName}\n";
echo "🏷️ Site title: {$siteTitle}\n";
echo "🎨 Theme color: {$themeColor}\n\n";

// Create domain folder
$domainPath = "../{$domainName}";
if (!is_dir($domainPath)) {
    mkdir($domainPath, 0755, true);
    echo "✅ Created domain folder: {$domainName}/\n";
} else {
    echo "⚠️ Domain folder already exists: {$domainName}/\n";
}

// Create config.php
$configTemplate = file_get_contents('templates/domain-config.php.template');
$configContent = str_replace([
    '{{APP_NAME}}',
    '{{SITE_TITLE}}',
    '{{VIEW_TEMPLATE}}',
    '{{THEME_COLOR}}'
], [
    $appName,
    $siteTitle,
    $appName . '.welcome',
    $themeColor
], $configTemplate);

file_put_contents("{$domainPath}/config.php", $configContent);
echo "✅ Created config.php\n";

// Create index.php
$indexTemplate = file_get_contents('templates/domain-index.php.template');
file_put_contents("{$domainPath}/index.php", $indexTemplate);
echo "✅ Created index.php\n";

// Create .htaccess
$htaccessTemplate = file_get_contents('templates/domain-htaccess.template');
file_put_contents("{$domainPath}/.htaccess", $htaccessTemplate);
echo "✅ Created .htaccess\n";

// Create router.php for development
$routerContent = '<?php

// Router script for PHP built-in server
$uri = urldecode(parse_url($_SERVER[\'REQUEST_URI\'], PHP_URL_PATH));

if ($uri !== \'/\' && file_exists(__DIR__ . $uri)) {
    return false;
}

require_once __DIR__ . \'/index.php\';';

file_put_contents("{$domainPath}/router.php", $routerContent);
echo "✅ Created router.php\n";

// Create app structure
$appPath = "apps/{$appName}";
if (!is_dir($appPath)) {
    mkdir($appPath, 0755, true);
    mkdir("{$appPath}/resources", 0755, true);
    mkdir("{$appPath}/resources/views", 0755, true);
    mkdir("{$appPath}/resources/views/{$appName}", 0755, true);
    echo "✅ Created app structure: {$appPath}/\n";
    
    // Create routes.php
    $routesContent = '<?php

use Illuminate\Support\Facades\Route;

Route::middleware(\'web\')->group(function () {
    Route::get(\'/\', function () {
        return view($_ENV[\'DOMAIN_VIEW_TEMPLATE\'], [
            \'siteName\' => $_ENV[\'DOMAIN_SITE_TITLE\'],
            \'domain\' => request()->getHost(),
            \'app\' => $_ENV[\'DOMAIN_APP_NAME\'],
            \'themeColor\' => $_ENV[\'DOMAIN_THEME_COLOR\']
        ]);
    });

    Route::get(\'/about\', function () {
        return "About " . $_ENV[\'DOMAIN_SITE_TITLE\'] . " - powered by Larabus" .
               "<br>Domain: " . request()->getHost() .
               "<br>App: " . $_ENV[\'DOMAIN_APP_NAME\'] .
               "<br>Theme: " . $_ENV[\'DOMAIN_THEME_COLOR\'];
    });
});';

    file_put_contents("{$appPath}/routes.php", $routesContent);
    echo "✅ Created routes.php\n";
    
    // Create welcome.blade.php
    $welcomeContent = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $siteName }}</title>
    <style>
        body {
            font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, {{ $themeColor ?? \'#6366f1\' }}, #8b5cf6);
            color: white;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            padding: 2rem;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }
        h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .info {
            margin: 1rem 0;
            padding: 1rem;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            font-size: 1.1rem;
        }
        .badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            margin: 0.5rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 {{ $siteName }}</h1>
        <div class="info">
            <div class="badge">Domain: {{ $domain }}</div>
            <div class="badge">App: {{ $app }}</div>
            <div class="badge">Theme: {{ $themeColor }}</div>
        </div>
        <p>Powered by <strong>Larabus Framework</strong></p>
        <p><a href="/about" style="color: white; text-decoration: underline;">About Page</a></p>
    </div>
</body>
</html>';

    file_put_contents("{$appPath}/resources/views/{$appName}/welcome.blade.php", $welcomeContent);
    echo "✅ Created welcome.blade.php\n";
    
} else {
    echo "⚠️ App already exists: {$appPath}/\n";
}

echo "\n🎉 Domain {$domainName} created successfully!\n\n";

echo "📋 Next steps:\n";
echo "1. Point your domain to: {$domainName}/ folder\n";
echo "2. For development: cd ../{$domainName} && php -S localhost:8000 router.php\n";
echo "3. Visit: http://localhost:8000 or http://{$domainName}\n\n";

echo "🚍 Happy coding with Larabus!\n";
