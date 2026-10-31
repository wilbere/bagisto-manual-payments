<x-admin::layouts>
    <x-slot:title>
        @lang('manual_payments::app.admin.receipts.title')
    </x-slot>

    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('manual_payments::app.admin.receipts.title')
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Cola de comprobantes recibidos para auditar, verificar en el banco y facturar pedidos automáticamente.
                </p>
            </div>
        </div>

        <x-admin::datagrid :src="route('admin.sales.manual_payments.index')" />
    </div>
</x-admin::layouts>
