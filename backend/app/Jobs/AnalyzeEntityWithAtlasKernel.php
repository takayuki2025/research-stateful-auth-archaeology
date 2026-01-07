<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AnalyzeEntityWithAtlasKernel implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;          // 再試行回数
    public int $timeout = 20;       // Job 自体の上限（秒）

    public function __construct(
        private array $payload
    ) {
    }

    public function handle(): array
    {
        $bin = config('atlaskernel.bin');

        if (! file_exists($bin)) {
            throw new \RuntimeException("AtlasKernel binary not found: {$bin}");
        }

        if (config('atlaskernel.log_payload')) {
            Log::info('[AtlasKernel] payload', $this->payload);
        }

        $process = new Process([$bin]);
        $process->setInput(json_encode($this->payload, JSON_UNESCAPED_UNICODE) . PHP_EOL);
        $process->setTimeout(config('atlaskernel.timeout'));

        $process->run();

        if (! $process->isSuccessful()) {
            Log::error('[AtlasKernel] failed', [
                'exit_code' => $process->getExitCode(),
                'stderr' => $process->getErrorOutput(),
            ]);

            throw new ProcessFailedException($process);
        }

        $output = trim($process->getOutput());
        $result = json_decode($output, true);

        if (! is_array($result)) {
            throw new \RuntimeException('Invalid AtlasKernel output: ' . $output);
        }

        Log::info('[AtlasKernel] success', [
            'entity_type' => $result['entity_type'] ?? null,
            'decision' => $result['decision'] ?? null,
            'confidence' => $result['confidence'] ?? null,
        ]);

        return $result;
    }
}
