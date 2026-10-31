<x-admin::layouts>
    <x-slot:title>
        @lang('manual_payments::app.admin.accounts.title')
    </x-slot>

    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('manual_payments::app.admin.accounts.title')
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Gestiona las cuentas bancarias, Pago Móvil, Zelle, Binance Pay y canales de WhatsApp que se muestran al cliente en el checkout.
                </p>
            </div>

            @if (! function_exists('bouncer') || bouncer()->hasPermission('settings.manual_payment_accounts.create'))
                <a
                    href="{{ route('admin.settings.manual_payment_accounts.create') }}"
                    class="primary-button flex items-center gap-2"
                >
                    <span class="icon-add text-xl"></span>
                    @lang('manual_payments::app.admin.accounts.btn_create')
                </a>
            @endif
        </div>

        <!-- Info Banner for Payment Methods vs Destination Accounts -->
        <div class="flex items-start gap-3 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-200">
            <span class="icon-information text-xl text-blue-600 dark:text-blue-400 mt-0.5"></span>
            <div class="flex-1">
                <p class="font-semibold text-sm">¿Cómo activar o desactivar las pasarelas en el checkout?</p>
                <p class="mt-1 text-xs text-blue-800 dark:text-blue-300 leading-relaxed">
                    Las cuentas listadas abajo son las receptoras de fondos que verá el cliente. Para activar/desactivar qué métodos de pago aparecen disponibles para compra (incluyendo el driver de <strong>Acordar por WhatsApp</strong> y su redirección automática), ve a
                    @if (\Illuminate\Support\Facades\Route::has('admin.configuration.index'))
                        <a href="{{ route('admin.configuration.index', ['sales', 'payment_methods']) }}" class="font-bold underline hover:text-blue-950 dark:hover:text-white">
                            Configuración &rsaquo; Ventas &rsaquo; Métodos de Pago
                        </a>.
                    @else
                        <strong>Configuración &rsaquo; Ventas &rsaquo; Métodos de Pago</strong>.
                    @endif
                </p>
            </div>
        </div>

        <x-admin::datagrid :src="route('admin.settings.manual_payment_accounts.index')" />
    </div>
</x-admin::layouts>
