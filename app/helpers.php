<?php

if (!function_exists('formatBytes')) {
    /**
     * Format bytes into human-readable size.
     */
    function formatBytes(int|float $bytes, int $precision = 1): string
    {
        if ($bytes <= 0) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = floor(log($bytes) / log(1024));
        $pow = min($pow, count($units) - 1);

        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }
}
