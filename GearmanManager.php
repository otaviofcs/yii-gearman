<?php
/**
 * File contains class GearmanManager
 *
 * @author Alexey Korchevsky <mitallast@gmail.com>
 * @link https://github.com/mitallast/yii-gearman
 * @copyright Alexey Korchevsky <mitallast@gmail.com> 2010-2011
 */

/**
 * Class GearmanManager is wrapper to GearmanWorker.
 * Component is optimized to using in Yii framework as application configurable component.
 *
 * For use it, add directory to protected in your application and register it in auto loader.
 * <code>
 * 'import'=>array(
 *      ... // existing load config
 *		'ext.gearman.*',
 *	),
 * </code>
 *
 * For run it, create new script, as example "worker.php":
 * <code>
 *  <?php
 *  // Gearman worker load script.
 *
 *  // change the following paths if necessary
 *  $yii=dirname(__FILE__).'/../yii/yii.php';
 *  $config=dirname(__FILE__).'/protected/config/main.php';
 *
 *  require_once($yii);
 *  // create application
 *  Yii::createWebApplication($config);
 *  // run gearman worker
 *  Yii::app()->gearmanManager->run();
 * </code>
 *
 * For maximum performance, it recommended disable persistent connections and maximum load components,
 * like database connection, before run component. It's simple, using "preload" option in config.
 *
 *
 * @author Alexey Korchevsky <mitallast@gmail.com>
 * @package ext.datamapper
 * @version 0.1
 * @since 0.1
 */
class GearmanManager extends CApplicationComponent
{
	/**
	 * @var string $defaultController name of class or id of default controller.
	 * It's use when worker call function without route. Defaults null.
	 */
	public $defaultController = null;

	private $_controllerRoutes = array();
	private $_servers;
	private $_options;
	private $_timeout;
	private $_worker;
	private $_active;
	
	/**
	 * Initialize component.
	 */
	public function init()
	{
		parent::init();
		$this->initWorker();
	}
	/**
	 * Start worker cycle.
	 * 
	 * @see setWorkCycleState
	 * @see getWorkCycleState
	 */
	public function run()
	{
		$this->setWorkCycleState(true);
		/** @var $worker GearmanWorker */
		$worker = $this->_worker;
		
		while($worker->work() && $this->getWorkCycleState())
		{
			if ($worker->returnCode() != GEARMAN_SUCCESS)
			{
				echo "return_code: " . $worker->returnCode() . "\n";
				break;
			}
		}
	}
	/**
	 * Magic function uses as route in pointer.
	 *
	 * @param string $gearmanFunctionName
	 * @param array $parameters
	 * @see setRoutes
	 */
	public function __call($gearmanFunctionName, $parameters)
	{
		try{
			/** @var $job GearmanJob */
			$job = $parameters[0];
			$route = $this->getControllerRoute($gearmanFunctionName);
			$controllerRoute = Yii::app()->createController($route[0]."/".$route[1]);

			/** @var $controller CController */
			$controller = $controllerRoute[0];
			$actionRoute = $controllerRoute[1];

			/** @var $action AbstractGearmanAction */
			$action = $controller->createAction($actionRoute);
			$action->setJob($job);
			$returnData = $action->run();
			$job->sendComplete($returnData);
			CVarDumper::dump($returnData);
		}
		catch(Exception $e)
		{
			$job->sendException((string)$e);
			//throw $e;
			echo $e;
		}
	}
	/**
	 * Get state component work cycle.
	 * 
	 * @return bool
	 * @see setWorkCycleState
	 */
	public function getWorkCycleState()
	{
		return $this->_active;
	}
	/**
	 * Change component work cycle state.
	 * If it set to false, cycle is stopped at next step.
	 *  
	 * @param bool $active defaults to true.
	 * @see run
	 * @see getWorkCycleState
	 */
	public function setWorkCycleState($active = true)
	{
		$this->_active = (bool)$active;
	}
	/**
	 * @param array $router hash config to route actions
	 * @example
	 * <code>
	 * array(
	 *    "gearmanFunctionName" => array("controllerId" => "actionName"),
	 * )
	 * </code>
	 * @return void
	 */
	public function setRoutes(array $routes)
	{
		$this->_controllerRoutes = $routes;
	}
	/**
	 * Get controller routes.
	 * 
	 * @return array
	 * @see setRoutes
	 */
	public function getRoutes()
	{
		return $this->_controllerRoutes;
	}
	/**
	 * Return server config list
	 * @return array
	 */
	public function getServers()
	{
		return $this->_servers;
	}
	/**
	 * Set server config list.
	 * 
	 * @param array servers
	 * @example
	 * <code>
	 * array(
	 *     "127.0.0.1", // simple address
	 *     array("127.0.0.12",4345), // with port
	 * )
	 * </code>
	 */
	public function setServers(array $servers)
	{
		$this->_servers = $servers;
	}
	/**
	 * Sets one or more options to the supplied value.
     *
     * @link http://php.net/manual/en/gearmanworker.setoptions.php
     * @param int $option The options to be set
     */
	public function setOptions($options)
	{
		$this->_options = (int)$options;
	}
	/**
	 * Get worker options
	 * @link http://php.net/manual/en/gearmanworker.setoptions.php
	 * @return int
	 */
	public function getOptions()
	{
		$this->_options;
	}
	/**
	 * Sets the interval of time to wait for socket I/O activity.
     *
     * @link http://php.net/manual/en/gearmanworker.settimeout.php
     * @param int $timeout An interval of time in milliseconds. A negative value
     *        indicates an infinite timeout
	 * @return void
	 */
	public function setTimeout($timeout)
	{
		$this->_timeout = (int)$timeout;
	}
	/**
	 * Returns the current time to wait, in milliseconds, for socket I/O activity.
	 *
	 * @link http://php.net/manual/en/gearmanworker.timeout.php
     * @return int A time period is milliseconds. A negative value indicates an infinite
     *         timeout
	 */
	public function getTimeout()
	{
		return $this->_timeout;
	}
	/**
	 * @param string $gearmanFunctionName
	 * @return array route array ( controllerId, actionName );
	 */
	protected function getControllerRoute($gearmanFunctionName)
	{
		foreach($this->getRoutes() as $functionName => $route)
		{
			if(strtolower($functionName) == strtolower($gearmanFunctionName))
			{
				return $route;
			}
		}

	    if($this->defaultController)
	    {
			$id = strtolower($this->defaultController);
			$id = str_ireplace("controller", "", $id);
			return array($id, $gearmanFunctionName);
	    }
	    else
		    throw new CException(Yii::t("gearman", "Controller route  not found"));
	}
	/**
	 * Registers server functions at worker.
	 *
	 * @return void
	 */
	protected function initWorker()
	{
		$worker = new GearmanWorker();
		if($this->getServers() === null)
		{
			$worker->addServer();
		}
		else
		{
			foreach($this->getServers() as $server)
			{
				if(is_string($server))
					$worker->addServer($server);
			    elseif(is_array($server))
			    {
				    if(count($server)==1)
					    $worker->addServer($server[0]);
			        elseif(count($server)>1)
				        $worker->addServer($server[0],$server[1]);
			    }
			}
		}

		if($this->getOptions() !== null)
			$worker->setOptions($this->getOptions());
		if($this->getTimeout() !== null)
			$worker->setTimeout($this->getTimeout());



		foreach($this->getRoutes() as $workerFunctionName => $route)
		{
			if(is_string($route))
				$workerFunctionName = $route;

			$worker->addFunction($workerFunctionName, array($this, $workerFunctionName));
		}

	    $this->_worker = $worker;
	}
}