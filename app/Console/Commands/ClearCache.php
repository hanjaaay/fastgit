<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CacheService;

class ClearCache extends Command
{
    protected $signature = 'cache:clear-all';
    protected $description = 'Clear all application cache';

    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    public function handle()
    {
        $this->cacheService->clearAllCache();
        $this->info('All cache cleared successfully');
    }
} 