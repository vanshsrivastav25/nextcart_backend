<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'image'];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if (!$this->image) return null;

        $path = public_path('uploads/products/small/' . $this->image);

        if (!File::exists($path)) return null;

        return asset('uploads/products/small/' . $this->image);
    }
}
