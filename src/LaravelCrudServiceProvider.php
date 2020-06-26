<?php

namespace LaraSnap\LaravelCrud;

use Illuminate\Support\ServiceProvider;
use LaraSnap\LaravelCrud\Commands\InstallCommand;


class LaravelCrudServiceProvider extends ServiceProvider{
	
	public function register(){
		
	}
	
	public function boot(){
		if ($this->app->runningInConsole()) {
            $this->registerConsoleCommands();
        }
	}
	
	private function registerConsoleCommands(){
		$this->commands(InstallCommand::class);
	}

}