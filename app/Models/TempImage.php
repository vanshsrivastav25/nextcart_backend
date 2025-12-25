<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class TempImage extends Model
{
    protected $fillable = ['name'];
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if (!$this->name) return null;

        $path = public_path('uploads/temp/thumb/' . $this->name);

        if (!File::exists($path)) return null;

        return asset('uploads/temp/thumb/' . $this->name);
    }
}
