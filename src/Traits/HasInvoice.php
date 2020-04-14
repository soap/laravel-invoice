<?php

namespace NeptuneSoftware\Invoice\Traits;

use NeptuneSoftware\Invoice\Models\Bill;
use NeptuneSoftware\Invoice\Models\Invoice;
use NeptuneSoftware\Invoice\Models\InvoiceLine;

trait HasInvoice
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

    /**
     * Set the polymorphic relation.
     *
     * @return mixed
     */
    public function invoiceLines()
    {
        return $this->morphMany(InvoiceLine::class, 'invoiceable');
    }
}
