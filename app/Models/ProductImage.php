<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Product;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'image_path'];

    public function getImagePathAttribute($value) {
        return 'https://siveno.shop/storage/' . $value;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
