<?php

namespace NeptuneSoftware\Invoice\Traits;

use NeptuneSoftware\Invoice\Models\InvoiceLine;

trait Invoiceable
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
