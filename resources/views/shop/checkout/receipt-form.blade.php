<x-shop::layouts
    :has-header="true"
    :has-feature="false"
    :has-footer="true"
>
    <x-slot:title>
        @lang('manual_payments::app.shop.title') - #{{ $order->increment_id ?? $order->id }}
    </x-slot>

    <div class="container mt-8 mb-16 px-4 md:px-12 max-w-5xl mx-auto">
        <!-- Top Status Bar -->
        <div class="flex items-center justify-between flex-wrap gap-4 pb-6 border-b border-gray-200">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">
                    @lang('manual_payments::app.shop.title')
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    @lang('manual_payments::app.shop.order_id'): <span class="font-bold text-gray-800">#{{ $order->increment_id ?? $order->id }}</span>
                </p>
            </div>

            <div>
                @php
                    $statusClasses = match($receipt->status) {
                        'pending_submission' => 'bg-amber-100 text-amber-800 border-amber-300',
                        'pending_verification' => 'bg-blue-100 text-blue-800 border-blue-300 animate-pulse',
                        'approved' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                        'rejected' => 'bg-rose-100 text-rose-800 border-rose-300',
                        'expired' => 'bg-gray-100 text-gray-800 border-gray-300',
                        default => 'bg-gray-100 text-gray-800 border-gray-300',
                    };
                @endphp
                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold border {{ $statusClasses }}">
                    {{ $receipt->status_name }}
                </span>
            </div>
        </div>

        <!-- Session Alerts -->
        @if (session('success'))
            <div class="mt-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mt-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Amount and Countdown Banner -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2 rounded-2xl bg-gradient-to-br from-navyBlue to-blue-900 text-white p-6 md:p-8 shadow-lg flex flex-col justify-between">
                <div>
                    <span class="text-xs uppercase font-semibold tracking-wider text-blue-200">
                        @lang('manual_payments::app.shop.total_to_pay')
                    </span>
                    <div class="text-3xl md:text-4xl font-extrabold mt-2 tracking-tight">
                        @if ($receipt->paid_currency === 'VES')
                            Bs. {{ number_format((float) $receipt->paid_amount, 2, ',', '.') }}
                        @elseif ($receipt->paid_currency === 'USDT')
                            {{ number_format((float) $receipt->paid_amount, 2, '.', ',') }} USDT
                        @else
                            $ {{ number_format((float) $receipt->paid_amount, 2, '.', ',') }} USD
                        @endif
                    </div>
                </div>

                @if ($receipt->paid_currency === 'VES' && $receipt->exchange_rate)
                    <p class="text-xs text-blue-200 mt-4 bg-white/10 rounded-lg p-2.5 backdrop-blur-xs">
                        @lang('manual_payments::app.shop.bcv_rate_info', [
                            'rate' => number_format((float) $receipt->exchange_rate, 4, ',', '.')
                        ])
                    </p>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs flex flex-col justify-center items-center text-center">
                <span class="text-xs uppercase font-semibold tracking-wider text-gray-500">
                    @lang('manual_payments::app.shop.countdown_text')
                </span>

                @if ($receipt->expires_at && $receipt->isPendingSubmission())
                    <div id="countdown-timer" class="text-3xl font-mono font-bold text-rose-600 mt-3" data-expire="{{ $receipt->expires_at->toIso8601String() }}">
                        --:--
                    </div>
                    <span class="text-xs text-gray-400 mt-1">minutos : segundos</span>
                @elseif ($receipt->isExpired())
                    <span class="text-rose-600 font-bold mt-2">@lang('manual_payments::app.shop.expired_notice')</span>
                @else
                    <span class="text-emerald-600 font-semibold mt-2">✓ Recibo registrado</span>
                @endif
            </div>
        </div>

        <!-- Conditional Status Notices -->
        @if ($receipt->isPendingVerification())
            <div class="mt-8 p-6 rounded-2xl bg-blue-50 border border-blue-200">
                <h3 class="font-bold text-blue-900 text-lg">@lang('manual_payments::app.shop.under_review_notice')</h3>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-800">
                    <div>
                        <span class="font-semibold">@lang('manual_payments::app.shop.reference_number'):</span>
                        <span class="font-mono font-bold">{{ $receipt->reference_number }}</span>
                    </div>
                    <div>
                        <span class="font-semibold">@lang('manual_payments::app.shop.payment_date'):</span>
                        <span>{{ $receipt->paid_at?->format('d/m/Y H:i') ?? 'N/A' }}</span>
                    </div>
                    @if ($receipt->payer_bank)
                        <div>
                            <span class="font-semibold">@lang('manual_payments::app.shop.origin_bank'):</span>
                            <span>{{ $receipt->payer_bank }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @elseif ($receipt->isApproved())
            <div class="mt-8 p-6 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900">
                <h3 class="font-bold text-lg">@lang('manual_payments::app.shop.approved_notice')</h3>
            </div>
        @elseif ($receipt->isRejected())
            <div class="mt-8 p-6 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900">
                <h3 class="font-bold text-lg">@lang('manual_payments::app.shop.rejected_notice')</h3>
                @if ($receipt->rejection_reason)
                    <p class="text-sm mt-2 font-medium">
                        <strong>@lang('manual_payments::app.shop.rejection_reason_label')</strong> {{ $receipt->rejection_reason }}
                    </p>
                @endif
            </div>
        @endif

        <!-- Bank Accounts Section -->
        @if ($accounts->isNotEmpty())
            <div class="mt-10">
                <h2 class="text-xl font-bold text-gray-900">
                    @lang('manual_payments::app.shop.bank_accounts_title')
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    @lang('manual_payments::app.shop.bank_accounts_desc')
                </p>

                <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach ($accounts as $account)
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs hover:border-blue-400 transition">
                            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                                <span class="font-bold text-gray-900">{{ $account->title }}</span>
                                <span class="text-xs px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 font-medium">
                                    {{ $account->bank_name ?? $account->type_name }}
                                </span>
                            </div>

                            <div class="mt-3 space-y-2 text-sm text-gray-600">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-400">@lang('manual_payments::app.shop.account_holder'):</span>
                                    <span class="font-medium text-gray-800">{{ $account->holder_name }}</span>
                                </div>

                                @if ($account->holder_id)
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-400">@lang('manual_payments::app.shop.holder_id'):</span>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-medium text-gray-800">{{ $account->holder_id }}</span>
                                            <button type="button" onclick="copyText('{{ $account->holder_id }}', this)" class="text-xs text-blue-600 hover:underline">
                                                @lang('manual_payments::app.shop.copy')
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @if ($account->phone)
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-400">@lang('manual_payments::app.shop.phone'):</span>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-medium text-gray-800">{{ $account->phone }}</span>
                                            <button type="button" onclick="copyText('{{ $account->phone }}', this)" class="text-xs text-blue-600 hover:underline">
                                                @lang('manual_payments::app.shop.copy')
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @if ($account->account_number)
                                    <div class="flex items-center justify-between flex-wrap gap-1">
                                        <span class="text-gray-400">@lang('manual_payments::app.shop.account_number'):</span>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-xs font-medium text-gray-800">{{ $account->account_number }}</span>
                                            <button type="button" onclick="copyText('{{ $account->account_number }}', this)" class="text-xs text-blue-600 hover:underline">
                                                @lang('manual_payments::app.shop.copy')
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @if ($account->email)
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-400">@lang('manual_payments::app.shop.email'):</span>
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-800">{{ $account->email }}</span>
                                            <button type="button" onclick="copyText('{{ $account->email }}', this)" class="text-xs text-blue-600 hover:underline">
                                                @lang('manual_payments::app.shop.copy')
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @if ($account->binance_id)
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-400">@lang('manual_payments::app.shop.binance_id'):</span>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-medium text-gray-800">{{ $account->binance_id }}</span>
                                            <button type="button" onclick="copyText('{{ $account->binance_id }}', this)" class="text-xs text-blue-600 hover:underline">
                                                @lang('manual_payments::app.shop.copy')
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if ($account->instructions)
                                <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-500 italic">
                                    {{ $account->instructions }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Submission Form (Visible when submission is allowed) -->
        @if ($receipt->canBeSubmitted())
            <div class="mt-12 rounded-2xl border border-gray-200 bg-white p-6 md:p-8 shadow-xs">
                <h2 class="text-xl font-bold text-gray-900">
                    @lang('manual_payments::app.shop.form_title')
                </h2>

                <form
                    action="{{ route('shop.manual_payments.receipt.submit', $receipt->token) }}"
                    method="POST"
                    enctype="multipart/form-data"
                    class="mt-6 space-y-6"
                >
                    @csrf

                    @if ($accounts->isNotEmpty())
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">
                                @lang('manual_payments::app.shop.destination_account')
                            </label>
                            <select name="payment_account_id" class="mt-1 block w-full rounded-xl border border-gray-300 py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                <option value="">@lang('manual_payments::app.shop.select_account')</option>
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}" {{ old('payment_account_id', $receipt->payment_account_id) == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->title }} ({{ $acc->bank_name ?? $acc->holder_name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Origin Bank -->
                        @if (in_array($receipt->payment_method, ['manual_pagomovil', 'manual_bank_transfer']))
                            <div>
                                <label class="block text-sm font-semibold text-gray-700">
                                    @lang('manual_payments::app.shop.origin_bank')
                                </label>
                                <select name="payer_bank" class="mt-1 block w-full rounded-xl border border-gray-300 py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                    <option value="">@lang('manual_payments::app.shop.select_origin_bank')</option>
                                    @foreach ($banks as $code => $name)
                                        <option value="{{ $name }}" {{ old('payer_bank', $receipt->payer_bank) == $name ? 'selected' : '' }}>
                                            {{ $code }} - {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Reference Number -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">
                                @lang('manual_payments::app.shop.reference_number') <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="reference_number"
                                required
                                value="{{ old('reference_number', $receipt->reference_number) }}"
                                placeholder="@lang('manual_payments::app.shop.reference_placeholder')"
                                class="mt-1 block w-full rounded-xl border border-gray-300 py-2.5 px-3 text-sm font-mono focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            >
                            @error('reference_number')
                                <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Payer Name -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">
                                @lang('manual_payments::app.shop.payer_name')
                            </label>
                            <input
                                type="text"
                                name="payer_name"
                                value="{{ old('payer_name', $receipt->payer_name) }}"
                                class="mt-1 block w-full rounded-xl border border-gray-300 py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            >
                        </div>

                        <!-- Payer ID / Phone -->
                        @if ($receipt->payment_method === 'manual_pagomovil')
                            <div>
                                <label class="block text-sm font-semibold text-gray-700">
                                    @lang('manual_payments::app.shop.payer_phone')
                                </label>
                                <input
                                    type="text"
                                    name="payer_phone"
                                    value="{{ old('payer_phone', $receipt->payer_phone) }}"
                                    placeholder="Ej. 04141234567"
                                    class="mt-1 block w-full rounded-xl border border-gray-300 py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                >
                            </div>
                        @elseif ($receipt->payment_method === 'manual_zelle')
                            <div>
                                <label class="block text-sm font-semibold text-gray-700">
                                    @lang('manual_payments::app.shop.payer_email')
                                </label>
                                <input
                                    type="email"
                                    name="payer_email"
                                    value="{{ old('payer_email', $receipt->payer_email) }}"
                                    class="mt-1 block w-full rounded-xl border border-gray-300 py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                >
                            </div>
                        @endif
                    </div>

                    <!-- Voucher Upload -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">
                            @lang('manual_payments::app.shop.proof_file') <span class="text-rose-500">*</span>
                        </label>
                        <div class="mt-2 flex justify-center rounded-2xl border-2 border-dashed border-gray-300 px-6 pt-5 pb-6 hover:border-blue-400 transition cursor-pointer relative" id="drop-zone">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label class="relative cursor-pointer rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-hidden">
                                        <span>Subir comprobante</span>
                                        <input id="proof-input" name="proof" type="file" required accept="image/jpeg,image/png,image/webp,application/pdf" class="sr-only">
                                    </label>
                                    <p class="pl-1">o arrastra y suelta aquí</p>
                                </div>
                                <p class="text-xs text-gray-400">PNG, JPG, WebP o PDF hasta 5MB</p>
                                <p id="file-name" class="text-xs font-semibold text-blue-700 hidden"></p>
                            </div>
                        </div>
                        @error('proof')
                            <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button
                            type="submit"
                            class="w-full md:w-auto px-8 py-3.5 bg-navyBlue hover:bg-blue-900 text-white font-bold rounded-xl shadow-md transition flex items-center justify-center gap-2 cursor-pointer"
                        >
                            {{ $receipt->isRejected() ? trans('manual_payments::app.shop.resubmit_button') : trans('manual_payments::app.shop.submit_button') }}
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>

    <!-- Copy Helper and Countdown Script -->
    <script>
        function copyText(text, btn) {
            navigator.clipboard.writeText(text).then(function() {
                const originalText = btn.innerText;
                btn.innerText = '¡Copiado!';
                setTimeout(() => { btn.innerText = originalText; }, 2000);
            });
        }

        const timerEl = document.getElementById('countdown-timer');
        if (timerEl) {
            const expireDate = new Date(timerEl.dataset.expire).getTime();
            const interval = setInterval(function() {
                const now = new Date().getTime();
                const distance = expireDate - now;

                if (distance < 0) {
                    clearInterval(interval);
                    timerEl.innerText = '00:00';
                    location.reload();
                    return;
                }

                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                timerEl.innerText = (minutes < 10 ? '0' + minutes : minutes) + ':' + (seconds < 10 ? '0' + seconds : seconds);
            }, 1000);
        }

        const proofInput = document.getElementById('proof-input');
        const fileNameEl = document.getElementById('file-name');
        if (proofInput && fileNameEl) {
            proofInput.addEventListener('change', function(e) {
                if (e.target.files.length > 0) {
                    fileNameEl.innerText = 'Archivo seleccionado: ' + e.target.files[0].name;
                    fileNameEl.classList.remove('hidden');
                }
            });
        }
    </script>
</x-shop::layouts>
