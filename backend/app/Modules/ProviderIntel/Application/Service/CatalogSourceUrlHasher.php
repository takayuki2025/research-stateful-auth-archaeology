<?php

namespace App\Modules\ProviderIntel\Application\Service;

final class CatalogSourceUrlHasher
{
    /** @var string[] */
    private array $dropQueryPrefixes = ['utm_'];

    /** @var string[] */
    private array $dropQueryKeys = ['gclid', 'fbclid'];

    public function canonicalize(string $url): string
    {
        $url = trim($url);

        $parts = parse_url($url);
        if ($parts === false || !isset($parts['host'])) {
            return $url;
        }

        $scheme = strtolower($parts['scheme'] ?? 'https');
        $host = strtolower($parts['host']);
        $port = $parts['port'] ?? null;

        $portPart = '';
        if ($port !== null) {
            $isDefault = ($scheme === 'http' && (int)$port === 80)
                      || ($scheme === 'https' && (int)$port === 443);
            if (!$isDefault) $portPart = ':' . (int)$port;
        }

        $path = $parts['path'] ?? '/';

        $query = $parts['query'] ?? '';
        $canonicalQuery = $this->canonicalizeQuery($query);

        $base = "{$scheme}://{$host}{$portPart}{$path}";
        if ($canonicalQuery !== '') $base .= '?' . $canonicalQuery;

        return $base;
    }

    public function hash(string $url): string
    {
        return hash('sha256', $this->canonicalize($url));
    }

    private function canonicalizeQuery(string $query): string
    {
        if ($query === '') return '';

        parse_str($query, $params);
        if (!is_array($params)) return '';

        $params = array_filter($params, fn($v) => $v !== '' && $v !== null);

        $filtered = [];
        foreach ($params as $k => $v) {
            $kStr = (string)$k;

            $drop = in_array($kStr, $this->dropQueryKeys, true);
            foreach ($this->dropQueryPrefixes as $prefix) {
                if (str_starts_with($kStr, $prefix)) { $drop = true; break; }
            }
            if ($drop) continue;

            $filtered[$kStr] = $v;
        }

        ksort($filtered);

        $pairs = [];
        foreach ($filtered as $k => $v) {
            if (is_array($v)) {
                sort($v);
                foreach ($v as $vv) {
                    $pairs[] = rawurlencode($k) . '=' . rawurlencode((string)$vv);
                }
            } else {
                $pairs[] = rawurlencode($k) . '=' . rawurlencode((string)$v);
            }
        }

        return implode('&', $pairs);
    }
}