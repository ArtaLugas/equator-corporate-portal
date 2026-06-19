<?php

namespace App\Observers;

use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\ServiceController;
use Illuminate\Support\Facades\Cache;

/**
 * Membersihkan cache payload halaman publik (homepage & About) setiap kali
 * konten terkait dibuat / diubah / dihapus, sehingga update CMS langsung tampil.
 */
class HomeContentCacheObserver
{
    public function saved($model): void
    {
        $this->flush();
    }

    public function deleted($model): void
    {
        $this->flush();
    }

    public function restored($model): void
    {
        $this->flush();
    }

    private function flush(): void
    {
        Cache::forget(HomeController::CACHE_KEY);
        Cache::forget(PageController::ABOUT_CACHE_KEY);
        Cache::forget(ServiceController::META_CACHE_KEY);
    }
}
