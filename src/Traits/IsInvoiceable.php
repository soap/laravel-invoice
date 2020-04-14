<?php

namespace NeptuneSoftware\Invoice\Traits;

use NeptuneSoftware\Invoice\Models\InvoiceLine;

trait IsInvoiceable
{
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
