<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cart extends Model
{
    use HasFactory;
    protected $connection = 'e_commerce';

    protected $guarded = [
        'id'
    ];

    protected $fillable = ["customer_email", "is_checked_out", "total"];

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
