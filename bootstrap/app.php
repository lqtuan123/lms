<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File; // Use the File facade
use App\Modules\ModuleRouteLoader;
  
    $modulesPath = dirname(__DIR__).('/app/Modules');
    
                $webroute = array();
                if (is_dir($modulesPath)) {
                    
                    // Scan the modules directory for subdirectories
                    foreach (scandir($modulesPath) as $module) {
                        if ($module === '.' || $module === '..') {
                            continue; // Skip current and parent directory entries
                        }
                
                        // Path to the module's routes file
                        $routeFile = "$modulesPath/$module/Routes/web.php";
                
                        // Check if the routes file exists
                        array_push( $webroute,$routeFile);
                        
                    }
                }
     
    array_push( $webroute,__DIR__.'/../routes/web.php');
    $app = Application::configure(basePath: dirname(__DIR__))
        ->withRouting(
            // 
            web: $webroute,
            api: __DIR__.'/../routes/api.php',
            commands: __DIR__.'/../routes/console.php',
            health: '/up',
            then: function () {
                
            },
        )
        ->withCommands([
            __DIR__.'/../app/Console/Commands',
            ])
        ->withMiddleware(function (Middleware $middleware) {
            //
            $middleware->alias([
                'admin.auth' => \App\Http\Middleware\AdminAuthenticate::class,
            ]);
            
        })
        ->withExceptions(function (Exceptions $exceptions) {
            //
        })->create();
 
 
 
    return $app;