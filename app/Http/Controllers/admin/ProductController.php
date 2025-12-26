<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProductController extends Controller
{
    // Display all products
    public function index()
    {
        $products = Product::orderBy('created_at', 'ASC')->get();

        return response()->json([
            'status' => 200,
            'data' => $products
        ], 200);
    }

    //Store new product with images
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'title'        => 'required|string',
            'price'        => 'required|numeric',
            'category'     => 'required|exists:categories,id',
            'brand'        => 'nullable|exists:brands,id',
            'sku_code'     => 'required|string|unique:products,sku_code',
            'status'       => 'required|integer',
            'is_featured'  => 'required|in:yes,no',
            'gallery'      => 'nullable|array',
            'gallery.*'    => 'exists:temp_images,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        // Save product database
        $product = new Product();
        $product->title = $request->title;
        $product->price = $request->price;
        $product->compare_price = $request->compare_price;
        $product->description = $request->description;
        $product->short_description = $request->short_description;
        $product->category_id = $request->category;
        $product->brand_id = $request->brand;
        $product->quantity = $request->quantity;
        $product->sku_code = $request->sku_code;
        $product->barcode = $request->barcode;
        $product->status = $request->status;
        $product->is_featured = $request->is_featured;
        $product->save();

        /**
         * IMAGE HANDLING
         * - Temp image → Product image
         * - Large + Small thumbnail
         * - First image = main image
         */
        if (!empty($request->gallery)) {

            File::ensureDirectoryExists(public_path('uploads/products/large'));
            File::ensureDirectoryExists(public_path('uploads/products/small'));

            foreach ($request->gallery as $key => $tempImageId) {

                $tempImage = TempImage::find($tempImageId);
                if (!$tempImage) {
                    continue;
                }

                // Generate unique image name
                $extension = pathinfo($tempImage->name, PATHINFO_EXTENSION);
                $imageName = $product->id . '-' . Str::uuid() . '.' . $extension;

                $manager = new ImageManager(new Driver());

                // Large image
                $img = $manager->read(public_path('uploads/temp/' . $tempImage->name));
                $img->scaleDown(1200);
                $img->save(public_path('uploads/products/large/' . $imageName));

                // Small thumbnail
                $img = $manager->read(public_path('uploads/temp/' . $tempImage->name));
                $img->coverDown(400, 460);
                $img->save(public_path('uploads/products/small/' . $imageName));

                $productImage = new ProductImage();
                $productImage->image = $imageName;
                $productImage->product_id = $product->id;
                $productImage->save();

                // Set first image as main product image
                if ($key === 0) {
                    $product->image = $imageName;
                    $product->save();
                }

                // Delete temp image files & DB record
                File::delete(public_path('uploads/temp/' . $tempImage->name));
                File::delete(public_path('uploads/temp/thumb/' . $tempImage->name));
                $tempImage->delete();
            }
        }

        return response()->json([
            'status' => 200,
            'message' => 'Product has been created successfully.'
        ], 200);
    }

    // Show single product
    public function show($id)
    {
        $product = Product::with('images')->find($id);

        if (!$product) {
            return response()->json([
                'status' => 404,
                'message' => 'Product not found.'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data' => $product
        ], 200);
    }

    // Update product
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 404,
                'message' => 'Product not found.'
            ], 404);
        }

        // Validation (SKU exception included)
        $validator = Validator::make($request->all(), [
            'title'        => 'required|string',
            'price'        => 'required|numeric',
            'category'     => 'required|exists:categories,id',
            'sku_code'     => 'required|string|unique:products,sku_code,' . $id,
            'status'       => 'required|integer',
            'is_featured'  => 'required|in:yes,no',
            'gallery'      => 'nullable|array',
            'gallery.*'    => 'exists:temp_images,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        // Update product data
        $product->title = $request->title;
        $product->price = $request->price;
        $product->compare_price = $request->compare_price;
        $product->description = $request->description;
        $product->short_description = $request->short_description;
        $product->category_id = $request->category;
        $product->brand_id = $request->brand;
        $product->quantity = $request->quantity;
        $product->sku_code = $request->sku_code;
        $product->barcode = $request->barcode;
        $product->status = $request->status;
        $product->is_featured = $request->is_featured;
        $product->save();

        /* ================= IMAGE HANDLING ================= */
        if (!empty($request->gallery)) {

            File::ensureDirectoryExists(public_path('uploads/products/large'));
            File::ensureDirectoryExists(public_path('uploads/products/small'));

            foreach ($request->gallery as $key => $tempImageId) {

                $tempImage = TempImage::find($tempImageId);
                if (!$tempImage) continue;

                $extension = pathinfo($tempImage->name, PATHINFO_EXTENSION);
                $imageName = $product->id . '-' . Str::uuid() . '.' . $extension;

                $manager = new ImageManager(new Driver());

                // LARGE
                $img = $manager->read(public_path('uploads/temp/' . $tempImage->name));
                $img->scaleDown(1200);
                $img->save(public_path('uploads/products/large/' . $imageName));

                // SMALL
                $img = $manager->read(public_path('uploads/temp/' . $tempImage->name));
                $img->coverDown(400, 460);
                $img->save(public_path('uploads/products/small/' . $imageName));

                // SAVE DB
                ProductImage::create([
                    'product_id' => $product->id,
                    'image'      => $imageName,
                ]);

                // FIRST IMAGE → MAIN IMAGE
                if (!$product->image) {
                    $product->image = $imageName;
                    $product->save();
                }

                // REMOVE TEMP
                File::delete(public_path('uploads/temp/' . $tempImage->name));
                File::delete(public_path('uploads/temp/thumb/' . $tempImage->name));
                $tempImage->delete();
            }
        }

        return response()->json([
            'status' => 200,
            'message' => 'Product has been updated successfully.'
        ], 200);
    }

    // Delete product with images
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 404,
                'message' => 'Product not found.'
            ], 404);
        }

        // Delete product images
        if ($product->image) {
            File::delete(public_path('uploads/products/large/' . $product->image));
            File::delete(public_path('uploads/products/small/' . $product->image));
        }

        $product->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Product has been deleted successfully.'
        ], 200);
    }

    public function saveProductImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'product_id' => 'required|exists:products,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $image = $request->file('image');
        $imageName = $request->product_id . '_' . time() . '.' . $image->extension();

        $manager = new ImageManager(new Driver());

        // Large Image
        $img = $manager->read($image->getPathName());
        $img->scaleDown(1200);
        $img->save(public_path('uploads/products/large/' . $imageName));

        // Small Image
        $img = $manager->read($image->getPathName());
        $img->coverDown(400, 600);
        $img->save(public_path('uploads/products/small/' . $imageName));

        $productImage = new ProductImage();
        $productImage->image = $imageName;
        $productImage->product_id = $request->product_id;
        $productImage->save();

        return response()->json([
            'status' => 200,
            'data' => $productImage,
            'message' => 'Image has been uploaded successfully.'
        ], 200);
    }

    public function updateDefaultImage(Request $request)
    {
        $product = Product::find($request->product_id);
        $product->image = $request->image;
        $product->save();

        return response()->json([
            'status' => 200,
            'message' => 'Product default image updated successfully.'
        ], 200);
    }

    public function deleteImage($id)
    {
        $image = ProductImage::find($id);
        if (!$image) {
            return response()->json(['status' => 404], 404);
        }

        File::delete([
            public_path('uploads/products/large/' . $image->image),
            public_path('uploads/products/small/' . $image->image),
        ]);

        $image->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Product image has been deleted successfully.'
        ]);
    }
}
