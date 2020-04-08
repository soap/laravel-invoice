<?php
namespace NeptuneSoftware\Invoice\Models;

use NeptuneSoftware\Invoice\Scopes\BillScope;
use NeptuneSoftware\Invoice\Scopes\InvoiceScope;

class Bill extends Invoice
{

    protected $guarded = [];

    public $incrementing = false;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new BillScope());
        static::creating(function ($model) {
            $model->is_bill = true;
        });
    }

    /**
     * Get the invoice lines for this invoice
     */
    public function lines()
    {
        return $this->hasMany(InvoiceLine::class, 'invoice_id')->withoutGlobalScope(InvoiceScope::class);
    }

}
