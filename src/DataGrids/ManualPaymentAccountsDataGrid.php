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

class ManualPaymentAccountsDataGrid extends DataGrid
{
    protected $primaryColumn = 'id';
    protected $sortOrder = 'asc';

    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('manual_payment_accounts')
            ->select(
                'id',
                'type',
                'title',
                'bank_name',
                'holder_name',
                'holder_id',
                'phone',
                'email',
                'status',
                'sort_order',
                'created_at'
            );

        $this->addFilter('id', 'manual_payment_accounts.id');
        $this->addFilter('type', 'manual_payment_accounts.type');
        $this->addFilter('title', 'manual_payment_accounts.title');
        $this->addFilter('bank_name', 'manual_payment_accounts.bank_name');
        $this->addFilter('status', 'manual_payment_accounts.status');

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
            'index' => 'type',
            'label' => trans('manual_payments::app.admin.accounts.columns.type'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                return match ($row->type) {
                    'pagomovil' => '<span class="badge badge-md badge-success">Pago Móvil</span>',
                    'bank_transfer' => '<span class="badge badge-md badge-info">Transferencia</span>',
                    'zelle' => '<span class="badge badge-md badge-primary">Zelle</span>',
                    'binance' => '<span class="badge badge-md badge-warning">Binance Pay</span>',
                    default => e($row->type),
                };
            },
        ]);

        $this->addColumn([
            'index' => 'title',
            'label' => trans('manual_payments::app.admin.accounts.columns.title'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'bank_name',
            'label' => trans('manual_payments::app.admin.accounts.columns.bank'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'holder_name',
            'label' => trans('manual_payments::app.admin.accounts.columns.holder'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => trans('manual_payments::app.admin.accounts.columns.status'),
            'type' => 'boolean',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                return $row->status
                    ? '<span class="badge badge-md badge-success">'.trans('manual_payments::app.admin.accounts.active').'</span>'
                    : '<span class="badge badge-md badge-danger">'.trans('manual_payments::app.admin.accounts.inactive').'</span>';
            },
        ]);
    }

    public function prepareActions(): void
    {
        $this->addAction([
            'icon' => 'icon-edit',
            'title' => trans('manual_payments::app.admin.actions.edit'),
            'method' => 'GET',
            'url' => function ($row) {
                return route('admin.settings.manual_payment_accounts.edit', $row->id);
            },
        ]);

        $this->addAction([
            'icon' => 'icon-delete',
            'title' => trans('manual_payments::app.admin.actions.delete'),
            'method' => 'DELETE',
            'url' => function ($row) {
                return route('admin.settings.manual_payment_accounts.destroy', $row->id);
            },
        ]);
    }
}
