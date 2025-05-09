<?php

namespace App\Modules\Tuongtac\Services;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class TuongtacServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('tuongtac.social', function ($app) {
            return new SocialService();
        });
    }

    public function boot()
    {
        // Đăng ký assets
        $this->publishes([
            __DIR__.'/../assets' => public_path('modules/tuongtac'),
        ], 'tuongtac-assets');
        
        // Tạo Blade directive để đưa social-interactions script vào view
        Blade::directive('socialInteractions', function () {
            return "<?php echo '<script src=\"' . asset('modules/tuongtac/social-interactions.js') . '\"></script>'; ?>";
        });
        
        // Đăng ký command
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\PublishAssetsCommand::class,
            ]);
        }
    }
} 