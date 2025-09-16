# Laravel Executed By

[![Latest Version on Packagist](https://img.shields.io/packagist/v/develupers/laravel-executed-by.svg?style=flat-square)](https://packagist.org/packages/develupers/laravel-executed-by)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/develupers/laravel-executed-by/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/develupers/laravel-executed-by/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/develupers/laravel-executed-by/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/develupers/laravel-executed-by/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/develupers/laravel-executed-by.svg?style=flat-square)](https://packagist.org/packages/develupers/laravel-executed-by)

A Laravel package to detect how your application code is being executed. This package analyzes PHP's global variables to determine if your code is running via Artisan commands, Composer scripts, Queue workers, Schedulers, or other execution contexts.

## Use Cases

- **Conditional Logic**: Execute different code paths based on the execution context
- **Debugging**: Identify how your application code was triggered
- **Logging**: Add context-aware information to your logs
- **Performance**: Skip unnecessary operations in certain contexts (e.g., skip cache warming during composer install)
- **Security**: Restrict certain operations to specific execution contexts

## Requirements

- PHP 8.2 or higher
- Laravel 11.x, or 12.x

## Installation

You can install the package via composer:

```bash
composer require develupers/laravel-executed-by
```

The package will automatically register its service provider and facade.

## Usage

The package provides a `Executed` facade with multiple static methods to detect different execution contexts.

### Basic Usage

```php
use Develupers\Executed\Facades\Executed;

// Check if running via Artisan
if (Executed::byArtisanCommand()) {
    // Code is being executed by an Artisan command
}

// Check if running via Composer
if (Executed::byComposerCommand()) {
    // Code is being executed during a Composer operation
}

// Check if running via Queue
if (Executed::byQueueCommand()) {
    // Code is being executed by a queue worker
}
```

### Available Detection Methods

#### Standard PHP Commands
```php
// Check if running via standard PHP command (e.g., php -r "code")
if (Executed::byStandardCommand()) {
    // Running via standard PHP input
}
```

#### Vendor Scripts
```php
// Check if running via a vendor script
if (Executed::byVendorScript()) {
    // Script is located in vendor/ directory
}
```

#### Composer Commands
```php
// Check if running during Composer operations
if (Executed::byComposerCommand()) {
    // Running during composer install/update/etc.
}
```

#### Artisan Commands
```php
// Check if running any Artisan command
if (Executed::byArtisanCommand()) {
    // Running via php artisan
}

// Get the specific Artisan command being run
$command = Executed::getArtisanCommand(); // Returns e.g., "migrate", "cache:clear"

// Check for a specific Artisan command
if (Executed::checkArtisanCommand('migrate')) {
    // Running any migrate command (migrate, migrate:fresh, etc.)
}

// Check for exact Artisan command match
if (Executed::checkArtisanCommand('migrate', 'fresh')) {
    // Running specifically php artisan migrate:fresh
}
```

#### Package Commands
```php
// Check if running package discovery or related commands
if (Executed::byPackageCommand()) {
    // Running package:discover or similar
}

// Check for specific package command
if (Executed::byPackageCommand('discover')) {
    // Running package:discover
}
```

#### Cache Commands
```php
// Check if running any cache-related command
if (Executed::byCacheCommand()) {
    // Running cache:clear, config:cache, route:cache, view:cache, optimize, etc.
}
```

#### Scheduler Commands
```php
// Check if running via Laravel Scheduler
if (Executed::bySchedulerCommand()) {
    // Running scheduled tasks
}

// Check for specific scheduler command
if (Executed::bySchedulerCommand('run')) {
    // Running schedule:run
}
```

#### Horizon Commands
```php
// Check if running Laravel Horizon
if (Executed::byHorizonCommand()) {
    // Running Horizon supervisor or workers
}

// Check for specific Horizon command
if (Executed::byHorizonCommand('work')) {
    // Running horizon:work
}
```

#### Queue Commands
```php
// Check if running queue workers
if (Executed::byQueueCommand()) {
    // Running queue:work, queue:listen, etc.
}

// Check for specific queue command
if (Executed::byQueueCommand('work')) {
    // Running queue:work
}
```

#### Mail Commands
```php
// Check if running mail-related commands
if (Executed::byMailCommand()) {
    // Running mail commands
}
```

## Real-World Examples

### Skip Heavy Operations During Package Discovery

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Develupers\Executed\Facades\Executed;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Skip cache warming during composer operations
        if (!Executed::byComposerCommand() && !Executed::byPackageCommand()) {
            $this->warmCache();
        }
    }

    private function warmCache()
    {
        // Heavy cache warming operations
    }
}
```

### Context-Aware Logging

```php
use Illuminate\Support\Facades\Log;
use Develupers\Executed\Facades\Executed;

class OrderService
{
    public function processOrder($order)
    {
        $context = [
            'order_id' => $order->id,
            'execution_context' => $this->getExecutionContext(),
        ];

        Log::info('Processing order', $context);

        // Process the order...
    }

    private function getExecutionContext(): string
    {
        if (Executed::byQueueCommand()) {
            return 'queue:' . Executed::getArtisanCommand();
        }

        if (Executed::bySchedulerCommand()) {
            return 'scheduler';
        }

        if (Executed::byArtisanCommand()) {
            return 'artisan:' . Executed::getArtisanCommand();
        }

        return 'web';
    }
}
```

### Conditional Database Seeding

```php
use Illuminate\Database\Seeder;
use Develupers\Executed\Facades\Executed;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Only seed test data during specific migrate command
        if (Executed::checkArtisanCommand('migrate', 'fresh')) {
            $this->call(TestDataSeeder::class);
        }

        // Always seed core data
        $this->call(CoreDataSeeder::class);
    }
}
```

### Prevent Accidental Command Execution

```php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Develupers\Executed\Facades\Executed;

class DangerousCommand extends Command
{
    protected $signature = 'app:dangerous-operation';

    public function handle()
    {
        // Ensure this isn't being run by queue or scheduler
        if (Executed::byQueueCommand() || Executed::bySchedulerCommand()) {
            $this->error('This command cannot be run via queue or scheduler!');
            return 1;
        }

        if (!$this->confirm('Are you sure you want to proceed?')) {
            return 0;
        }

        // Perform dangerous operation...
    }
}
```

### Optimize API Client Initialization

```php
namespace App\Services;

use Develupers\Executed\Facades\Executed;

class ExternalApiService
{
    private $client;

    public function __construct()
    {
        // Skip expensive client initialization during cache commands
        if (!Executed::byCacheCommand()) {
            $this->initializeClient();
        }
    }

    private function initializeClient()
    {
        // Expensive API client setup
        $this->client = new ApiClient([
            'timeout' => 30,
            'verify_ssl' => true,
            // ... more configuration
        ]);
    }

    public function fetch($endpoint)
    {
        if (!$this->client) {
            $this->initializeClient();
        }

        return $this->client->get($endpoint);
    }
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Omar Robinson](https://github.com/OmarRobinson)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
