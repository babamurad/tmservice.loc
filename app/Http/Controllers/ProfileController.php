<?php

namespace App\Http\Controllers;

use App\Models\PortfolioImage;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()->masterProfile;

        if (! $profile) {
            return response()->json(['message' => 'Профиль не найден.'], 404);
        }

        $profile->load(['city', 'category', 'user', 'portfolioImages']);

        return response()->json($profile);
    }

    public function update(Request $request): JsonResponse
    {
        $profile = $request->user()->masterProfile;

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
        $profile = $request->user()->masterProfile;

        if (! $profile) {
            return response()->json(['message' => 'Профиль не найден.'], 404);
        }

        $profile->update(['is_free' => ! $profile->is_free]);

        return response()->json(['is_free' => $profile->fresh()->is_free]);
    }

    public function portfolio(Request $request): JsonResponse
    {
        $profile = $request->user()->masterProfile;

        if (! $profile) {
            return response()->json(['message' => 'Профиль не найден.'], 404);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $file = $request->file('image');
        $filename = uniqid('portfolio_') . '.webp';

        $image = Image::read($file->getRealPath());
        $image->resize(width: 1200, height: 1200);
        $encoded = $image->toWebp(quality: 80);

        Storage::disk('public')->put('portfolio/' . $filename, $encoded);

        $portfolioImage = PortfolioImage::create([
            'master_profile_id' => $profile->id,
            'image_path' => 'portfolio/' . $filename,
        ]);

        return response()->json($portfolioImage, 201);
    }

    public function generateQr(Request $request): JsonResponse
    {
        $profile = $request->user()->masterProfile;

        if (! $profile) {
            return response()->json(['message' => 'Профиль не найден.'], 404);
        }

        $filename = 'qr_master_' . $profile->id . '.png';

        $qrCode = new QrCode('appscheme://master/' . $profile->id);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        Storage::disk('public')->put('qr/' . $filename, $result->getString());

        $profile->update(['qr_code_path' => 'qr/' . $filename]);

        return response()->json([
            'qr_code_url' => asset('storage/qr/' . $filename),
        ]);
    }
}
