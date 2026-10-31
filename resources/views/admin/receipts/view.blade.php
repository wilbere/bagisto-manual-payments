<x-admin::layouts>
    <x-slot:title>
        @lang('manual_payments::app.admin.receipts.view_title'){{ $receipt->id }} (Orden #{{ $receipt->order_id }})
    </x-slot>

    <div class="flex flex-col gap-6">
        <!-- Top Navigation Bar -->
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('admin.sales.manual_payments.index') }}"
                    class="transparent-button flex items-center gap-1 hover:bg-gray-200 dark:hover:bg-gray-800"
                >
                    <span class="icon-arrow-left text-xl"></span>
                    Volver a la cola
                </a>

                <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                    Conciliación #{{ $receipt->id }} &mdash; Pedido
                    <a class="text-blue-600 hover:underline" href="{{ route_has('admin.sales.orders.view') ? route('admin.sales.orders.view', $receipt->order_id) : '#' }}">
                        #{{ $receipt->order_id }}
                    </a>
                </h1>
            </div>

            <div class="flex items-center gap-2">
                @php
                    $statusBadge = match($receipt->status) {
                        'pending_submission' => 'badge-warning',
                        'pending_verification' => 'badge-info animate-pulse',
                        'approved' => 'badge-success',
                        'rejected' => 'badge-danger',
                        'expired' => 'badge-dark',
                        default => 'badge-info',
                    };
                @endphp
                <span class="badge badge-md {{ $statusBadge }}">
                    {{ $receipt->status_name }}
                </span>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Panel: Voucher Proof Preview (7 cols) -->
            <div class="lg:col-span-7 flex flex-col gap-4 rounded-lg border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                    <p class="font-bold text-gray-800 dark:text-white">
                        Comprobante de Pago
                    </p>

                    @if ($receipt->proof_image_path)
                        <a
                            href="{{ route('admin.sales.manual_payments.download_proof', $receipt->id) }}"
                            target="_blank"
                            class="secondary-button flex items-center gap-1.5 text-xs"
                        >
                            <span class="icon-download text-base"></span>
                            @lang('manual_payments::app.admin.actions.download_proof')
                        </a>
                    @endif
                </div>

                @if ($receipt->proof_image_path)
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-2 flex justify-center items-center overflow-hidden dark:border-gray-800 dark:bg-gray-950 min-h-[400px]">
                        @if (str_contains($receipt->proof_mime ?? '', 'pdf'))
                            <iframe
                                src="{{ route('admin.sales.manual_payments.download_proof', $receipt->id) }}"
                                class="w-full h-[600px] rounded-sm"
                            ></iframe>
                        @else
                            <img
                                src="{{ route('admin.sales.manual_payments.download_proof', $receipt->id) }}"
                                alt="Comprobante"
                                class="max-h-[650px] w-auto object-contain rounded-md shadow-xs hover:scale-105 transition cursor-zoom-in"
                                onclick="window.open(this.src, '_blank')"
                            >
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 text-center">
                        Archivo: {{ $receipt->proof_original_name }} &bull; {{ round(($receipt->proof_size ?? 0) / 1024, 1) }} KB &bull; Clic sobre la imagen para abrirla en tamaño completo
                    </p>
                @else
                    <div class="p-12 text-center text-gray-400 flex flex-col items-center justify-center">
                        <span class="icon-cross text-4xl mb-2 text-gray-300"></span>
                        <p class="font-medium">El comprador aún no ha subido el archivo del comprobante.</p>
                        @if ($receipt->expires_at)
                            <p class="text-xs text-gray-400 mt-1">Expira: {{ $receipt->expires_at->format('d/m/Y H:i:s') }}</p>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Right Panel: Data & Reconciliation Actions (5 cols) -->
            <div class="lg:col-span-5 flex flex-col gap-6">
                <!-- Data Comparison Card -->
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 flex flex-col gap-4">
                    <p class="font-bold text-gray-800 dark:text-white border-b border-gray-100 pb-3 dark:border-gray-800">
                        Datos Reportados vs. Pedido
                    </p>

                    <!-- Amount Box -->
                    <div class="rounded-lg bg-blue-50 p-4 border border-blue-100 dark:bg-blue-950/40 dark:border-blue-900">
                        <span class="text-xs uppercase font-semibold text-blue-700 dark:text-blue-300">Monto del Pago</span>
                        <div class="text-2xl font-extrabold text-blue-900 dark:text-white mt-1">
                            @if ($receipt->paid_currency === 'VES')
                                Bs. {{ number_format((float) $receipt->paid_amount, 2, ',', '.') }}
                            @elseif ($receipt->paid_currency === 'USDT')
                                {{ number_format((float) $receipt->paid_amount, 2, '.', ',') }} USDT
                            @else
                                $ {{ number_format((float) $receipt->paid_amount, 2, '.', ',') }} USD
                            @endif
                        </div>
                        @if ($receipt->exchange_rate)
                            <span class="text-xs text-blue-600 dark:text-blue-400 block mt-1">
                                Tasa aplicada: {{ number_format((float) $receipt->exchange_rate, 4, ',', '.') }} Bs./USD
                            </span>
                        @endif
                    </div>

                    <!-- Details Table -->
                    <div class="flex flex-col gap-2.5 text-xs">
                        <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                            <span class="text-gray-500">Número de Referencia:</span>
                            <span class="font-mono font-bold text-gray-800 dark:text-white text-sm">
                                {{ $receipt->reference_number ?? 'N/A' }}
                            </span>
                        </div>

                        <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                            <span class="text-gray-500">Método de Pago:</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                {{ $receipt->payment_method }}
                            </span>
                        </div>

                        <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                            <span class="text-gray-500">Cuenta Destino:</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                {{ $receipt->account?->title ?? 'No especificada' }}
                            </span>
                        </div>

                        <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                            <span class="text-gray-500">Banco Origen del Cliente:</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                {{ $receipt->payer_bank ?? 'N/A' }}
                            </span>
                        </div>

                        <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                            <span class="text-gray-500">Titular Pagador:</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                {{ $receipt->payer_name ?? 'N/A' }} ({{ $receipt->payer_id ?? 'Sin documento' }})
                            </span>
                        </div>

                        @if ($receipt->payer_phone)
                            <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                                <span class="text-gray-500">Teléfono Pagador:</span>
                                <span class="font-mono text-gray-800 dark:text-white">{{ $receipt->payer_phone }}</span>
                            </div>
                        @endif

                        @if ($receipt->payer_email)
                            <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                                <span class="text-gray-500">Correo Pagador:</span>
                                <span class="text-gray-800 dark:text-white">{{ $receipt->payer_email }}</span>
                            </div>
                        @endif

                        <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                            <span class="text-gray-500">Fecha y Hora del Pago:</span>
                            <span class="text-gray-800 dark:text-white">{{ $receipt->paid_at?->format('d/m/Y H:i:s') ?? 'N/A' }}</span>
                        </div>

                        @if ($receipt->verified_at)
                            <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-800">
                                <span class="text-gray-500">Auditado por:</span>
                                <span class="text-gray-800 dark:text-white">
                                    {{ $receipt->verifier?->name ?? 'Admin #'.$receipt->verified_by }} ({{ $receipt->verified_at->format('d/m/Y H:i') }})
                                </span>
                            </div>
                        @endif

                        @if ($receipt->rejection_reason)
                            <div class="p-3 bg-rose-50 border border-rose-200 rounded-md text-rose-800 mt-2">
                                <span class="font-bold">Motivo de Rechazo:</span> {{ $receipt->rejection_reason }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Action Controls (Approve / Reject) -->
                @if (! $receipt->isApproved())
                    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 flex flex-col gap-4">
                        <p class="font-bold text-gray-800 dark:text-white">
                            Acciones de Conciliación
                        </p>

                        <!-- Approve Button & Form -->
                        @if (! function_exists('bouncer') || bouncer()->hasPermission('sales.manual_payments.approve'))
                            <form
                                action="{{ route('admin.sales.manual_payments.approve', $receipt->id) }}"
                                method="POST"
                                onsubmit="return confirm('@lang('manual_payments::app.admin.receipts.modal_approve_desc')');"
                                class="flex flex-col gap-3"
                            >
                                @csrf
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">
                                        Notas de Aprobación (Opcional):
                                    </label>
                                    <input
                                        type="text"
                                        name="admin_notes"
                                        placeholder="Ej. Conciliado con extracto bancario Banesco"
                                        class="rounded-md border border-gray-300 py-1.5 px-3 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    >
                                </div>

                                <button
                                    type="submit"
                                    class="primary-button bg-emerald-600 hover:bg-emerald-700 text-white w-full py-2.5 flex items-center justify-center gap-2 font-bold"
                                >
                                    <span class="icon-done text-xl"></span>
                                    @lang('manual_payments::app.admin.receipts.modal_approve_title')
                                </button>
                            </form>
                        @endif

                        <div class="border-t border-gray-100 dark:border-gray-800 my-1"></div>

                        <!-- Reject Button & Form -->
                        @if (! function_exists('bouncer') || bouncer()->hasPermission('sales.manual_payments.reject'))
                            <form
                                action="{{ route('admin.sales.manual_payments.reject', $receipt->id) }}"
                                method="POST"
                                onsubmit="return confirm('¿Seguro que deseas rechazar este comprobante?');"
                                class="flex flex-col gap-3"
                            >
                                @csrf
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">
                                        @lang('manual_payments::app.admin.receipts.reason_label') <span class="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="reason"
                                        required
                                        placeholder="@lang('manual_payments::app.admin.receipts.reason_placeholder')"
                                        class="rounded-md border border-gray-300 py-1.5 px-3 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    >
                                </div>

                                <button
                                    type="submit"
                                    class="secondary-button border-rose-300 text-rose-700 hover:bg-rose-50 w-full py-2 flex items-center justify-center gap-2"
                                >
                                    <span class="icon-cancel text-lg"></span>
                                    @lang('manual_payments::app.admin.receipts.modal_reject_title')
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin::layouts>
