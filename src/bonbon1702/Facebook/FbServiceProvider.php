<?php
/**
 * Created by PhpStorm.
 * User: tuan
 * Date: 12/2/2014
 * Time: 4:06 PM
 */

namespace bonbon1702\Facebook;

use Illuminate\Support\ServiceProvider;

class FbServiceProvider extends ServiceProvider {
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;
    /**
     * Boot the package.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('bonbon1702/facebooksdk');
    }
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['bonbon1702.facebooksdk'] = $this->app->share(function ($app)
        {
            $config = $app['config']->get('facebook::config');
            return new Fb(
                $app['config'],
                $app['session.store'],
                $config['app_id'],
                $config['app_secret'],
                $config['redirect_url']
            );
        });
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('bonbon1702.facebooksdk');
    }
} 