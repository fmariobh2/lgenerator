<?php namespace Fragale\Generators\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Fragale\Helpers\PathsInfo;
use Illuminate\Support\Pluralizer;

class CrudRemoveCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'makefast:remove';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Drop a resource form the App.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$p=new PathsInfo();

		$this->info('Now, I will drop this resource from the App');
		$resource = $this->argument('resource');
		$dirs = $this->option('dirs');
		
		$model = $resource;  // resource
        $models = Pluralizer::plural($model);   // resources
        $Models = ucwords($models);             // Resources
        $Model = Pluralizer::singular($Models); // Resource

		if($dirs){
			$this->info('WARNING: Views directory will be erased !!!');
		}
		$resourcesingular=substr($resource, 0,-1);
		$modelo=app_path()."/cruds/$Model.php";
		$controlador=app_path().'/Http/Controllers/cruds/'.$Models.'Controller.php';
		$vistas=$p->pathViews()."/cruds/$model";

		$this->info("I will delete the model $modelo");
		$this->info("I will delete the controller $controlador");
		$this->info("I will delete the views $vistas ");

		$continue=false;
		if ($this->option('auto')){
			$continue=true;
		}
		if(!$continue){
			$continue=$this->confirm('Do you wish to continue? [yes|no]');
		}
		if ($continue)
		{
					$this->info("rm $modelo");
					shell_exec("rm $modelo");
					$this->info("rm $controlador");
					shell_exec("rm $controlador");
					if($dirs){
						$this->info("Se elimina completamente el directorio $vistas ");
						$this->info("rm $vistas -rf");
						shell_exec("rm $vistas -rf");
					}else{
						$this->info("Se eliminan solo las vistas del directorio $vistas pero no el directorio ");
						$this->info("rm $vistas/create.blade.php");
						$this->info("rm $vistas/edit.blade.php");
						$this->info("rm $vistas/show.blade.php");
						$this->info("rm $vistas/index.blade.php");
						shell_exec("rm $vistas/create.blade.php");
						shell_exec("rm $vistas/edit.blade.php");
						shell_exec("rm $vistas/show.blade.php");
						shell_exec("rm $vistas/index.blade.php");
					}
					/*
					$this->info("rm $migracion");    		
					shell_exec("rm $migracion"); 
					$this->info("rm $seeds");    		
					shell_exec("rm $seeds"); 
					*/

		}

	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('resource', InputArgument::REQUIRED, 'The resource that you want to drop.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('dirs', null, InputOption::VALUE_NONE, 'Indicate if you want to delete the views directory.', null),		
			array('auto', null, InputOption::VALUE_NONE, 'Do not ask for confirmation.', null),		
			);
	}

}
