<?php

namespace Soap\Invoices\Traits;

use Soap\Invoices\Models\Bill;
use Soap\Invoices\Models\Invoice;
use Soap\Invoices\Models\InvoiceLine;

trait HasInvoices
{
    /**
     * Set the polymorphic relation.
     *
     * @return mixed
     */
    public function invoices()
    {
        return $this->morphMany(Invoice::class, 'related');
    }

    /**
     * @return mixed
     */
    public function bills()
    {
        return $this->morphMany(Bill::class, 'related');
    }
}
