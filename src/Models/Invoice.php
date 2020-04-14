<?php

namespace NeptuneSoftware\Invoice\Models;

use Illuminate\Support\Str;
use NeptuneSoftware\Invoice\InvoiceReferenceGenerator;
use NeptuneSoftware\Invoice\Scopes\InvoiceScope;

class Invoice extends BaseModel
{

    /**
     * Invoice constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('invoice.table_names.invoices'));
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new InvoiceScope());
        static::creating(function ($model) {
            /**
             * @var \Illuminate\Database\Eloquent\Model $model
             */
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }

            $model->total     = 0;
            $model->tax       = 0;
            $model->discount  = 0;
            $model->is_bill   = false;
            $model->currency  = config('invoice.default_currency', 'TRY');
            $model->status    = config('invoice.default_status', 'concept');
            $model->reference = InvoiceReferenceGenerator::generate();
        });
    }
}
