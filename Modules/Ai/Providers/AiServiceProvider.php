<?php

namespace Modules\Ai\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class AiServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Ai';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'ai';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerApiRoutes();
        $this->registerWebRoutes();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Register API routes.
     *
     * @return void
     */
    protected function registerApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->group(module_path($this->moduleName, 'Routes/api.php'));
    }

    /**
     * Register web routes.
     *
     * @return void
     */
    protected function registerWebRoutes()
    {
        Route::middleware('web')
            ->group(module_path($this->moduleName, 'Routes/web.php'));
    }
}
