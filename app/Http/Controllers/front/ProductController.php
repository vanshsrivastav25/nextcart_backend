<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function latestProduct()
    {
        $product = Product::orderBy('created_at', 'ASC')
            ->where('status', 1)
            ->limit(8)
            ->get();
        return response()->json([
            'status' => 200,
            'data' => $product
        ], 200);
    }
    
    public function featuredProduct()
    {
        $product = Product::orderBy('created_at', 'ASC')
            ->where('status', 1)
            ->where('is_featured', 'yes')
            ->limit(8)
            ->get();
        return response()->json([
            'status' => 200,
            'data' => $product
        ], 200);
    }
}
