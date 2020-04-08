<?php

namespace NeptuneSoftware\Invoice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use NeptuneSoftware\Invoice\InvoiceReferenceGenerator;
use NeptuneSoftware\Invoice\Scopes\InvoiceScope;

class Invoice extends Model
{

    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'related_id', 'related_type', 'tax',  'total', 'discount', 'currency',
        'reference', 'status', 'receiver_info', 'sender_info', 'payment_info', 'note', 'is_bill'
    ];

    protected $guarded = [];

    public $incrementing = false;

    /**
     * Invoice constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('invoice.table_names.invoices'));
    }

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

    /**
     * Get the invoice lines for this invoice
     */
    public function lines()
    {
        return $this->hasMany(InvoiceLine::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function related()
    {
        return $this->morphTo();
    }

}
