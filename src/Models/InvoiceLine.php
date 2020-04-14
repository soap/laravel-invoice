<?php

namespace NeptuneSoftware\Invoice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use NeptuneSoftware\Invoice\Scopes\BillScope;
use NeptuneSoftware\Invoice\Scopes\InvoiceScope;

class InvoiceLine extends Model
{
    protected $guarded = [];

    public $incrementing = false;

    protected $fillable = [
        'amount', 'tax', 'tax_details', 'invoice_id', 'description', 'invoiceable_id',
        'invoiceable_type', 'name', 'discount', 'quantity', 'is_free', 'is_complimentary'
    ];

    protected $with = ['invoice'];

    protected $casts = [
        'tax_details' => 'array'
    ];

    /**
     * InvoiceLine constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('invoice.table_names.invoice_lines'));
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            /**
             * @var \Illuminate\Database\Eloquent\Model $model
             */
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class, 'invoice_id')
            ->withoutGlobalScope(InvoiceScope::class)
            ->withGlobalScope('bill', new BillScope());
    }

    /**
     * Get the owning invoiceable model.
     */
    public function invoiceable()
    {
        return $this->morphTo();
    }
}
