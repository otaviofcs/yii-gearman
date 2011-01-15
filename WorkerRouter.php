<?php
/**
* File contains class WorkerRouter
*
* @author Alexey Korchevsky <mitallast@gmail.com>
* @link https://github.com/mitallast/yii-worker
* @copyright Alexey Korchevsky <mitallast@gmail.com> 2010-2011
*/

/**
* Class WorkerRouter
*
* @author Alexey Korchevsky <mitallast@gmail.com>
* @package ext.worker
* @version 0.1 15.01.11 13:16
* @since 0.1
*/
class WorkerRouter extends CApplicationComponent implements IWorkerRouter
{
	/**
	 * @var CTypedMap<IWorkerRoute>
	 */
	private $_routeList;

	public function __construct()
	{
		$this->_routeList = new CTypedMap("IWorkerRoute");
	}
	/**
	 * @param IWorkerJob|string $command
	 * @return IWorkerRoute
	 */
	public function getRoute($command)
	{
		if($command instanceof IWorkerJob)
			$command = $command->getCommandName();
		
		$command = strtolower($command);
		return $this->_routeList->itemAt($command);
	}
	/**
	 * @return IWorkerRoute[]
	 */
	public function getRoutes()
	{
		return $this->_routeList->toArray();
	}
	/**
	 * Set route rule.
	 * 
	 * @param string $commandName
	 * @param string|array|IWorkerRoute $route
	 * @see setRoutes
	 * @return void
	 */
	public function setRoute($commandName, $route)
	{
		if(!$this->_routeList->contains(strtolower($commandName)))
		{
			$routeObject = null;
			if(is_string($route))
			{
				$routeObject = new WorkerRoute($commandName, $route, $commandName);
			}
			elseif(is_array($route))
			{
				$routeObject = new WorkerRoute($commandName, $route[0], $route[1]);
			}
			elseif($route instanceof IWorkerRoute)
			{
				$routeObject = $route;
			}
			else
			{
				throw new InvalidArgumentException(Yii::t(
					"worker",
					'Parameter "$route" must be string, array(2) or IWorkerRoute object, {type} given',
					array('{type}' => gettype($route))
				));
			}
			
			$this->_routeList->add(strtolower($commandName), $routeObject);
		}
		else throw new CException(Yii::t('worker','Command "{command}" is registered now'));
	}
	/**
	 * Set map route rules. Example:
	 * <code>
	 * array(
	 *    "strrevert" => "workerController",
	 *    "mystrrevert" => array("StrController", "revert"),
	 *    "var_dump" => new WorkerRoute("var_dump", "contoller", "action"),
	 * )
	 * </code>
	 * 
	 * @param array $routes routes map
	 */
	public function setRoutes(array $routes)
	{
		foreach($routes as $command=>$route)
		{
			$this->setRoute($command, $route);
		}
	}
}