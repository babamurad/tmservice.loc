<?php

namespace App\Http\Controllers;

use App\Models\MasterProfile;

abstract class Controller
{
    /**
     * Мастер, которого может увидеть/затронуть обычный (не-admin) клиент:
     * телефон подтверждён, профиль одобрен модерацией. Общий критерий для
     * отзывов, жалоб и любых будущих публичных действий над мастером.
     */
    protected function findPublicMaster(int $id): MasterProfile
    {
        return MasterProfile::where('moderation_status', 'approved')
            ->whereHas('user', fn ($q) => $q->whereNotNull('phone_verified_at'))
            ->findOrFail($id);
    }
}
