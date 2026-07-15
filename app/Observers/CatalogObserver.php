<?php

namespace App\Observers;

use App\Services\CatalogCache;

class CatalogObserver
{
    public function __construct(private CatalogCache $cache) {}

    public function saved(): void
    {
        $this->cache->flush();
    }

    public function deleted(): void
    {
        $this->cache->flush();
    }
}