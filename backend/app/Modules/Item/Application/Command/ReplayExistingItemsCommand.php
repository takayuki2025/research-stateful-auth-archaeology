<?php

namespace App\Modules\Item\Application\Command;

use Illuminate\Console\Command;
use App\Models\LegacyItem;
use App\Modules\Item\Application\Event\ItemImported;

final class ReplayExistingItemsCommand extends Command
{
    protected $signature = 'atlas:replay-items 
        {--limit=}
        {--source=legacy-db}';

    public function handle(): int
    {
        $query = LegacyItem::query()->orderBy('id');

        if ($limit = $this->option('limit')) {
            $query->limit((int)$limit);
        }

        $source = $this->option('source');

        $query->chunk(100, function ($items) use ($source) {
            foreach ($items as $legacy) {

                // items は「まだ作らない」
                $item = Item::create([
                    'name' => $legacy->name,
                    'status' => 'draft', // or pending_review
                ]);

                event(new ItemImported(
                    itemId: $item->id,
                    rawText: implode(' ', array_filter([
                        $legacy->name,
                        $legacy->brand,
                        $legacy->description,
                    ])),
                    tenantId: null,
                    source: $source,
                ));
            }
        });

        return Command::SUCCESS;
    }
}