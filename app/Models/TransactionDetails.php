<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id', 'item_id', 'quantity', 'price'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transactions::class);
    }
}
