<?php

namespace Arsoft\Module\Commands;

use Illuminate\Console\Command;

class makeModuleBackendCommand extends Command
{
    protected $signature = 'armodule:make-backend {name : The name of the class}';
    protected $name = 'armodule:make-backend';
    protected $description = 'Membuat modul backend';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $path = app_path('ModuleBackend');
        $nameSpace = 'App\ModuleBackend';

        // validate module initialized
        if (!file_exists($path)) {
            $this->info(" Modul backend belum terinisialisasi");
            $this->info(" Gunakan perintah \"php artisan armodule:init-backend\" untuk melakukan inisialisasi");
            return false;
        }

        $tempArgument = str_replace('/', DIRECTORY_SEPARATOR, $this->argument('name'));

        // validate argument
        $arguments = explode(DIRECTORY_SEPARATOR, $tempArgument);
        if (count($arguments) != 2) {
            $this->info('Argumen kurang sesuai, gunakan format : ParentName/ChildName');
            return false;
        }

        // validate duplicate module name
        if (file_exists($path . DIRECTORY_SEPARATOR . $tempArgument)) {
            $this->info("Modul \"" . $this->argument('name') . "\" sudah ada, gunakan nama yang berbeda \n");
            return false;
        }

        // inisialisasi path
        $pathCreated = '';
        foreach ($arguments as $key => $argument) {
            $pathCreated .= ucfirst($argument);
            $fullPath = $path . DIRECTORY_SEPARATOR . $pathCreated;

            if (!file_exists($fullPath)) {
                mkdir($fullPath);
            } else {
                if (is_dir($fullPath . '/Providers')) {
                    $this->info('\"' . $fullPath . '\" sudah digunakan, gunakan struktur modul yang berbeda');
                    return false;
                }
            }
            $pathCreated .= DIRECTORY_SEPARATOR;
        }
        $pathCreated = rtrim($pathCreated, DIRECTORY_SEPARATOR);

        $this->info('Inisialisasi modul ' . $pathCreated);

