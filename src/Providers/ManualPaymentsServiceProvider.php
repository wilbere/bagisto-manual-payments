<?php

namespace Wilbere\BagistoManualPayments\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Wilbere\BagistoManualPayments\Console\ExpirePendingOrdersCommand;
use Wilbere\BagistoManualPayments\Console\InstallCommand;
use Wilbere\BagistoManualPayments\Services\AmountResolverService;
use Wilbere\BagistoManualPayments\Services\ReceiptReconciliationService;

class ManualPaymentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/manual-payments.php', 'manual-payments');
        $this->mergeConfigFrom(__DIR__.'/../../config/payment-methods.php', 'payment_methods');
        $this->mergeConfigFrom(__DIR__.'/../../config/menu.php', 'menu.admin');
        $this->mergeConfigFrom(__DIR__.'/../../config/acl.php', 'acl');

        $this->app->singleton(AmountResolverService::class);
        $this->app->singleton(ReceiptReconciliationService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'manual_payments');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'manual_payments');

        $this->loadRoutesFrom(__DIR__.'/../../routes/admin.php');
        $this->loadRoutesFrom(__DIR__.'/../../routes/shop.php');

        $this->publishes([
            __DIR__.'/../../config/manual-payments.php' => config_path('manual-payments.php'),
            __DIR__.'/../../resources/views' => resource_path('views/vendor/manual_payments'),
            __DIR__.'/../../resources/lang' => resource_path('lang/vendor/manual_payments'),
        ], 'manual-payments-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                ExpirePendingOrdersCommand::class,
            ]);
        }

        $this->registerSchedule();
    }

    protected function registerSchedule(): void
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('manual-payments:expire')
                ->everyFifteenMinutes()
                ->withoutOverlapping()
                ->onOneServer();
        });
    }
}
