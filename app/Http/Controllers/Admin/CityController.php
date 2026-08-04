<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class CityController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(City::orderBy('name_ru')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name_ru' => 'required|string|max:255',
            'name_tm' => 'required|string|max:255',
            'is_active' => 'boolean',
            // Только головной город (без своего parent_city_id) может быть
            // родителем — не допускаем посёлок-у-посёлка (см. plan/README.md).
            'parent_city_id' => ['nullable', 'integer', Rule::exists('cities', 'id')->whereNull('parent_city_id')],
        ]);

        return response()->json(City::create($validated), 201);
    }

    public function update(Request $request, City $city): JsonResponse
    {
        $validated = $request->validate([
            'name_ru' => 'sometimes|required|string|max:255',
            'name_tm' => 'sometimes|required|string|max:255',
            'is_active' => 'sometimes|boolean',
            'parent_city_id' => [
                'nullable',
                'integer',
                Rule::exists('cities', 'id')->whereNull('parent_city_id'),
                Rule::notIn([$city->id]),
            ],
        ]);

        $city->update($validated);

        return response()->json($city);
    }

    public function destroy(City $city): Response
    {
        $city->delete();

        return response()->noContent();
    }
}
