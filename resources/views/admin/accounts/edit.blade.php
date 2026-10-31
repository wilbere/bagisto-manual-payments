<x-admin::layouts>
    <x-slot:title>
        @lang('manual_payments::app.admin.accounts.edit_title') - {{ $account->title }}
    </x-slot>

    <!-- Info Banner for Payment Methods vs Destination Accounts -->
    <div class="mb-4 flex items-start gap-3 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-200">
        <span class="icon-information text-xl text-blue-600 dark:text-blue-400 mt-0.5"></span>
        <div class="flex-1">
            <p class="font-semibold text-sm">¿Dónde se activan los métodos de pago (drivers) en el checkout?</p>
            <p class="mt-1 text-xs text-blue-800 dark:text-blue-300 leading-relaxed">
                Este formulario gestiona las <strong>cuentas de destino</strong> que el cliente verá durante el pago. Para activar o desactivar qué pasarelas aparecen en el checkout de la tienda (incluyendo el driver de <strong>Acordar por WhatsApp</strong> y su número predeterminado), ve a
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

    <form
        action="{{ route('admin.settings.manual_payment_accounts.update', $account->id) }}"
        method="POST"
        enctype="multipart/form-data"
        class="flex flex-col gap-6"
    >
        @csrf
        @method('PUT')

        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('manual_payments::app.admin.accounts.edit_title'): {{ $account->title }}
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
                        <option value="pagomovil" {{ old('type', $account->type) == 'pagomovil' ? 'selected' : '' }}>Pago Móvil (VES)</option>
                        <option value="bank_transfer" {{ old('type', $account->type) == 'bank_transfer' ? 'selected' : '' }}>Transferencia Bancaria Nacional (VES)</option>
                        <option value="zelle" {{ old('type', $account->type) == 'zelle' ? 'selected' : '' }}>Zelle (USD)</option>
                        <option value="binance" {{ old('type', $account->type) == 'binance' ? 'selected' : '' }}>Binance Pay (USDT)</option>
                        <option value="whatsapp" {{ old('type', $account->type) == 'whatsapp' ? 'selected' : '' }}>WhatsApp (Atención / Coordinación)</option>
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
                        value="{{ old('title', $account->title) }}"
                        class="rounded-md border border-gray-300 py-2 px-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                </div>

                <!-- Bank Selection and Bank Code -->
                <div id="group-bank-details" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Bank Selection -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                            @lang('manual_payments::app.admin.accounts.fields.bank_name')
                        </label>
                        <input
                            type="text"
                            name="bank_name"
                            value="{{ old('bank_name', $account->bank_name) }}"
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
                            value="{{ old('bank_code', $account->bank_code) }}"
                            class="rounded-md border border-gray-300 py-2 px-3 text-sm font-mono dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                    </div>
                </div>

                <!-- Account Number (20 digits) -->
                <div id="group-account-number" class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                        @lang('manual_payments::app.admin.accounts.fields.account_number')
                    </label>
                    <input
                        type="text"
                        name="account_number"
                        maxlength="50"
                        value="{{ old('account_number', $account->account_number) }}"
                        class="rounded-md border border-gray-300 py-2 px-3 text-sm font-mono dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                </div>

                <!-- Holder Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Holder Name -->
                    <div id="group-holder-name" class="flex flex-col gap-1.5">
                        <label id="label-holder-name" class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                            @lang('manual_payments::app.admin.accounts.fields.holder_name') <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="holder_name"
                            id="input-holder-name"
                            required
                            value="{{ old('holder_name', $account->holder_name) }}"
                            class="rounded-md border border-gray-300 py-2 px-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                    </div>

                    <!-- Holder ID (Cédula o RIF) -->
                    <div id="group-holder-id" class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                            @lang('manual_payments::app.admin.accounts.fields.holder_id')
                        </label>
                        <input
                            type="text"
                            name="holder_id"
                            value="{{ old('holder_id', $account->holder_id) }}"
                            class="rounded-md border border-gray-300 py-2 px-3 text-sm font-mono dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                    </div>
                </div>

                <!-- Phone and Email -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Phone -->
                    <div id="group-phone" class="flex flex-col gap-1.5">
                        <label id="label-phone" class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                            @lang('manual_payments::app.admin.accounts.fields.phone')
                        </label>
                        <input
                            type="text"
                            name="phone"
                            id="input-phone"
                            value="{{ old('phone', $account->phone) }}"
                            class="rounded-md border border-gray-300 py-2 px-3 text-sm font-mono dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                    </div>

                    <!-- Email -->
                    <div id="group-email" class="flex flex-col gap-1.5">
                        <label id="label-email" class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                            @lang('manual_payments::app.admin.accounts.fields.email')
                        </label>
                        <input
                            type="email"
                            name="email"
                            id="input-email"
                            value="{{ old('email', $account->email) }}"
                            class="rounded-md border border-gray-300 py-2 px-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                    </div>
                </div>

                <!-- Binance ID -->
                <div id="group-binance-id" class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                        @lang('manual_payments::app.admin.accounts.fields.binance_id')
                    </label>
                    <input
                        type="text"
                        name="binance_id"
                        value="{{ old('binance_id', $account->binance_id) }}"
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
                        class="rounded-md border border-gray-300 py-2 px-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >{{ old('instructions', $account->instructions) }}</textarea>
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
                        <option value="1" {{ old('status', $account->status) ? 'selected' : '' }}>@lang('manual_payments::app.admin.accounts.active')</option>
                        <option value="0" {{ ! old('status', $account->status) ? 'selected' : '' }}>@lang('manual_payments::app.admin.accounts.inactive')</option>
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
                        value="{{ old('sort_order', $account->sort_order) }}"
                        class="rounded-md border border-gray-300 py-2 px-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                </div>

                <!-- Current QR Image Preview -->
                @if ($account->qr_image_path)
                    <div id="group-qr-preview" class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Código QR Actual
                        </label>
                        <img
                            src="{{ Storage::disk('public')->url($account->qr_image_path) }}"
                            alt="QR"
                            class="w-32 h-32 object-contain border border-gray-200 rounded-lg p-1 bg-white"
                        >
                    </div>
                @endif

                <!-- QR Code Upload -->
                <div id="group-qr-code" class="flex flex-col gap-1.5">
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const typeSelect = document.getElementById('account-type');
            if (!typeSelect) return;

            const groupBankDetails = document.getElementById('group-bank-details');
            const groupAccountNumber = document.getElementById('group-account-number');
            const groupHolderId = document.getElementById('group-holder-id');
            const groupHolderName = document.getElementById('group-holder-name');
            const groupPhone = document.getElementById('group-phone');
            const groupEmail = document.getElementById('group-email');
            const groupBinanceId = document.getElementById('group-binance-id');
            const groupQrCode = document.getElementById('group-qr-code');
            const groupQrPreview = document.getElementById('group-qr-preview');

            const labelHolderName = document.getElementById('label-holder-name');
            const labelPhone = document.getElementById('label-phone');
            const labelEmail = document.getElementById('label-email');

            const inputHolderName = document.getElementById('input-holder-name');
            const inputPhone = document.getElementById('input-phone');
            const inputEmail = document.getElementById('input-email');

            function toggle(el, show) {
                if (!el) return;
                if (show) {
                    el.classList.remove('hidden');
                } else {
                    el.classList.add('hidden');
                }
            }

            function updateFields() {
                const type = typeSelect.value;

                // Bank details (Bank name, bank code)
                const showBankDetails = (type === 'pagomovil' || type === 'bank_transfer');
                toggle(groupBankDetails, showBankDetails);

                // Account number (20 digits - only for bank transfer)
                const showAccountNumber = (type === 'bank_transfer');
                toggle(groupAccountNumber, showAccountNumber);

                // Holder ID (Cédula/RIF - Pago Móvil & Bank Transfer)
                const showHolderId = (type === 'pagomovil' || type === 'bank_transfer');
                toggle(groupHolderId, showHolderId);
                if (groupHolderName) {
                    if (showHolderId) {
                        groupHolderName.classList.remove('md:col-span-2');
                    } else {
                        groupHolderName.classList.add('md:col-span-2');
                    }
                }

                // Phone
                const showPhone = (type === 'pagomovil' || type === 'zelle' || type === 'whatsapp');
                toggle(groupPhone, showPhone);

                // Email
                const showEmail = (type === 'zelle' || type === 'binance');
                toggle(groupEmail, showEmail);

                // Grid column span for Phone & Email
                if (groupPhone && groupEmail) {
                    if (showPhone && !showEmail) {
                        groupPhone.classList.add('md:col-span-2');
                    } else {
                        groupPhone.classList.remove('md:col-span-2');
                    }

                    if (showEmail && !showPhone) {
                        groupEmail.classList.add('md:col-span-2');
                    } else {
                        groupEmail.classList.remove('md:col-span-2');
                    }
                }

                // Binance ID
                const showBinance = (type === 'binance');
                toggle(groupBinanceId, showBinance);

                // QR Code (optional for Pago Móvil, Zelle, Binance, WhatsApp; not typical for Bank Transfer)
                const showQr = (type !== 'bank_transfer');
                toggle(groupQrCode, showQr);
                toggle(groupQrPreview, showQr);

                // Contextual labels & placeholders
                if (labelHolderName) {
                    if (type === 'whatsapp') {
                        labelHolderName.innerHTML = 'Nombre del Asesor / Contacto <span class="text-rose-500">*</span>';
                        if (inputHolderName) inputHolderName.placeholder = 'Ej. Soporte Ventas / Carlos Pérez';
                    } else if (type === 'binance') {
                        labelHolderName.innerHTML = 'Nombre o Alias en Binance <span class="text-rose-500">*</span>';
                        if (inputHolderName) inputHolderName.placeholder = 'Ej. MiTienda Oficial';
                    } else if (type === 'zelle') {
                        labelHolderName.innerHTML = 'Nombre del Titular Zelle <span class="text-rose-500">*</span>';
                        if (inputHolderName) inputHolderName.placeholder = 'Nombre registrado en la cuenta Zelle';
                    } else {
                        labelHolderName.innerHTML = 'Nombre del Titular <span class="text-rose-500">*</span>';
                        if (inputHolderName) inputHolderName.placeholder = 'Nombre del Titular de la cuenta';
                    }
                }

                if (labelPhone && inputPhone) {
                    if (type === 'whatsapp') {
                        labelPhone.textContent = 'Número de WhatsApp con código de país';
                        inputPhone.placeholder = 'Ej. +584141234567';
                    } else if (type === 'pagomovil') {
                        labelPhone.textContent = 'Número de Teléfono Pago Móvil';
                        inputPhone.placeholder = 'Ej. 04141234567';
                    } else if (type === 'zelle') {
                        labelPhone.textContent = 'Número de Teléfono Zelle (si aplica)';
                        inputPhone.placeholder = 'Ej. +1 555 123 4567';
                    }
                }

                if (labelEmail && inputEmail) {
                    if (type === 'binance') {
                        labelEmail.textContent = 'Paymail o Correo de Binance (opcional)';
                        inputEmail.placeholder = 'Ej. binance@mitienda.com';
                    } else {
                        labelEmail.textContent = 'Correo Electrónico Zelle';
                        inputEmail.placeholder = 'Ej. pagos@mitienda.com';
                    }
                }
            }

            typeSelect.addEventListener('change', updateFields);
            updateFields();
        });
    </script>
</x-admin::layouts>
