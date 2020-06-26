<?php

namespace LaraSnap\LaravelCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larasnap:crud {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate CRUD for LaraSnap Admin';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Filesystem $filesystem){
       $name = $this->getNameInput();
       
       $types = ['Model', 'Migration', 'Request', 'Service', 'Filter', 'Controller'];      
       foreach($types as $type){
            $path = $this->getPath($name, $type);
            $this->makeDirectory($path);
            $this->files->put($path, $this->buildFile($name, $type));
            $this->info($type.' created successfully.');
       }
       
       $views = ['create', 'edit', 'index', 'show'];       
       foreach($views as $view){
            $path = $this->laravel->resourcePath().'/views/'.Str::kebab(Str::plural($name)).'/'.$view.'.blade.php';
            $this->makeDirectory($path);
            $stub = $this->files->get(__DIR__.'/stubs/views/'.$view.'.stub');
            $this->files->put($path, $this->replaceViewContent($stub, $name));
       }
       $this->info('Views created successfully.');
       
       $this->publishCRUDRoutes($name);
       $this->info('Routes created successfully.');
  
    }
	
   /**
    * Get the desired class name from the input.
    *
    * @return string
    */
    protected function getNameInput()
    {
        return trim($this->argument('name'));
    }
    
    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name, $type)
    {   
        switch($type){
            case 'Model':
                return $this->laravel['path'].'/'.str_replace('\\', '/', $name).'.php';
            case 'Request':
                return $this->laravel['path'].'/Http/Requests/'.str_replace('\\', '/', $name).'Request.php';  
            case 'Service':
                return $this->laravel['path'].'/Http/Services/'.str_replace('\\', '/', $name).'Service.php'; 
            case 'Filter':
                return $this->laravel['path'].'/Http/Filters/'.str_replace('\\', '/', $name).'Filters.php';                 
            case 'Controller':
                return $this->laravel['path'].'/Http/Controllers/'.str_replace('\\', '/', $name).'Controller.php'; 
            case 'Migration':
                return $this->laravel->databasePath().'/migrations/'.date('Y_m_d_His').'_create_'.Str::snake(Str::plural(str_replace('\\', '/', $name))).'_table.php';
        }        
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (! $this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildFile($name, $type)
    {
        $stub = $this->files->get($this->getStub($type));

        return $this->replaceContent($stub, $name, $type);
    } 

    /**
     * Replace the content for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceContent($stub, $name, $type)
    { 
        $namePlural      = lcfirst(Str::plural($name));
        $nameSnakePlural = Str::snake(Str::plural($name));
        $nameSingular    = lcfirst($name);
        $nameSnakeSingular = Str::snake(($name));
        
        switch($type){
            case 'Model':
                 return str_replace(
                            ['DummyCRUD'], 
                            [$name], 
                            $stub
                        ); 
            case 'Request':
                 return str_replace(
                            ['DummyCRUD', 'DummyNameSnakeSingular'], 
                            [$name, $nameSnakeSingular], 
                            $stub
                        );  
            case 'Service':
                 return str_replace(
                            ['DummyCRUD', 'DummyNamePlural', 'DummyNameSingular'], 
                            [$name, $namePlural, $nameSingular], 
                            $stub
                        );
            case 'Filter':
                 return str_replace(
                            ['DummyCRUD'], 
                            [$name], 
                            $stub
                        );                         
            case 'Controller':
                return str_replace(
                            ['DummyCRUD', 'DummyNameSingular', 'DummyNamePlural', 'DummyNameSnakePlural', 'DummyServiceVariable','DummyNameView'], 
                            [$name, $nameSingular, $namePlural, $nameSnakePlural, lcfirst($name.'Service'), Str::kebab(Str::plural($name))], 
                            $stub
                        );
            case 'Migration':  
                return str_replace(
                            ['DummyCRUD', 'DummyNameSnakePlural'], 
                            [Str::plural($name), $nameSnakePlural], 
                            $stub
                        );
        }                
    }
    
     /**
     * Replace the content for the given view stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceViewContent($stub, $name)
    { 
        return str_replace(
                            ['DummyCRUD', 'DummyNameSingular', 'DummySingularVariable', 'DummyPlural', 'DummyNamePlural', 'DummyNameSnakePlural'], 
                            [$name, Str::kebab($name), lcfirst($name), Str::plural($name), lcfirst(Str::plural($name)), Str::snake(Str::plural($name))], 
                            $stub
                        );
    }
    
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub($type)
    {       
        switch($type){
            case 'Model':
                return __DIR__.'/stubs/model.stub'; 
            case 'Request':
                return __DIR__.'/stubs/request.stub';  
            case 'Service':
                return __DIR__.'/stubs/service.stub'; 
            case 'Filter':
                return __DIR__.'/stubs/filter.stub';         
            case 'Controller':
                return __DIR__.'/stubs/controller.stub';
            case 'Migration':
                return __DIR__.'/stubs/migration.stub';    
        }        
    }
    
    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the CRUD'],
        ];
    }

    /**
     * Publish CRUD Routes.
     *
     */    
    protected function publishCRUDRoutes($name){
        $stub = $this->files->get(__DIR__.'/stubs/web_route.stub'); 
        $routesContents = str_replace(['DummyCRUD', 'DummyNameSnakeSingular', 'DummyNameSnakePlural'], [$name, Str::snake(($name)), Str::snake(Str::plural($name))], $stub); 
        $this->files->append(base_path('routes/web.php'),$routesContents);
    }
    
}
