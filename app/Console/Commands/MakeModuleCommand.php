<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name}';
    protected $description = 'Create a new module with controllers, models, views, and routes';
    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle()
    {
        $name = $this->argument('name');
        $this->makeModule($name);
        $this->info("Module $name created successfully.");
    }

    protected function makeModule($name)
    {
        $modulePath = base_path("app/Modules/$name");

        // Tạo các thư mục
        $this->files->makeDirectory("$modulePath/Controllers", 0755, true);
        $this->files->makeDirectory("$modulePath/Models", 0755, true);
        $this->files->makeDirectory("$modulePath/Views", 0755, true);
        $this->files->makeDirectory("$modulePath/Routes", 0755, true);
        $this->files->makeDirectory("$modulePath/Migrations", 0755, true);
        
        // Tạo file route
        $this->files->put("$modulePath/Routes/web.php", "<?php\n\nuse Illuminate\Support\Facades\Route;\n\n// Define routes here\n");

        // Tạo controller mẫu
        $controllerTemplate = "<?php\n\nnamespace App\Modules\\$name\Controllers;\n\nuse App\Http\Controllers\Controller;\n\nclass {$name}Controller extends Controller\n{\n    public function index()\n    {\n        return view('$name::index');\n    }\n}";
        $this->files->put("$modulePath/Controllers/{$name}Controller.php", $controllerTemplate);

        // Tạo view mẫu
        $viewTemplate = "<h1>Welcome to the $name module</h1>";
        $this->files->put("$modulePath/Views/index.blade.php", $viewTemplate);
    }
}
