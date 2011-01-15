<?php
/**
* File contains class WorkerApplication
*
* @author Alexey Korchevsky <mitallast@gmail.com>
* @link https://github.com/mitallast/yii-worker
* @copyright Alexey Korchevsky <mitallast@gmail.com> 2010-2011
*/

require_once "Interfaces.php";

/**
 * Class WorkerApplication extends CApplication by providing functionality by worker specific requests.
 *
 * WorkerApplication managers contollers in MVC pattern, provides specific core components to work with
 * job queue:
 * <ul>
 *    <li>{@link worker} Worker daemon, connect to job server queue, implements command routing.</li>
 *    <li>{@link router} Worker router, implements command to controller action routing.</li>
 * </ul>
 *
 * @author Alexey Korchevsky <mitallast@gmail.com>
 * @package ext.worker
 * @version 0.1 15.01.11 12:46
 * @since 0.1
 */
class WorkerApplication extends CApplication implements IWorkerApplication
{
	/**
	 * @return string the ID of the default controller. Defaults to 'worker'.
	 */
	public $defaultController = 'worker';

	/**
	 * Start worker cycle.
	 * To add custom route rules you can add in at worker.
	 * <code>
	 *
	 * // add callback in php5.3 style
	 * $app->getWorker()->setCommand("commandName", function($job){
	 *      $job->setReturn($data->getMessage());
	 * });
	 *
	 * // add callback as
	 * $app->getWorker()->setCommand("commandName", array("controllerId", "action"));
	 * </code>
	 */
	public function processRequest()
	{
		$routes = $this->getRouter()->getRoutes();
	    $worker = $this->getWorker();

	    foreach($routes as $route)
	    {
		    $worker->setCommand($route->getCommandName(), array($this, 'runCommand'));
	    }

	    $worker->run();
	}
	/**
	 * Get worker daemon component.
	 * Also you can call $app->getComponent("worker") or $app->worker.
	 * 
	 * @return IWorkerDaemon
	 */
	public function getWorker()
	{
		return $this->getComponent("worker");
	}
	/**
	 * Set worker daemon component.
	 * Also you can call $app->setWorker("router", $component) or $app->router = $component.
	 *
	 * @param mixed $worker
	 * @see setComponent
	 */
	public function setWorker($worker)
	{
		$this->setComponent("worker", $worker);
	}
	/**
	 * Get worker route component.
	 * Also you can call $app->getComponent("router") or $app->router.
	 *
	 * @return IWorkerRouter
	 * @see getComponent
	 */
	public function getRouter()
	{
		return $this->getComponent("router");
	}
	/**
	 * Set worker route component.
	 * Also you can call $app->setComponent("router", $component) or $app->router = $component.
	 *
	 * @param mixed $router
	 * @return void
	 */
	public function setRouter($router)
	{
		$this->setComponent("router", $router);
	}
	/**
	 * Default callback worker daemon.
	 * It's calls when worker get new job and router have not custom callback.
	 *
	 * @param IWorkerJob $job
	 */
	public function runCommand(IWorkerJob $job)
	{
		try{
			$route = $this->getRouter()->getRoute($job);

			if(is_null($route))
			{
				$controllerId = $this->defaultController;
			    $actionId = $job->getCommandName();
			}
			else
			{
				$controllerId = $route->getControllerId();
			    $actionId = $route->getCommandName();
			}


			$controller = $this->createController($controllerId);
			$controller->init();
			
			/** @var $action IWorkerAction */
			$action = $controller->createAction($actionId);
			if($action instanceof IWorkerAction)
			{
				$action->setJob($job);
			    $action->run();
			}
			else throw new CException(Yii::t(
				"worker",
				"Action is not instance of IWorkerAction"
			));
		}
		catch(Exception $e)
		{
			$job->sendException($e);
		    throw $e;
		}
	}
	/**
	 * Parse contoller id string and return controller class instance.
	 * 
	 * @param string $controllerId
	 * @return IWorkerController
	 */
	private function createController($controllerId)
	{
		$controllerId = trim($controllerId);
		
		if(!strlen($controllerId))
		    throw new InvalidArgumentException(Yii::t("worker", "Invalid controller id"));

	    $path = null;
	    $className = null;
	    $classFile = null;
	    if(strpos($controllerId, '.'))
	    {
		    $lastDot = strrpos($controllerId, '.');
			$path = substr($controllerId, 0, $lastDot);
	        $className = $controllerId = substr($controllerId, $lastDot+1);
	    }
		else
			$className = $controllerId;

	    if(!strpos($className, "Controller"))
	    {
		    $className = ucfirst($className) . "Controller";
	    }
		if($path)
	        $classFile = $path . '.' . $className;
		else
			$classFile = $className;

	    if(!class_exists($className, false))
	    {
		    Yii::import($classFile, true);
	    }

	    if(class_exists($className,false))
		{
			if(is_subclass_of($className,'CController'))
			{
				return new $className($controllerId);
			}
		    else
			    throw new CException(Yii::t(
				    "worker",
				    "Class \"{class}\" is not subclass of CController",
				    array("{class}" => $className)
			    ));
		}
	    else
		    throw new CException(Yii::t(
			    "worker",
			    "Class \"{class}\" is not found",
			    array("{class}" => $className)
		    ));
	}
}
