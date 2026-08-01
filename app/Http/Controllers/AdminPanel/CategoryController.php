<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::orderBy('name_ru')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name_ru' => 'required|string|max:255',
            'name_tm' => 'required|string|max:255',
            'icon_url' => 'nullable|string|max:255',
        ]);

        Category::create($validated);

        return back()->with('status', 'Категория добавлена.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name_ru' => 'required|string|max:255',
            'name_tm' => 'required|string|max:255',
            'icon_url' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $category->update($validated);

        return back()->with('status', 'Категория обновлена.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return back()->with('status', 'Категория удалена.');
    }
}
