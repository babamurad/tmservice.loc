<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Category::orderBy('name_ru')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name_ru' => 'required|string|max:255',
            'name_tm' => 'required|string|max:255',
            'icon_url' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        return response()->json(Category::create($validated), 201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name_ru' => 'sometimes|required|string|max:255',
            'name_tm' => 'sometimes|required|string|max:255',
            'icon_url' => 'sometimes|nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $category->update($validated);

        return response()->json($category);
    }

    public function destroy(Category $category): Response
    {
        $category->delete();

        return response()->noContent();
    }
}
