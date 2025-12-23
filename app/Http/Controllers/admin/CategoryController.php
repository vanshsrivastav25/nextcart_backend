<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('created_at', 'ASC')->get();
        return response()->json([
            'status' => 200,
            'data' => $categories
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $category = new Category();
        $category->name = $request->name;
        $category->status = $request->status;
        $category->save();

        return response()->json([
            'status' => 200,
            'data' => $category,
            'message' => 'Category added successfully.'
        ], 200);
    }

    public function show($id)
    {
        $category = Category::find($id);

        if ($category == null) {
            return response()->json([
                'status' => 404,
                'data' => [],
                'message' => 'Category not found.'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data' => $category
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if ($category == null) {
            return response()->json([
                'status' => 404,
                'data' => [],
                'message' => 'Category not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $category->name = $request->name;
        $category->status = $request->status;
        $category->save();

        return response()->json([
            'status' => 200,
            'data' => $category,
            'message' => 'Category updated successfully.'
        ], 200);
    }

    public function destroy($id)
    {
        $category = Category::find($id);

        if ($category == null) {
            return response()->json([
                'status' => 404,
                'data' => [],
                'message' => 'Category not found.'
            ], 404);
        }

        $category->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Category deleted successfully.'
        ], 200);
    }
}
