<?php

namespace App\Services;

class StorageService
{
    private $basePath;

    public function __construct()
    {
        $this->basePath = storage_path('packages');
        if (!is_dir($this->basePath)) {
            mkdir($this->basePath, 0755, true);
        }
    }

    private function getObjectPath(string $key): string
    {
        return $this->basePath . '/' . ltrim($key, '/');
    }

    public function put(string $key, string $content): bool
    {
        $path = $this->getObjectPath($key);
        $dir = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($path, $content) !== false;
    }

    public function get(string $key): ?string
    {
        $path = $this->getObjectPath($key);

        if (!file_exists($path)) {
            return null;
        }

        return file_get_contents($path);
    }

    public function exists(string $key): bool
    {
        return file_exists($this->getObjectPath($key));
    }

    public function delete(string $key): bool
    {
        $path = $this->getObjectPath($key);

        if (!file_exists($path)) {
            return false;
        }

        return unlink($path);
    }

    public function list(string $prefix = ''): array
    {
        $path = $this->getObjectPath($prefix);

        if (!is_dir($path)) {
            return [];
        }

        $items = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $items[] = [
                    'key' => str_replace($this->basePath . '/', '', $file->getPathname()),
                    'size' => $file->getSize(),
                    'modified' => date('c', $file->getMTime()),
                ];
            }
        }

        return $items;
    }

    public function getSize(string $key): ?int
    {
        $path = $this->getObjectPath($key);
        return file_exists($path) ? filesize($path) : null;
    }
}
