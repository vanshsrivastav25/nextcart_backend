<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Product extends Model
{
    // JSON response me auto append hoga
    protected $appends = ['image_url'];

    /**
     * Get product image URL
     */
    public function getImageUrlAttribute()
    {
        // Image null ya empty hai
        if (empty($this->image)) {
            return null;
        }

        $path = public_path('uploads/products/small/' . $this->image);

        // File exist nahi karti
        if (!File::exists($path)) {
            return null;
        }

        return asset('uploads/products/small/' . $this->image);
    }
}
