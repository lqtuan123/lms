<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class PublishAssetsCommand extends Command
{
    protected $signature = 'module:publish-assets {module? : Tên module muốn xuất bản assets}';
    protected $description = 'Xuất bản assets từ các module ra thư mục public';

    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle()
    {
        $module = $this->argument('module');
        
        if ($module) {
            $this->publishModuleAssets($module);
        } else {
            $this->publishAllModuleAssets();
        }
        
        $this->info('Assets đã được xuất bản thành công.');
    }

    protected function publishModuleAssets($module)
    {
        $sourceDir = base_path("app/Modules/{$module}/assets");
        $targetDir = public_path("modules/" . strtolower($module));
        
        if (!is_dir($sourceDir)) {
            $this->warn("Module {$module} không có thư mục assets.");
            return;
        }
        
        $this->publishDirectory($sourceDir, $targetDir);
    }

    protected function publishAllModuleAssets()
    {
        $modulesDir = base_path('app/Modules');
        
        if (!is_dir($modulesDir)) {
            $this->warn("Không tìm thấy thư mục Modules.");
            return;
        }
        
        foreach (scandir($modulesDir) as $module) {
            if ($module === '.' || $module === '..') {
                continue;
            }
            
            $this->publishModuleAssets($module);
        }
    }
    
    protected function publishDirectory($source, $destination)
    {
        if (!is_dir($destination)) {
            $this->files->makeDirectory($destination, 0755, true);
        }
        
        $files = $this->files->allFiles($source);
        
        foreach ($files as $file) {
            $sourcePath = $file->getRealPath();
            $destinationPath = $destination . '/' . $file->getRelativePathname();
            
            $destinationDir = dirname($destinationPath);
            if (!is_dir($destinationDir)) {
                $this->files->makeDirectory($destinationDir, 0755, true);
            }
            
            $this->files->copy($sourcePath, $destinationPath);
        }
        
        $this->info("Đã xuất bản assets từ {$source} đến {$destination}");
    }
} 