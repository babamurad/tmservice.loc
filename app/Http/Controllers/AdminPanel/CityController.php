<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CityController extends Controller
{
    public function index(): View
    {
        return view('admin.cities.index', [
            'cities' => City::orderBy('name_ru')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name_ru' => 'required|string|max:255',
            'name_tm' => 'required|string|max:255',
        ]);

        City::create($validated);

        return back()->with('status', 'Город добавлен.');
    }

    public function update(Request $request, City $city): RedirectResponse
    {
        $validated = $request->validate([
            'name_ru' => 'required|string|max:255',
            'name_tm' => 'required|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $city->update($validated);

        return back()->with('status', 'Город обновлён.');
    }

    public function destroy(City $city): RedirectResponse
    {
        $city->delete();

        return back()->with('status', 'Город удалён.');
    }
}
