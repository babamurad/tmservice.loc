<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class DeepLinkController extends Controller
{
    public function appleAppSiteAssociation(): JsonResponse
    {
        $teamId = config('deeplink.apple_team_id');
        $bundleId = config('deeplink.apple_bundle_id');

        $details = [];

        if ($teamId && $bundleId) {
            $details[] = [
                'appID' => "{$teamId}.{$bundleId}",
                'paths' => ['/m/*'],
            ];
        }

        return response()->json([
            'applinks' => [
                'apps' => [],
                'details' => $details,
            ],
        ]);
    }

    public function androidAssetLinks(): JsonResponse
    {
        $package = config('deeplink.android_package');
        $fingerprints = config('deeplink.android_sha256_fingerprints');

        $links = [];

        if ($package && $fingerprints !== []) {
            $links[] = [
                'relation' => ['delegate_permission/common.handle_all_urls'],
                'target' => [
                    'namespace' => 'android_app',
                    'package_name' => $package,
                    'sha256_cert_fingerprints' => $fingerprints,
                ],
            ];
        }

        return response()->json($links);
    }
}
