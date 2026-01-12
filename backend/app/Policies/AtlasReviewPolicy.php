<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Shop;

final class AtlasReviewPolicy
{
    /**
     * Atlas 分析リクエスト一覧を閲覧できるか
     */
    public function list(User $user, Shop $shop): bool
    {
        return $this->canReview($user, $shop);
    }

    /**
     * Atlas 分析結果をレビュー（Approve/Reject/Replay）できるか
     */
    public function review(User $user, Shop $shop): bool
    {
        return $this->canReview($user, $shop);
    }

    private function canReview(User $user, Shop $shop): bool
    {
        // グローバル管理者
        if ($user->roles()
            ->whereIn('slug', ['domain_lead_admin', 'supervisor_admin'])
            ->exists()) {
            return true;
        }

        // shop 単位 owner / manager（role_user pivot に shop_id がある前提）
        return $user->roles()
            ->whereIn('roles.slug', ['owner', 'manager'])
            ->wherePivot('shop_id', $shop->id)
            ->exists();
    }
}