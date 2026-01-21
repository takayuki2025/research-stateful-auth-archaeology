<?php

namespace App\Modules\Auth\Infrastructure\Security;

use App\Modules\Auth\Domain\Dto\DecodedToken;
use App\Modules\Auth\Domain\Port\TokenVerifierPort;

final class CompositeTokenVerifier implements TokenVerifierPort
{
    /**
     * @param array<string, TokenVerifierPort> $verifiers keyed by provider name
     * @param string[] $order
     */
    public function __construct(
        private readonly array $verifiers,
        private readonly array $order,
    ) {}

    public function decode(string $jwt): DecodedToken
    {
        $errors = [];

        foreach ($this->order as $provider) {
            $v = $this->verifiers[$provider] ?? null;
            if (!$v) continue;

            try {
                return $v->decode($jwt);
            } catch (\Throwable $e) {
                $errors[$provider] = $e->getMessage();
                continue;
            }
        }

        throw new \UnexpectedValueException(
    'JWT verification failed: ' . json_encode($errors, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR)
);
    }
}