<?php

namespace App\Modules\Item\Domain\ValueObject;

use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

final class ItemImagePath
{
    private function __construct(
        private string $path
    ) {
        // ★ 念のため / を除去
        $this->path = ltrim($this->path, '/');
    }

    /**
     * DB / JSON / API から来る raw 値
     */
    public static function fromRaw(?string $raw): ?self
    {
        if (!$raw) {
            return null;
        }

        $path = preg_replace('#^/?storage/#', '', $raw);
        $path = ltrim($path, '/');

        return new self($path);
    }

    /**
     * Repository / internal 用
     */
    public static function fromInternal(string $path): self
    {
        // ★ internal でも必ず正規化
        $path = preg_replace('#^/?storage/#', '', $path);
        $path = ltrim($path, '/');

        return new self($path);
    }

    /**
     * ★ 今回追加するメソッド（これが足りなかった）
     * UploadedFile → 保存 → ValueObject
     */
    public static function fromUploadedFile(
        UploadedFile $file,
        string $directory = 'item-drafts',
        string $disk = 'public'
    ): self {
        if (! $file->isValid()) {
            throw new InvalidArgumentException('Invalid image upload');
        }

        $storedPath = $file->store($directory, $disk);

        if (! $storedPath) {
            throw new InvalidArgumentException('Failed to store image');
        }

        return self::fromInternal($storedPath);
    }

    public function value(): string
    {
        return $this->path;
    }
}
