<?php

namespace App\Http\Controllers;

use App\Models\MasterProfile;
use App\Models\PortfolioImage;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;

class ProfileController extends Controller
{
    private const MAX_PORTFOLIO_IMAGES = 10;

    private function getMasterProfile(Request $request): ?MasterProfile
    {
        $user = $request->user();
        $profile = $user->masterProfile;

        if (! $profile) {
            $profile = $user->masterProfile()->create();
        }

        return $profile;
    }

    public function show(Request $request): JsonResponse
    {
        $profile = $this->getMasterProfile($request);

        if (! $profile) {
            return response()->json(['message' => 'Профиль не найден.'], 404);
        }

        $profile->load(['city', 'category', 'user', 'portfolioImages']);

        return response()->json($profile);
    }

    public function update(Request $request): JsonResponse
    {
        $profile = $this->getMasterProfile($request);

        if (! $profile) {
            return response()->json(['message' => 'Профиль не найден.'], 404);
        }

        $validated = $request->validate([
            'bio' => 'nullable|string|max:1000',
            'city_id' => 'nullable|exists:cities,id',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $profile->update($validated);

        return response()->json($profile);
    }

    public function status(Request $request): JsonResponse
    {
        $profile = $this->getMasterProfile($request);

        if (! $profile) {
            return response()->json(['message' => 'Профиль не найден.'], 404);
        }

        $profile->update(['is_free' => ! $profile->is_free]);

        return response()->json(['is_free' => $profile->fresh()->is_free]);
    }

    public function portfolio(Request $request): JsonResponse
    {
        $profile = $this->getMasterProfile($request);

        if (! $profile) {
            return response()->json(['message' => 'Профиль не найден.'], 404);
        }

        if ($profile->portfolioImages()->count() >= self::MAX_PORTFOLIO_IMAGES) {
            return response()->json([
                'message' => 'Достигнут лимит фото в портфолио ('.self::MAX_PORTFOLIO_IMAGES.'). Удалите старое фото, чтобы загрузить новое.',
            ], 422);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $file = $request->file('image');
        $filename = uniqid('portfolio_').'.webp';

        $image = Image::decodePath($file->getRealPath());
        $image->resize(width: 1200, height: 1200);
        $encoded = $image->encode(new WebpEncoder(quality: 80));

        Storage::disk('public')->put('portfolio/'.$filename, $encoded->toString());

        $portfolioImage = PortfolioImage::create([
            'master_profile_id' => $profile->id,
            'image_path' => 'portfolio/'.$filename,
        ]);

        return response()->json($portfolioImage, 201);
    }

    public function deletePortfolio(Request $request, int $id): JsonResponse|Response
    {
        $profile = $this->getMasterProfile($request);

        if (! $profile) {
            return response()->json(['message' => 'Профиль не найден.'], 404);
        }

        $portfolioImage = PortfolioImage::find($id);

        if (! $portfolioImage) {
            return response()->json(['message' => 'Фото не найдено.'], 404);
        }

        if ($portfolioImage->master_profile_id !== $profile->id) {
            return response()->json(['message' => 'Это фото принадлежит другому мастеру.'], 403);
        }

        Storage::disk('public')->delete($portfolioImage->image_path);
        $portfolioImage->delete();

        return response()->noContent();
    }

    public function generateQr(Request $request): JsonResponse
    {
        $profile = $this->getMasterProfile($request);

        if (! $profile) {
            return response()->json(['message' => 'Профиль не найден.'], 404);
        }

        $filename = 'qr_master_'.$profile->id.'.png';

        $qrCode = new QrCode(route('master.link', $profile->id));
        $writer = new PngWriter;
        $result = $writer->write($qrCode);

        Storage::disk('public')->put('qr/'.$filename, $result->getString());

        $profile->update(['qr_code_path' => 'qr/'.$filename]);

        return response()->json([
            'qr_code_url' => asset('storage/qr/'.$filename),
        ]);
    }
}