        // validate is module exist
        $parentName = ucfirst($arguments[0]);
        $childName = ucfirst($arguments[1]);
        if (!file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'Stubs' . DIRECTORY_SEPARATOR . 'Backend' . DIRECTORY_SEPARATOR . $parentName . DIRECTORY_SEPARATOR . $childName)) {
            // generate default module
            $this->generateDefaultModule($pathCreated, $path, $nameSpace, $parentName, $childName);
        } else {
            // generate specific module
            $this->generateSpecificModule($pathCreated, $path, $nameSpace);
        }

        $this->info("\nModul berhasil dibuat => url => " . $pathCreated);
    }

    public function generateDefaultModule($pathCreated, $path, $nameSpace, $parentModuleName, $childModuleName)
    {
        // stub origin path
        $stubPath = __DIR__ . DIRECTORY_SEPARATOR . 'Stubs' . DIRECTORY_SEPARATOR . 'Backend' . DIRECTORY_SEPARATOR . 'Default' . DIRECTORY_SEPARATOR;
        // module destination path
        $modulePath = $path . DIRECTORY_SEPARATOR . $pathCreated . DIRECTORY_SEPARATOR;
        // namespace
        $nameSpace = $nameSpace . DIRECTORY_SEPARATOR . $pathCreated;

        // copy controllers
        if (!is_dir($modulePath . 'Controllers')) {
            mkdir($modulePath . 'Controllers');
        }
        $moduleControllerPath = $modulePath . 'Controllers' . DIRECTORY_SEPARATOR . $childModuleName . 'Controller.php';
        copy(
            $stubPath . 'Controllers' . DIRECTORY_SEPARATOR . 'DefaultController.php',
            $moduleControllerPath
        );
        $tempContent = file_get_contents($moduleControllerPath);
        $tempContent = str_replace('__defaultNamespace__', str_replace(DIRECTORY_SEPARATOR, '\\', $nameSpace), $tempContent);
        $tempContent = str_replace('__childModuleName__', $childModuleName, $tempContent);
        file_put_contents($moduleControllerPath, $tempContent);
        $this->info('controllers copied ' . $pathCreated);

        // // copy models
        // if (!is_dir($modulePath . 'Models')) {
        //     mkdir($modulePath . 'Models');
        // }
        // $modelStubPath = $modulePath . 'Models' . DIRECTORY_SEPARATOR . $childModuleName . '.php';
        // copy(
        //     $stubPath . 'Models' . DIRECTORY_SEPARATOR . 'Default.php',
        //     $modelStubPath
        // );
        // $tempContent = file_get_contents($modelStubPath);
        // $tempContent = str_replace('__defaultNamespace__', str_replace(DIRECTORY_SEPARATOR, '\\', $nameSpace), $tempContent);
        // $tempContent = str_replace('__childModuleName__', $childModuleName, $tempContent);
        // $tempContent = str_replace('__childModuleNameLC__', strtolower($childModuleName), $tempContent);
        // file_put_contents($modelStubPath, $tempContent);
        // $this->info('models copied ' . $pathCreated);

        // copy route-service-provider
        if (!is_dir($modulePath . 'Providers')) {
            mkdir($modulePath . 'Providers');
        }
        $moduleRouteServiceProviderPath = $modulePath . 'Providers' . DIRECTORY_SEPARATOR . 'routeServiceProvider.php';
        copy(
            $stubPath . 'Providers' . DIRECTORY_SEPARATOR . 'routeServiceProvider.php',
            $moduleRouteServiceProviderPath
        );
        $tempContent = file_get_contents($moduleRouteServiceProviderPath);
        $tempContent = str_replace('__defaultNamespace__', str_replace(DIRECTORY_SEPARATOR, '\\', $nameSpace), $tempContent);
        $tempPath = "app_path('ModuleBackend" . DIRECTORY_SEPARATOR . $pathCreated . DIRECTORY_SEPARATOR . "Routes" . DIRECTORY_SEPARATOR . "api.php')";
        $tempContent = str_replace('__defaultModulePath__', str_replace(DIRECTORY_SEPARATOR, '/', $tempPath), $tempContent);
        file_put_contents($moduleRouteServiceProviderPath, $tempContent);
        $this->info('service-providers copied ' . $pathCreated);

        // copy form request
        if (!is_dir($modulePath . 'Requests')) {
            mkdir($modulePath . 'Requests');
        }
        $requestStubPath = $stubPath . 'Requests';
        if (is_dir($requestStubPath)) {
            $requestDirectory = opendir($requestStubPath);
            while (($file = readdir($requestDirectory)) !== false) {
                if ($file === '.' || $file === '..'
                ) {
                    continue;
                }
                $moduleRequestPath = $modulePath . 'Requests' . DIRECTORY_SEPARATOR . $file;
                copy(
                    $requestStubPath . DIRECTORY_SEPARATOR . $file,
                    $moduleRequestPath
                );
                $tempContent = file_get_contents($moduleRequestPath);
                $tempContent = str_replace('__defaultNamespace__', str_replace(DIRECTORY_SEPARATOR, '\\', $nameSpace), $tempContent);
                file_put_contents($moduleRequestPath, $tempContent);
            }
            closedir($requestDirectory);
        }
        $this->info('form-request copied ' . $pathCreated);

        // copy route-api
        if (!is_dir($modulePath . 'Routes')) {
            mkdir($modulePath . 'Routes');
        }
        $moduleRoutePath = $modulePath . 'Routes' . DIRECTORY_SEPARATOR . 'api.php';
        copy(
            $stubPath . 'Routes' . DIRECTORY_SEPARATOR . 'api.php',
            $moduleRoutePath
        );
        $tempContent = file_get_contents($moduleRoutePath);
        $tempContent = str_replace('__defaultNamespace__', str_replace(DIRECTORY_SEPARATOR, '\\', $nameSpace), $tempContent);
        $tempContent = str_replace('__childModuleName__', $childModuleName, $tempContent);
        $tempContent = str_replace('__parentModuleName__', $parentModuleName, $tempContent);
        $tempContent = str_replace('__childModuleNameLC__', strtolower($childModuleName), $tempContent);
        $tempContent = str_replace('__parentModuleNameLC__', strtolower($parentModuleName), $tempContent);
        file_put_contents($moduleRoutePath, $tempContent);
        $this->info('routes copied ' . $pathCreated);

        return false;
    }

    public function generateSpecificModule($pathCreated, $path, $nameSpace)
    {
        // stub origin path
        $stubPath = __DIR__ . DIRECTORY_SEPARATOR . 'Stubs' . DIRECTORY_SEPARATOR . 'Backend' . DIRECTORY_SEPARATOR . $pathCreated . DIRECTORY_SEPARATOR;
        // module destination path
        $modulePath = $path . DIRECTORY_SEPARATOR . $pathCreated . DIRECTORY_SEPARATOR;
        // namespace
        $nameSpace = $nameSpace . DIRECTORY_SEPARATOR . $pathCreated;

        // copy controllers
        if (!is_dir($modulePath . 'Controllers')) {
            mkdir($modulePath . 'Controllers');
        }
        $controllerStubPath = $stubPath . 'Controllers';
        if (is_dir($controllerStubPath)) {
            $controllerDirectory = opendir($controllerStubPath);
            while (($file = readdir($controllerDirectory)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $moduleControllerPath = $modulePath . 'Controllers' . DIRECTORY_SEPARATOR . $file;
                copy(
                    $controllerStubPath . DIRECTORY_SEPARATOR . $file,
                    $moduleControllerPath
                );
                $tempContent = file_get_contents($moduleControllerPath);
                $tempContent = str_replace('__defaultNamespace__', str_replace(DIRECTORY_SEPARATOR, '\\', $nameSpace), $tempContent);
                file_put_contents($moduleControllerPath, $tempContent);
            }
            closedir($controllerDirectory);
        }
        $this->info('controllers copied ' . $pathCreated);

        // copy models
        if (!is_dir($modulePath . 'Models')) {
            mkdir($modulePath . 'Models');
        }
        $modelStubPath = $stubPath . 'Models';
        if (is_dir($modelStubPath)) {
            $modelDirectory = opendir($modelStubPath);
            while (($file = readdir($modelDirectory)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                // if directory, then loop inside directory and copy file
                if (empty(pathinfo($file, PATHINFO_EXTENSION))) {
                    if (!is_dir($modulePath . 'Models' . DIRECTORY_SEPARATOR . $file)) {
                        mkdir($modulePath . 'Models' . DIRECTORY_SEPARATOR . $file);
                    }
                    $nestedModelStubPath = $modelStubPath . DIRECTORY_SEPARATOR . $file;
                    $nestedModelDirectory = opendir($nestedModelStubPath);
                    while (($nestedFile = readdir($nestedModelDirectory)) !== false) {
                        if ($nestedFile === '.' || $nestedFile === '..') {
                            continue;
                        }
                        $nestedModuleModelPath = $modulePath . 'Models' . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR . $nestedFile;
                        copy(
                            $nestedModelStubPath . DIRECTORY_SEPARATOR . $nestedFile,
                            $nestedModuleModelPath
                        );
                        $tempContent = file_get_contents($nestedModuleModelPath);
                        $tempContent = str_replace('__defaultNamespace__', str_replace(DIRECTORY_SEPARATOR, '\\', $nameSpace), $tempContent);
                        file_put_contents($nestedModuleModelPath, $tempContent);
                    }
                    closedir($nestedModelDirectory);
                    continue;
                }

                // if not directory ( is file ), then copy file
                $moduleModelPath = $modulePath . 'Models' . DIRECTORY_SEPARATOR . $file;
                copy(
                    $modelStubPath . DIRECTORY_SEPARATOR . $file,
                    $moduleModelPath
                );
                $tempContent = file_get_contents($moduleModelPath);
                $tempContent = str_replace('__defaultNamespace__', str_replace(DIRECTORY_SEPARATOR, '\\', $nameSpace), $tempContent);
                file_put_contents($moduleModelPath, $tempContent);
            }
            closedir($modelDirectory);
        }
        $this->info('models copied ' . $pathCreated);
        
        // copy route-service-provider
        if (!is_dir($modulePath . 'Providers')) {
            mkdir($modulePath . 'Providers');
        }
        $moduleRouteServiceProviderPath = $modulePath . 'Providers' . DIRECTORY_SEPARATOR . 'routeServiceProvider.php';
        copy(
            $stubPath . 'Providers' . DIRECTORY_SEPARATOR . 'routeServiceProvider.php',
            $moduleRouteServiceProviderPath
        );
        $tempContent = file_get_contents($moduleRouteServiceProviderPath);
        $tempContent = str_replace('__defaultNamespace__', str_replace(DIRECTORY_SEPARATOR, '\\', $nameSpace), $tempContent);
        $tempPath = "app_path('ModuleBackend" . DIRECTORY_SEPARATOR . $pathCreated . DIRECTORY_SEPARATOR . "Routes" . DIRECTORY_SEPARATOR . "api.php')";
        $tempContent = str_replace('__defaultModulePath__', str_replace(DIRECTORY_SEPARATOR, '/', $tempPath), $tempContent);
        file_put_contents($moduleRouteServiceProviderPath, $tempContent);
        $this->info('service-providers copied ' . $pathCreated);

        // copy form requests
        if (!is_dir($modulePath . 'Requests')) {
            mkdir($modulePath . 'Requests');
        }
        $requestStubPath = $stubPath . 'Requests';
        if (is_dir($requestStubPath)) {
            $requestDirectory = opendir($requestStubPath);
            while (($file = readdir($requestDirectory)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $moduleRequestPath = $modulePath . 'Requests' . DIRECTORY_SEPARATOR . $file;
                copy(
                    $requestStubPath . DIRECTORY_SEPARATOR . $file,
                    $moduleRequestPath
                );
                $tempContent = file_get_contents($moduleRequestPath);
                $tempContent = str_replace('__defaultNamespace__', str_replace(DIRECTORY_SEPARATOR, '\\', $nameSpace), $tempContent);
                file_put_contents($moduleRequestPath, $tempContent);
            }
            closedir($requestDirectory);
        }
        $this->info('form-request copied ' . $pathCreated);

        // copy route-api
        if (!is_dir($modulePath . 'Routes')) {
            mkdir($modulePath . 'Routes');
        }
        $moduleRoutePath = $modulePath . 'Routes' . DIRECTORY_SEPARATOR . 'api.php';
        copy(
            $stubPath . 'Routes' . DIRECTORY_SEPARATOR . 'api.php',
            $moduleRoutePath
        );
        $tempContent = file_get_contents($moduleRoutePath);
        $tempContent = str_replace('__defaultNamespace__', str_replace(DIRECTORY_SEPARATOR, '\\', $nameSpace), $tempContent);
        file_put_contents($moduleRoutePath, $tempContent);
        $this->info('routes copied ' . $pathCreated);
    }
}
