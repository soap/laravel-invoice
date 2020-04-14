<?php

namespace NeptuneSoftware\Invoice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BaseModel extends Model
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
     * Get the invoice lines for this invoice
     */
    public function lines()
    {
        return $this->hasMany(InvoiceLine::class, 'invoice_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function related()
    {
        return $this->morphTo();
    }

}
