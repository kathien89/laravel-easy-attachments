<?php 

namespace BenAllfree\LaravelEasyAttachments;

class ServiceProvider extends \Illuminate\Support\ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
    if ($this->app->runningInConsole()) {
      $this->publishes([
        __DIR__.'/../publish/config/easy-attachments.php' => config_path('easy-attachments.php'),
        __DIR__.'/../publish/migrations' => base_path('database/migrations'),
      ], 'laravel-easy-attachments');
    }
    $path = config('easy-attachments.la_path');
    if(!file_exists($path))
    {
      mkdir($path, 0755, true);
    }
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
    $this->mergeConfigFrom(
        __DIR__.'/../publish/config/easy-attachments.php', 'easy-attachments'
    );
    
		$this->app->bind('image.reprocess', function($app)
		{
			return new Commands\ImageReprocess;
		});
    $this->commands('image.reprocess');
		$this->app->bind('image.add', function($app)
		{
			return new Commands\ImageAdd;
		});
    $this->commands('image.add');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
