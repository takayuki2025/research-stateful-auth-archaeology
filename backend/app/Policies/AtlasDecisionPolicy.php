<?php

declare(strict_types=1);

namespace App\Policies;

use DomainException;

final class AtlasDecisionPolicy
{
    /**
     * decisionType: approve | system_approve | reject | edit_confirm | manual_override
     * actorRole: role slug（owner / manager / staff ...）
     * maxConfidence: AfterSnapshot の最大 confidence（null の場合は 0 扱い）
     */
    public function assertCanDecide(
        string $decisionType,
        string $actorRole,
        ?float $maxConfidence,
    ): void {
        $c = $maxConfidence ?? 0.0;

        // 1) 決定種別のバリデーション（想定外を弾く）
        $allowed = ['approve', 'system_approve', 'reject', 'edit_confirm', 'manual_override'];
        if (!in_array($decisionType, $allowed, true)) {
            throw new DomainException('Invalid decisionType.');
        }

        // 2) Owner/Admin 系は基本OK（ただし system_approve は systemのみ等、将来拡張可）
        if (in_array($actorRole, [
            'owner',
            'domain_lead_admin',
            'supervisor_admin',
            'system_manager_admin',
        ], true)) {
            // 高confidence手動上書きは “許可はするが監査必須” は別途Listenerで対応する想定
            return;
        }

        // 3) Manager：基本OKだが、危険操作を制御
        if ($actorRole === 'manager') {
            // managerは manual_override を禁止（事故率が跳ねるため）
            if ($decisionType === 'manual_override') {
                throw new DomainException('Managers cannot manual_override.');
            }

            // edit_confirm は confidence >= 0.7 なら禁止（上位者に回す）
            if ($decisionType === 'edit_confirm' && $c >= 0.70) {
                throw new DomainException('High-confidence edit_confirm requires owner/admin approval.');
            }

            // approve/reject は許可（組織運用上の実務ライン）
            return;
        }

        // 4) Staff：閲覧はできても決定は禁止（v3の責任設計）
        if ($actorRole === 'staff') {
            throw new DomainException('Staff cannot decide review.');
        }

        // 5) 未定義ロールは拒否
        throw new DomainException('You are not allowed to decide review.');
    }
}