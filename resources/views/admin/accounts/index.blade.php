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
                    Gestiona las cuentas bancarias, Pago Móvil, Zelle y Binance Pay que se muestran al cliente en el checkout.
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

        <x-admin::datagrid :src="route('admin.settings.manual_payment_accounts.index')" />
    </div>
</x-admin::layouts>
