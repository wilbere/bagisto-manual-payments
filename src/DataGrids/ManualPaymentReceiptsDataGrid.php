<?php

namespace Wilbere\BagistoManualPayments\DataGrids;

if (! class_exists(\Webkul\DataGrid\DataGrid::class)) {
    abstract class BaseDataGridStub
    {
        protected $primaryColumn = 'id';
        protected $sortOrder = 'desc';
        protected $columns = [];
        protected $actions = [];

        abstract public function prepareQueryBuilder();
        abstract public function prepareColumns();

        public function addFilter(string $key, string $column): void {}

        public function addColumn(array $column): void
        {
            $this->columns[] = $column;
        }

        public function addAction(array $action): void
        {
            $this->actions[] = $action;
        }

        public function toJson(): mixed
        {
            return response()->json(['records' => []]);
        }
    }

    class_alias(BaseDataGridStub::class, \Webkul\DataGrid\DataGrid::class);
}

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class ManualPaymentReceiptsDataGrid extends DataGrid
{
    protected $primaryColumn = 'id';
    protected $sortOrder = 'desc';

    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('manual_payment_receipts')
            ->select(
                'manual_payment_receipts.id',
                'manual_payment_receipts.order_id',
                'manual_payment_receipts.payment_method',
                'manual_payment_receipts.reference_number',
                'manual_payment_receipts.payer_name',
                'manual_payment_receipts.payer_bank',
                'manual_payment_receipts.paid_amount',
                'manual_payment_receipts.paid_currency',
                'manual_payment_receipts.status',
                'manual_payment_receipts.proof_image_path',
                'manual_payment_receipts.created_at'
            );

        $this->addFilter('id', 'manual_payment_receipts.id');
        $this->addFilter('order_id', 'manual_payment_receipts.order_id');
        $this->addFilter('payment_method', 'manual_payment_receipts.payment_method');
        $this->addFilter('reference_number', 'manual_payment_receipts.reference_number');
        $this->addFilter('status', 'manual_payment_receipts.status');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => 'ID',
            'type' => 'integer',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'order_id',
            'label' => trans('manual_payments::app.admin.receipts.columns.order_id'),
            'type' => 'integer',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                if (route_has('admin.sales.orders.view')) {
                    return '<a class="text-blue-600 hover:underline font-semibold" href="'.route('admin.sales.orders.view', $row->order_id).'">#'.$row->order_id.'</a>';
                }

                return '#'.$row->order_id;
            },
        ]);

        $this->addColumn([
            'index' => 'payment_method',
            'label' => trans('manual_payments::app.admin.receipts.columns.method'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                return match ($row->payment_method) {
                    'manual_pagomovil' => '<span class="badge badge-md badge-success">Pago Móvil</span>',
                    'manual_bank_transfer' => '<span class="badge badge-md badge-info">Transferencia</span>',
                    'manual_zelle' => '<span class="badge badge-md badge-primary">Zelle</span>',
                    'manual_binance' => '<span class="badge badge-md badge-warning">Binance Pay</span>',
                    default => e($row->payment_method),
                };
            },
        ]);

        $this->addColumn([
            'index' => 'reference_number',
            'label' => trans('manual_payments::app.admin.receipts.columns.reference'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                return $row->reference_number
                    ? '<span class="font-mono font-bold text-gray-800">'.e($row->reference_number).'</span>'
                    : '<span class="italic text-gray-400">Sin reportar</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'paid_amount',
            'label' => trans('manual_payments::app.admin.receipts.columns.amount'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => false,
            'closure' => function ($row) {
                $currency = $row->paid_currency ?? 'USD';
                $amount = (float) $row->paid_amount;

                if ($currency === 'VES') {
                    return 'Bs. '.number_format($amount, 2, ',', '.');
                }

                if ($currency === 'USDT') {
                    return number_format($amount, 2, '.', ',').' USDT';
                }

                return '$ '.number_format($amount, 2, '.', ',');
            },
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => trans('manual_payments::app.admin.receipts.columns.status'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                return match ($row->status) {
                    'pending_submission' => '<span class="badge badge-md badge-warning">Por Enviar</span>',
                    'pending_verification' => '<span class="badge badge-md badge-info animate-pulse">Por Conciliar</span>',
                    'approved' => '<span class="badge badge-md badge-success">Aprobado</span>',
                    'rejected' => '<span class="badge badge-md badge-danger">Rechazado</span>',
                    'expired' => '<span class="badge badge-md badge-dark">Expirado</span>',
                    default => e($row->status),
                };
            },
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('manual_payments::app.admin.receipts.columns.date'),
            'type' => 'datetime',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
        ]);
    }

    public function prepareActions(): void
    {
        $this->addAction([
            'icon' => 'icon-view',
            'title' => trans('manual_payments::app.admin.actions.view'),
            'method' => 'GET',
            'url' => function ($row) {
                return route('admin.sales.manual_payments.view', $row->id);
            },
        ]);
    }
}
