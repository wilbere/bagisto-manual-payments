<?php

namespace Wilbere\BagistoManualPayments\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
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
        $this->mergeConfigFrom(__DIR__.'/../../config/system.php', 'core');
        $this->mergeConfigFrom(__DIR__.'/../../config/menu.php', 'menu.admin');
        $this->mergeConfigFrom(__DIR__.'/../../config/acl.php', 'acl');

        $this->app->singleton(AmountResolverService::class);
        $this->app->singleton(ReceiptReconciliationService::class);
        $this->app->singleton(\Wilbere\BagistoManualPayments\Services\WhatsAppService::class);
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

        $this->registerViewRenderEvents();

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                ExpirePendingOrdersCommand::class,
            ]);
        }

        $this->registerSchedule();
    }

    protected function registerViewRenderEvents(): void
    {
        // Enlace para subir comprobante en la vista de pedido de la cuenta del cliente
        Event::listen('bagisto.shop.customers.account.orders.view.before', static function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('manual_payments::shop.account.order-payment-banner');
        });

        // Banner en caso de caer en la vista genérica de checkout exitoso
        Event::listen('bagisto.shop.checkout.success.continue-shopping.before', static function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('manual_payments::shop.checkout.success-banner');
        });
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
