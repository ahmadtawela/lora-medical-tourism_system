<?php

namespace App\Providers;

use App\Models\MedicalReport;
use App\Observers\MedicalReportObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        MedicalReport::observe(MedicalReportObserver::class);
    }
}
