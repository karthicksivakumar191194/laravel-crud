# LaraSnap CRUD

LaraSnap CRUD helps you to create **CRUD** operation with a single line of code with 2 fields(Name, Label) by default. Before using the LaraSnap CRUD make sure [LaraSnap Admin Composer Package](https://karthicksivakumar191194.github.io/larasnap/) is installed on your application. 

For example, if you want to create a **CRUD**  for **photos** you can create it by using the `php artisan larasnap:crud Photo` command. Here **Photo** is name of the **CRUD** & use Pascal Case for the CRUD name. 

The command will create the following files, migration file & adds the routes to application web.php file.
- app/Photo.php
- app/Http/Controllers/PhotoController.php
- app/Http/Services/PhotoService.php
- app/Http/Filters/PhotoFilters.php
- app/Http/Requests/PhotoRequest.php
- resources/views/photos/

Once the files are generated run `composer dump-autoload` from the terminal to finish the process.
