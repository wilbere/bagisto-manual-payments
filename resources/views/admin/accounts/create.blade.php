<x-admin::layouts>
    <x-slot:title>
        @lang('manual_payments::app.admin.accounts.create_title')
    </x-slot>

    <form
        action="{{ route('admin.settings.manual_payment_accounts.store') }}"
        method="POST"
        enctype="multipart/form-data"
        class="flex flex-col gap-6"
    >
        @csrf

        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('manual_payments::app.admin.accounts.create_title')
            </p>

            <div class="flex items-center gap-2.5">
                <a
                    href="{{ route('admin.settings.manual_payment_accounts.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:hover:bg-gray-800"
                >
                    @lang('manual_payments::app.admin.actions.cancel')
                </a>

                <button type="submit" class="primary-button">
                    @lang('manual_payments::app.admin.actions.save')
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Panel: Account Details -->
            <div class="md:col-span-2 flex flex-col gap-5 rounded-lg border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <!-- Channel Type -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                        @lang('manual_payments::app.admin.accounts.fields.type') <span class="text-rose-500">*</span>
                    </label>
                    <select
                        name="type"
                        id="account-type"
                        required
                        class="rounded-md border border-gray-300 py-2 px-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="pagomovil" {{ old('type') == 'pagomovil' ? 'selected' : '' }}>Pago Móvil (VES)</option>
                        <option value="bank_transfer" {{ old('type') == 'bank_transfer' ? 'selected' : '' }}>Transferencia Bancaria Nacional (VES)</option>
                        <option value="zelle" {{ old('type') == 'zelle' ? 'selected' : '' }}>Zelle (USD)</option>
                        <option value="binance" {{ old('type') == 'binance' ? 'selected' : '' }}>Binance Pay (USDT)</option>
                    </select>
                </div>

                <!-- Title / Label -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                        @lang('manual_payments::app.admin.accounts.fields.title') <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="title"
                        required
                        value="{{ old('title') }}"
                        placeholder="Ej. Banesco Pago Móvil Principal o Zelle Comercial"
                        class="rounded-md border border-gray-300 py-2 px-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Bank Selection -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                            @lang('manual_payments::app.admin.accounts.fields.bank_name')
                        </label>
                        <input
                            type="text"
                            name="bank_name"
                            value="{{ old('bank_name') }}"
                            placeholder="Ej. Banesco, Mercantil, Chase..."
                            class="rounded-md border border-gray-300 py-2 px-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                    </div>

                    <!-- Bank Code -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                            @lang('manual_payments::app.admin.accounts.fields.bank_code')
                        </label>
                        <input
                            type="text"
                            name="bank_code"
                            maxlength="4"
                            value="{{ old('bank_code') }}"
                            placeholder="Ej. 0134"
                            class="rounded-md border border-gray-300 py-2 px-3 text-sm font-mono dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                    </div>
                </div>

                <!-- Account Number (20 digits) -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                        @lang('manual_payments::app.admin.accounts.fields.account_number')
                    </label>
                    <input
                        type="text"
                        name="account_number"
                        maxlength="50"
                        value="{{ old('account_number') }}"
                        placeholder="Ej. 01340000000000000000 (20 dígitos)"
                        class="rounded-md border border-gray-300 py-2 px-3 text-sm font-mono dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Holder Name -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                            @lang('manual_payments::app.admin.accounts.fields.holder_name') <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="holder_name"
                            required
                            value="{{ old('holder_name') }}"
                            placeholder="Nombre del Titular de la cuenta"
                            class="rounded-md border border-gray-300 py-2 px-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                    </div>

                    <!-- Holder ID (Cédula o RIF) -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                            @lang('manual_payments::app.admin.accounts.fields.holder_id')
                        </label>
                        <input
                            type="text"
                            name="holder_id"
                            value="{{ old('holder_id') }}"
                            placeholder="Ej. V-12345678 o J-12345678-0"
                            class="rounded-md border border-gray-300 py-2 px-3 text-sm font-mono dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Phone for Pago Móvil -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                            @lang('manual_payments::app.admin.accounts.fields.phone')
                        </label>
                        <input
                            type="text"
                            name="phone"
                            value="{{ old('phone') }}"
                            placeholder="Ej. 04141234567"
                            class="rounded-md border border-gray-300 py-2 px-3 text-sm font-mono dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                    </div>

                    <!-- Email for Zelle -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                            @lang('manual_payments::app.admin.accounts.fields.email')
                        </label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Ej. pagos@mitienda.com"
                            class="rounded-md border border-gray-300 py-2 px-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                    </div>
                </div>

                <!-- Binance ID -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                        @lang('manual_payments::app.admin.accounts.fields.binance_id')
                    </label>
                    <input
                        type="text"
                        name="binance_id"
                        value="{{ old('binance_id') }}"
                        placeholder="Ej. 12345678 o Paymail"
                        class="rounded-md border border-gray-300 py-2 px-3 text-sm font-mono dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                </div>

                <!-- Instructions -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                        @lang('manual_payments::app.admin.accounts.fields.instructions')
                    </label>
                    <textarea
                        name="instructions"
                        rows="3"
                        placeholder="Instrucciones adicionales para el comprador (opcional)"
                        class="rounded-md border border-gray-300 py-2 px-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >{{ old('instructions') }}</textarea>
                </div>
            </div>

            <!-- Right Panel: Settings & QR -->
            <div class="flex flex-col gap-5 rounded-lg border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <!-- Status -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                        @lang('manual_payments::app.admin.accounts.fields.status')
                    </label>
                    <select
                        name="status"
                        class="rounded-md border border-gray-300 py-2 px-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>@lang('manual_payments::app.admin.accounts.active')</option>
                        <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>@lang('manual_payments::app.admin.accounts.inactive')</option>
                    </select>
                </div>

                <!-- Sort Order -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                        @lang('manual_payments::app.admin.accounts.fields.sort_order')
                    </label>
                    <input
                        type="number"
                        name="sort_order"
                        value="{{ old('sort_order', 0) }}"
                        class="rounded-md border border-gray-300 py-2 px-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                </div>

                <!-- QR Code Upload -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                        @lang('manual_payments::app.admin.accounts.fields.qr_image')
                    </label>
                    <input
                        type="file"
                        name="qr_image"
                        accept="image/jpeg,image/png,image/webp"
                        class="rounded-md border border-gray-300 py-2 px-3 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                    >
                </div>
            </div>
        </div>
    </form>
</x-admin::layouts>
