<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Policy;

use DomainException;

final class AtlasDecisionPolicy
{
    public function assertCanDecide(
        string $decisionType,
        string $actorRole,
        ?float $maxConfidence,
    ): void {

        // 役割の正規化（slug前提）
        $role = $actorRole;

        // 無条件OK（強権限）
        if (in_array($role, ['owner', 'domain_lead_admin', 'supervisor_admin', 'system_manager_admin'], true)) {
            return;
        }

        // manager: 高confidenceの manual_override/edit_confirm を抑制したい、など将来ルール
        if ($role === 'manager') {
            // 例：confidence >= 0.90 の manual_override は禁止
            if (in_array($decisionType, ['manual_override'], true) && ($maxConfidence ?? 0.0) >= 0.90) {
                throw new DomainException('High-confidence manual_override requires owner/admin.');
            }
            return;
        }

        // staff: approveはOK、manual_overrideは不可、など
        if ($role === 'staff') {
            if (in_array($decisionType, ['manual_override'], true)) {
                throw new DomainException('staff cannot manual_override.');
            }
            return;
        }

        throw new DomainException('You are not allowed to decide.');
    }
}