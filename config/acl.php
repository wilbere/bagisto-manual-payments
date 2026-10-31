<?php

return [
    [
        'key'   => 'sales.manual_payments',
        'name'  => 'manual_payments::app.admin.acl.reconciliation',
        'route' => 'admin.sales.manual_payments.index',
        'sort'  => 6.5,
    ],
    [
        'key'   => 'sales.manual_payments.approve',
        'name'  => 'manual_payments::app.admin.acl.approve',
        'route' => 'admin.sales.manual_payments.approve',
        'sort'  => 1,
    ],
    [
        'key'   => 'sales.manual_payments.reject',
        'name'  => 'manual_payments::app.admin.acl.reject',
        'route' => 'admin.sales.manual_payments.reject',
        'sort'  => 2,
    ],
    [
        'key'   => 'settings.manual_payment_accounts',
        'name'  => 'manual_payments::app.admin.acl.accounts',
        'route' => 'admin.settings.manual_payment_accounts.index',
        'sort'  => 3.6,
    ],
    [
        'key'   => 'settings.manual_payment_accounts.create',
        'name'  => 'manual_payments::app.admin.acl.create_account',
        'route' => 'admin.settings.manual_payment_accounts.create',
        'sort'  => 1,
    ],
    [
        'key'   => 'settings.manual_payment_accounts.edit',
        'name'  => 'manual_payments::app.admin.acl.edit_account',
        'route' => 'admin.settings.manual_payment_accounts.edit',
        'sort'  => 2,
    ],
    [
        'key'   => 'settings.manual_payment_accounts.delete',
        'name'  => 'manual_payments::app.admin.acl.delete_account',
        'route' => 'admin.settings.manual_payment_accounts.delete',
        'sort'  => 3,
    ],
];
