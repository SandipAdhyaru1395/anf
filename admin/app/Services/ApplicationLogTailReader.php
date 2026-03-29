<?php

namespace App\Services;

class ApplicationLogTailReader
{
    public function __construct(
        private int $maxBytes = 524288,
        private int $maxLines = 2000,
    ) {}

    /**
     * @return array{
     *   content: string,
     *   path: string,
     *   exists: bool,
     *   readable: bool,
     *   file_size: int|null,
     *   truncated_file: bool,
     *   line_count: int,
     *   max_bytes: int,
     *   max_lines: int
     * }
     */
    public function tail(?string $lineContains = null): array
    {
        $path = storage_path('logs/laravel.log');
        $result = [
            'content' => '',
            'path' => $path,
            'exists' => false,
            'readable' => false,
            'file_size' => null,
            'truncated_file' => false,
            'line_count' => 0,
            'max_bytes' => $this->maxBytes,
            'max_lines' => $this->maxLines,
        ];

        if (! is_file($path)) {
            return $result;
        }

        $result['exists'] = true;
        $real = realpath($path);
        $logsDir = realpath(storage_path('logs'));
        if ($real === false || $logsDir === false || ! str_starts_with($real, $logsDir)) {
            return $result;
        }

        if (! is_readable($path)) {
            return $result;
        }

        $result['readable'] = true;
        $size = filesize($path);
        if ($size === false) {
            return $result;
        }

        $result['file_size'] = $size;
        if ($size === 0) {
            return $result;
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return $result;
        }

        try {
            $readFrom = 0;
            if ($size > $this->maxBytes) {
                $readFrom = $size - $this->maxBytes;
                $result['truncated_file'] = true;
            }
            if ($readFrom > 0) {
                fseek($handle, $readFrom);
                fgets($handle);
            }
            $chunk = stream_get_contents($handle) ?: '';
        } finally {
            fclose($handle);
        }

        $normalized = str_replace(["\r\n", "\r"], "\n", $chunk);
        $lines = explode("\n", $normalized);
        $lines = array_slice($lines, -$this->maxLines);

        if ($lineContains !== null && $lineContains !== '') {
            $lines = array_values(array_filter(
                $lines,
                fn (string $line) => mb_stripos($line, $lineContains) !== false
            ));
        }

        $result['line_count'] = count($lines);
        $result['content'] = implode("\n", $lines);

        return $result;
    }
}
