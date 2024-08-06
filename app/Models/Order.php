<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $connection = 'e_commerce';

    protected $guarded = [
        'id'
    ];

    protected $fillable = ["order_reference_number", "discount_price", "cart_id", "total_price"];


    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        // Generate a unique identifier for tenant_identifier column before saving the model
        static::creating(function ($order) {
            $order->order_reference_number = uniqid();
        });
    }
}
