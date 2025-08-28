# 🗄️ Multi-Database Management in Larabus

Larabus now supports **per-app database configuration**, allowing each application to have its own isolated database connections instead of sharing a single database.

## 🎯 Overview

Each domain can specify its own database connections in the `config.php` file. The `LarabusServiceProvider` automatically loads these configurations and sets the appropriate default connection for each app.

## 📁 Configuration Structure

### Domain Config Format

Each domain's `config.php` now supports database configuration:

```php
<?php

return [
    'app_name' => 'myapp',
    'site_title' => 'My Application',
    'view_template' => 'myapp.welcome',
    'theme_color' => '#3b82f6',
    
    // Database configuration for this app
    'db_default' => 'myapp_sqlite', // Default connection name
    'db_connections' => [
        'myapp_mysql' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'port' => '3306',
            'database' => 'myapp_database',
            'username' => 'myapp_user',
            'password' => 'secure_password',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ],
        'myapp_sqlite' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../larabus/database/myapp.sqlite',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ],
        'myapp_pgsql' => [
            'driver' => 'pgsql',
            'host' => 'localhost',
            'port' => '5432',
            'database' => 'myapp_postgres',
            'username' => 'postgres',
            'password' => 'password',
            'charset' => 'utf8',
            'prefix' => '',
        ],
    ]
];
```

## 🔧 How It Works

### 1. Configuration Loading
- Domain's `index.php` loads `config.php`
- Database connections are converted to environment variables
- Format: `DOMAIN_DB_CONNECTIONS_{CONNECTION}_{PARAMETER}`

### 2. Service Provider Processing
- `LarabusServiceProvider` reads environment variables
- Adds app-specific connections to Laravel's database config
- Sets the default connection for the app

### 3. Runtime Usage
- Models automatically use the app's default connection
- You can explicitly specify connections when needed

## 💻 Usage Examples

### Basic Model Usage
```php
// Uses app's default connection automatically
$users = User::all();

// Create new record on default connection
User::create(['name' => 'John', 'email' => 'john@example.com']);
```

### Explicit Connection Usage
```php
// Use specific connection
$users = User::on('myapp_mysql')->get();

// Query different connections
$mysqlUsers = User::on('myapp_mysql')->count();
$sqliteUsers = User::on('myapp_sqlite')->count();
```

### Migration with Specific Connection
```php
// In migration file
Schema::connection('myapp_mysql')->create('custom_table', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});
```

### Raw Database Queries
```php
// Use app's default connection
$results = DB::select('SELECT * FROM users WHERE active = ?', [1]);

// Use specific connection
$results = DB::connection('myapp_mysql')->select('SELECT * FROM users');
```

## 🏗️ Setting Up New Apps

### 1. Create Domain with Database Config
```bash
php larabus/create-domain.php mysite.com mysite "My Site" "#ff6600"
```

This creates a domain with a template database configuration.

### 2. Customize Database Settings
Edit `mysite.com/config.php` to specify your database connections:

```php
'db_default' => 'mysite_production',
'db_connections' => [
    'mysite_production' => [
        'driver' => 'mysql',
        'host' => 'prod-db.example.com',
        'database' => 'mysite_prod',
        'username' => 'prod_user',
        'password' => env('DB_PASSWORD'),
        // ... other settings
    ],
    'mysite_staging' => [
        'driver' => 'mysql',
        'host' => 'staging-db.example.com',
        'database' => 'mysite_staging',
        // ... other settings
    ]
]
```

### 3. Create Database Files
For SQLite connections, ensure the database file exists:
```bash
touch larabus/database/mysite.sqlite
```

### 4. Run Migrations
```bash
cd larabus
php artisan migrate --database=mysite_production
```

## 🔍 Testing Database Configuration

### Test Endpoint
Visit `/db-test` on any domain to see the database configuration:

```json
{
    "app": "site1",
    "default_connection": "site1_sqlite",
    "available_connections": [
        "sqlite",
        "mysql", 
        "site1_sqlite",
        "site1_mysql"
    ],
    "current_db_config": {
        "driver": "sqlite",
        "database": "/path/to/larabus/database/site1.sqlite"
    }
}
```

### Test Script
Run the multi-database test script:
```bash
cd larabus
php test-multi-db.php
```

## 🌟 Benefits

### 1. **Data Isolation**
- Each app has its own database(s)
- No data mixing between applications
- Independent schema management

### 2. **Flexible Database Types**
- SQLite for development/simple apps
- MySQL/PostgreSQL for production
- Different databases for different apps

### 3. **Scalability**
- Distribute apps across different database servers
- Independent database maintenance and backups
- App-specific performance tuning

### 4. **Security**
- Different database credentials per app
- Isolated access controls
- Reduced attack surface

## ⚠️ Important Notes

### Environment Variables
The domain `index.php` sets environment variables in this format:
```
DOMAIN_DB_CONNECTIONS_MYAPP_MYSQL_HOST=localhost
DOMAIN_DB_CONNECTIONS_MYAPP_MYSQL_DATABASE=myapp_db
DOMAIN_DB_DEFAULT=myapp_mysql
```

### Connection Naming
- Use descriptive connection names: `{app}_{type}`
- Examples: `site1_mysql`, `blog_sqlite`, `api_pgsql`

### Database Files (SQLite)
- Place SQLite files in `larabus/database/` directory
- Use absolute paths in configuration
- Ensure proper file permissions (644)

### Migrations
- Run migrations per connection when needed
- Use `--database=connection_name` parameter
- Consider separate migration files per app

## 🚀 Advanced Usage

### Dynamic Connection Switching
```php
// Switch connections based on logic
$connection = request()->get('use_archive') ? 'myapp_archive' : 'myapp_live';
$data = User::on($connection)->get();
```

### Multiple Database Types
```php
// Read from MySQL, cache in Redis, log to PostgreSQL
$user = User::on('myapp_mysql')->find(1);
Cache::store('redis')->put('user_1', $user);
Log::connection('myapp_pgsql')->info('User accessed', ['id' => 1]);
```

### Cross-Database Operations
```php
// Copy data between databases
$prodUsers = User::on('myapp_production')->get();
foreach ($prodUsers as $user) {
    User::on('myapp_staging')->create($user->toArray());
}
```

---

🎉 **Each app now has complete database independence while sharing the same Laravel framework!**
