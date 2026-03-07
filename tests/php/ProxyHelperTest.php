<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../proxy_helper.php';

class ProxyHelperTest extends TestCase
{
    private string $cacheFile;

    protected function setUp(): void
    {
        // Define a temporary cache file path
        $this->cacheFile = __DIR__ . '/test_cache.json';
        if (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
    }

    protected function tearDown(): void
    {
        // Clean up
        if (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
    }

    public function testFetchWithCacheReturnsFreshCache(): void
    {
        // Setup a dummy cache file "saved" recently
        $cacheData = json_encode(['mock' => 'data']);
        file_put_contents($this->cacheFile, $cacheData);

        // Call fetch_with_cache, should serve and return immediate dummy cache when return_only is true
        // using URL that doesn't actually exist
        $result = fetch_with_cache('http://invalid-test-url.local', $this->cacheFile, 600, [], true);

        $this->assertEquals($cacheData, $result);
    }
}