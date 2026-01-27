<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model properties
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $description
 * @property string|null $image_url
 * @property float $price
 * @property int $stock
 * @property string $status
 * @property-read \App\Models\User $seller
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ShopCategory[] $categories
 */
class ShopProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'description', 'image_url', 'price', 'stock', 'status', 'type',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'array',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * Stock is sensitive information and should only be visible after purchase confirmation.
     *
     * @var array<int, string>
     */
    // protected $hidden = [
    //     'stock',
    // ];

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

    public function getCountDataAttribute(): int
    {
        return count($this->stock ?? []);
    }
}
