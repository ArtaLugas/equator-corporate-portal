<?php

namespace App\Observers;

use App\Http\Controllers\Public\HomeController;
use Illuminate\Support\Facades\Cache;

/**
 * Membersihkan cache payload homepage setiap kali konten yang ditampilkan
 * di homepage dibuat / diubah / dihapus, sehingga update CMS langsung tampil.
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

    private function flush(): void
    {
        Cache::forget(HomeController::CACHE_KEY);
    }
}
