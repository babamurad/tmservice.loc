<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\MasterProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MasterProfile::with(['city', 'category', 'user'])
            ->whereHas('user', fn ($q) => $q->whereNotNull('phone_verified_at'))
            ->where('moderation_status', 'approved');

        if ($request->filled('city_id')) {
            $query->whereIn('city_id', $this->resolveCityIds((int) $request->city_id));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('q')) {
            $needle = '%'.addcslashes($request->q, '%_\\').'%';

            $query->where(function ($sub) use ($needle) {
                $this->whereLikeAny($sub, ['bio'], $needle)
                    ->orWhereHas('category', fn ($q) => $this->whereLikeAny($q, ['name_ru', 'name_tm'], $needle))
                    ->orWhereHas('city', fn ($q) => $this->whereLikeAny($q, ['name_ru', 'name_tm'], $needle));
            });
        }

        $masters = $query->orderByDesc('is_free')
            ->orderByDesc('avg_rating')
            ->paginate(15);

        return response()->json($masters);
    }

    /**
     * Головной город (parent_city_id = null) — ищем и в нём самом, и во всех
     * его посёлках-спутниках (клиент из Туркменабада должен видеть мастеров
     * из соседних посёлков, а не только по точному city_id). Если передан
     * id самого посёлка — клиент явно сузил поиск, ищем только по нему.
     * См. plan/README.md, "Города и посёлки-спутники Туркменабада".
     */
    private function resolveCityIds(int $cityId): array
    {
        $city = City::find($cityId);

        if (! $city || $city->isSatellite()) {
            return [$cityId];
        }

        return City::where('id', $cityId)->orWhere('parent_city_id', $cityId)->pluck('id')->all();
    }

    /**
     * SQLite не понимает "\" как escape-символ в LIKE без явного ESCAPE —
     * MySQL понимает по умолчанию, SQLite нет. Пишем ESCAPE явно, чтобы
     * escapeLike работал одинаково на обоих драйверах (dev — sqlite, прод — MySQL).
     */
    private function whereLikeAny(Builder $query, array $columns, string $needle): Builder
    {
        return $query->where(function (Builder $sub) use ($columns, $needle) {
            foreach ($columns as $index => $column) {
                $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                $sub->{$method}("{$column} LIKE ? ESCAPE '\\'", [$needle]);
            }
        });
    }

    public function show(int $id): JsonResponse
    {
        $master = MasterProfile::with(['city', 'category', 'user', 'portfolioImages'])
            ->whereHas('user', fn ($q) => $q->whereNotNull('phone_verified_at'))
            ->where('moderation_status', 'approved')
            ->findOrFail($id);

        return response()->json($master);
    }
}
