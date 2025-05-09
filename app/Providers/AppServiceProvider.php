<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Tạo helper function để định dạng thời gian
        \Illuminate\Support\Facades\Blade::directive('formatTimeAgo', function ($expression) {
            return "<?php 
                \$time = $expression;
                \$now = \Carbon\Carbon::now();
                \$diff = \$time->diffInSeconds(\$now);
                
                if (\$diff < 60) {
                    echo \$diff . 's trước';
                } elseif (\$diff < 3600) {
                    echo floor(\$diff / 60) . ' phút trước';
                } elseif (\$diff < 86400) {
                    echo 'khoảng ' . floor(\$diff / 3600) . ' giờ trước';
                } elseif (\$diff < 604800) {
                    echo floor(\$diff / 86400) . ' ngày trước';
                } else {
                    echo \$time->format('d/m/Y');
                }
            ?>";
        });
    }
}
