<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'description', 'image_url', 'price', 'stock', 'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * Stock is sensitive information and should only be visible after purchase confirmation.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'stock',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ShopCategory::class, 'shop_category_product');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(ShopTransaction::class, 'product_id');
    }
}
