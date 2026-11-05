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
            <div class="md:col-span-2 rounded-2xl text-white p-6 md:p-8 shadow-lg flex flex-col justify-between bg-[#18181b]" style="background: linear-gradient(135deg, #18181b 0%, #27272a 100%) !important; color: #ffffff !important;">
                <div>
                    <span class="text-xs uppercase font-semibold tracking-wider text-[#ded5c3]" style="color: #ded5c3 !important;">
                        @lang('manual_payments::app.shop.total_to_pay')
                    </span>
                    <div class="text-3xl md:text-4xl font-extrabold mt-2 tracking-tight text-white" style="color: #ffffff !important;">
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
                    <p class="text-xs text-[#ded5c3] mt-4 bg-white/10 rounded-lg p-2.5 backdrop-blur-xs" style="color: #ded5c3 !important;">
                        @lang('manual_payments::app.shop.bcv_rate_info', [
                            'rate' => number_format((float) $receipt->exchange_rate, 4, ',', '.')
                        ])
                    </p>
                @endif
            </div>

            <div class="rounded-2xl border border-[#ede8dc] bg-[#fdfbf7] p-6 shadow-xs flex flex-col justify-center items-center text-center">
                <span class="text-xs uppercase font-semibold tracking-wider text-[#71717a]">
                    @lang('manual_payments::app.shop.countdown_text')
                </span>

                @if ($receipt->expires_at && $receipt->isPendingSubmission())
                    <div id="countdown-timer" class="text-3xl font-mono font-bold text-rose-600 mt-3" data-expire="{{ $receipt->expires_at->toIso8601String() }}">
                        --:--
                    </div>
                    <span class="text-xs text-[#a1a1aa] mt-1">minutos : segundos</span>
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

        <!-- WhatsApp Dedicated Card (When Payment Method is manual_whatsapp) -->
        @if ($receipt->payment_method === 'manual_whatsapp' && ! empty($whatsAppUrl))
            <div class="mt-8 rounded-2xl border-2 border-emerald-500 bg-emerald-50/70 p-6 md:p-8 shadow-md">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-emerald-600 text-white rounded-2xl shadow-md">
                            <svg class="w-8 h-8 fill-current" viewBox="0 0 24 24">
                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.058.376-.058c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.158.57 4.184 1.571 5.938l-1.668 6.096 6.262-1.642c1.704.929 3.654 1.458 5.735 1.458 6.627 0 12-5.373 12-12s-5.373-12-12-12z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-extrabold text-emerald-950">
                                @lang('manual_payments::app.shop.whatsapp_banner_title')
                            </h3>
                            <p class="text-sm text-emerald-800 mt-1 max-w-xl">
                                @lang('manual_payments::app.shop.whatsapp_banner_desc')
                            </p>
                        </div>
                    </div>

                    <a
                        href="{{ $whatsAppUrl }}"
                        target="_blank"
                        id="btn-whatsapp-action"
                        class="w-full md:w-auto px-8 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-xl shadow-md transition flex items-center justify-center gap-2 cursor-pointer text-base"
                    >
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z" />
                        </svg>
                        @lang('manual_payments::app.shop.btn_open_whatsapp')
                    </a>
                </div>
            </div>
        @elseif (! empty($whatsAppUrl))
            <!-- Support banner for other payment methods -->
            <div class="mt-8 rounded-2xl border border-gray-200 bg-white p-4 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-emerald-600 text-2xl">💬</span>
                    <span class="text-sm font-medium text-gray-700">@lang('manual_payments::app.shop.whatsapp_support_question')</span>
                </div>
                <a
                    href="{{ $whatsAppUrl }}"
                    target="_blank"
                    class="px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-xs rounded-xl border border-emerald-300 transition flex items-center gap-1.5"
                >
                    @lang('manual_payments::app.shop.whatsapp_support_btn')
                </a>
            </div>
        @endif

        <!-- Bank Accounts Section -->
        @if ($accounts->isNotEmpty())
            <div class="mt-10">
                <h2 class="text-xl font-bold text-[#18181b]">
                    @lang('manual_payments::app.shop.bank_accounts_title')
                </h2>
                <p class="text-sm text-[#71717a] mt-1">
                    @lang('manual_payments::app.shop.bank_accounts_desc')
                </p>

                <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach ($accounts as $account)
                        <div class="rounded-2xl border border-[#ede8dc] bg-white p-5 shadow-xs hover:border-[#9e8464] transition">
                            <div class="flex items-center justify-between pb-3 border-b border-[#ede8dc]">
                                <span class="font-bold text-[#18181b]">{{ $account->title }}</span>
                                <span class="text-xs px-2.5 py-0.5 rounded-full bg-[#fdfbf7] border border-[#ede8dc] text-[#9e8464] font-semibold">
                                    {{ $account->bank_name ?? $account->type_name }}
                                </span>
                            </div>

                            <div class="mt-3 space-y-2 text-sm text-[#52525b]">
                                <div class="flex items-center justify-between">
                                    <span class="text-[#71717a]">@lang('manual_payments::app.shop.account_holder'):</span>
                                    <span class="font-medium text-[#18181b]">{{ $account->holder_name }}</span>
                                </div>

                                @if ($account->holder_id)
                                    <div class="flex items-center justify-between">
                                        <span class="text-[#71717a]">@lang('manual_payments::app.shop.holder_id'):</span>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-medium text-[#18181b]">{{ $account->holder_id }}</span>
                                            <button type="button" onclick="copyText('{{ $account->holder_id }}', this)" class="text-xs font-semibold text-[#9e8464] hover:underline cursor-pointer" style="color: #9e8464 !important;">
                                                @lang('manual_payments::app.shop.copy')
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @if ($account->phone)
                                    <div class="flex items-center justify-between">
                                        <span class="text-[#71717a]">@lang('manual_payments::app.shop.phone'):</span>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-medium text-[#18181b]">{{ $account->phone }}</span>
                                            <button type="button" onclick="copyText('{{ $account->phone }}', this)" class="text-xs font-semibold text-[#9e8464] hover:underline cursor-pointer" style="color: #9e8464 !important;">
                                                @lang('manual_payments::app.shop.copy')
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @if ($account->account_number)
                                    <div class="flex items-center justify-between flex-wrap gap-1">
                                        <span class="text-[#71717a]">@lang('manual_payments::app.shop.account_number'):</span>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-xs font-medium text-[#18181b]">{{ $account->account_number }}</span>
                                            <button type="button" onclick="copyText('{{ $account->account_number }}', this)" class="text-xs font-semibold text-[#9e8464] hover:underline cursor-pointer" style="color: #9e8464 !important;">
                                                @lang('manual_payments::app.shop.copy')
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @if ($account->email)
                                    <div class="flex items-center justify-between">
                                        <span class="text-[#71717a]">@lang('manual_payments::app.shop.email'):</span>
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-[#18181b]">{{ $account->email }}</span>
                                            <button type="button" onclick="copyText('{{ $account->email }}', this)" class="text-xs font-semibold text-[#9e8464] hover:underline cursor-pointer" style="color: #9e8464 !important;">
                                                @lang('manual_payments::app.shop.copy')
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @if ($account->binance_id)
                                    <div class="flex items-center justify-between">
                                        <span class="text-[#71717a]">@lang('manual_payments::app.shop.binance_id'):</span>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-medium text-[#18181b]">{{ $account->binance_id }}</span>
                                            <button type="button" onclick="copyText('{{ $account->binance_id }}', this)" class="text-xs font-semibold text-[#9e8464] hover:underline cursor-pointer" style="color: #9e8464 !important;">
                                                @lang('manual_payments::app.shop.copy')
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if ($account->instructions)
                                <div class="mt-3 pt-3 border-t border-[#ede8dc] text-xs text-[#71717a] italic">
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
            <div class="mt-12 rounded-2xl border border-[#ede8dc] bg-white p-6 md:p-8 shadow-xs">
                <h2 class="text-xl font-bold text-[#18181b]">
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
                            <label class="block text-sm font-semibold text-[#18181b]">
                                @lang('manual_payments::app.shop.destination_account')
                            </label>
                            <select name="payment_account_id" class="mt-1 block w-full rounded-xl border border-[#ede8dc] bg-[#fdfbf7] py-2.5 px-3 text-sm focus:border-[#9e8464] focus:ring-1 focus:ring-[#9e8464]">
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
                                <label class="block text-sm font-semibold text-[#18181b]">
                                    @lang('manual_payments::app.shop.origin_bank')
                                </label>
                                <select name="payer_bank" class="mt-1 block w-full rounded-xl border border-[#ede8dc] bg-[#fdfbf7] py-2.5 px-3 text-sm focus:border-[#9e8464] focus:ring-1 focus:ring-[#9e8464]">
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
                            <label class="block text-sm font-semibold text-[#18181b]">
                                @lang('manual_payments::app.shop.reference_number') <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="reference_number"
                                required
                                value="{{ old('reference_number', $receipt->reference_number) }}"
                                placeholder="@lang('manual_payments::app.shop.reference_placeholder')"
                                class="mt-1 block w-full rounded-xl border border-[#ede8dc] bg-[#fdfbf7] py-2.5 px-3 text-sm font-mono focus:border-[#9e8464] focus:ring-1 focus:ring-[#9e8464]"
                            >
                            @error('reference_number')
                                <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Payer Name -->
                        <div>
                            <label class="block text-sm font-semibold text-[#18181b]">
                                @lang('manual_payments::app.shop.payer_name')
                            </label>
                            <input
                                type="text"
                                name="payer_name"
                                value="{{ old('payer_name', $receipt->payer_name) }}"
                                class="mt-1 block w-full rounded-xl border border-[#ede8dc] bg-[#fdfbf7] py-2.5 px-3 text-sm focus:border-[#9e8464] focus:ring-1 focus:ring-[#9e8464]"
                            >
                        </div>

                        <!-- Payer ID / Phone -->
                        @if ($receipt->payment_method === 'manual_pagomovil')
                            <div>
                                <label class="block text-sm font-semibold text-[#18181b]">
                                    @lang('manual_payments::app.shop.payer_phone')
                                </label>
                                <input
                                    type="text"
                                    name="payer_phone"
                                    value="{{ old('payer_phone', $receipt->payer_phone) }}"
                                    placeholder="Ej. 04141234567"
                                    class="mt-1 block w-full rounded-xl border border-[#ede8dc] bg-[#fdfbf7] py-2.5 px-3 text-sm focus:border-[#9e8464] focus:ring-1 focus:ring-[#9e8464]"
                                >
                            </div>
                        @elseif ($receipt->payment_method === 'manual_zelle')
                            <div>
                                <label class="block text-sm font-semibold text-[#18181b]">
                                    @lang('manual_payments::app.shop.payer_email')
                                </label>
                                <input
                                    type="email"
                                    name="payer_email"
                                    value="{{ old('payer_email', $receipt->payer_email) }}"
                                    class="mt-1 block w-full rounded-xl border border-[#ede8dc] bg-[#fdfbf7] py-2.5 px-3 text-sm focus:border-[#9e8464] focus:ring-1 focus:ring-[#9e8464]"
                                >
                            </div>
                        @endif
                    </div>

                    <!-- Voucher Upload -->
                    <div>
                        <label class="block text-sm font-semibold text-[#18181b]">
                            @lang('manual_payments::app.shop.proof_file') <span class="text-rose-500">*</span>
                        </label>
                        <div class="mt-2 flex justify-center rounded-2xl border-2 border-dashed border-[#ded5c3] bg-[#fdfbf7] px-6 pt-5 pb-6 hover:border-[#9e8464] transition cursor-pointer relative" id="drop-zone">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-[#b59f7f]" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-[#52525b] justify-center">
                                    <label class="relative cursor-pointer rounded-md font-semibold text-[#9e8464] hover:text-[#7f684e] focus-within:outline-hidden">
                                        <span>Subir comprobante</span>
                                        <input id="proof-input" name="proof" type="file" required accept="image/jpeg,image/png,image/webp,application/pdf" class="sr-only">
                                    </label>
                                    <p class="pl-1 text-[#71717a]">o arrastra y suelta aquí</p>
                                </div>
                                <p class="text-xs text-[#a1a1aa]">PNG, JPG, WebP o PDF hasta 5MB</p>
                                <p id="file-name" class="text-xs font-semibold text-emerald-700 hidden"></p>
                            </div>
                        </div>
                        @error('proof')
                            <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-6 border-t border-[#ede8dc]">
                        <button
                            type="submit"
                            class="w-full md:w-auto px-10 py-4 bg-[#18181b] hover:bg-[#9e8464] text-white font-bold text-xs uppercase tracking-widest rounded-full shadow-lg transition-all active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer"
                            style="background-color: #18181b !important; color: #ffffff !important; display: inline-flex !important;"
                        >
                            <svg class="w-4 h-4 text-[#ded5c3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ $receipt->isRejected() ? trans('manual_payments::app.shop.resubmit_button') : trans('manual_payments::app.shop.submit_button') }}</span>
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

        @if ($receipt->payment_method === 'manual_whatsapp' && ! empty($autoRedirectWhatsApp) && ! empty($whatsAppUrl) && $receipt->isPendingSubmission())
            // Redirección automática de cortesía tras 1.5s
            setTimeout(function() {
                window.location.href = '{!! $whatsAppUrl !!}';
            }, 1500);
        @endif
    </script>
</x-shop::layouts>
