<?php

return [
    'default_currency' => 'THB',
    'default_status' => 'draft',
    'locale' => 'th_TH',
    'table_names' => [
        'invoices' => 'invoices',
        'invoice_lines' => 'invoice_lines',
    ],

    'tables' => [
        'invoices' => 'invoices',
        'invoice_lines' => 'invoice_lines',
    ],
    'models' => [
        'invoices' => \Soap\Invoices\Models\Invoice::class,
        'invoice_lines' => \Soap\Invoices\Models\InvoiceLine::class
    ]
];
