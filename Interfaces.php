<?php

/**
 * Interface of worker application.
 *
 * @author Alexey Korchevsky <mitallast@gmail.com>
 * @package ext.worker
 * @version 0.2
 * @since 0.2
 */
interface IWorkerApplication
{
	/**
	 * @abstract
	 * @return IWorkerDaemon
	 */
	public function getWorker();
	/**
	 * @abstract
	 * @param IWorkerDaemon $worker
	 */
	public function setWorker($worker);
	/**
	 * @abstract
	 * @return IWorkerRouter
	 */
	public function getRouter();
	/**
	 * @abstract
	 * @param IWorkerRouter $router
	 */
	public function setRouter($router);
	/**
	 * @abstract
	 * @param IWorkerJob $command
	 */
	public function runCommand(IWorkerJob $command);
}

interface IWorkerDaemon extends IApplicationComponent
{
	/**
	 * @abstract
	 */
	public function run();
	/**
	 * Set daemon activity. If it started, this method is stopped it after cycle complete.
	 * 
	 * @abstract
	 * @param bool $active
	 */
	public function setActive($active);
	/**
	 * @abstract
	 * @param string $commandName
	 * @param mixed $callback
	 */
	public function setCommand($commandName, $callback);
	/**
	 * @abstract
	 * @param  $commandName
	 * @return void
	 */
	public function removeCommand($commandName);
}

interface IWorkerJob
{
	/**
	 * @abstract
	 * @return string
	 */
	public function getCommandName();
	/**
	 * @abstract
	 * @return string
	 */
	public function getIdentifier();
	/**
	 * @abstract
	 * @return string
	 */
	public function getWorkload();
	/**
	 * Sends result data and the complete status update for this job.
     *
     * @param string $result
     * @return bool
	 */
	public function sendComplete($data);
	/**
	 * @abstract
	 * @param Exception $exception
	 * @return void
	 */
	public function sendException($exception);
}

interface IWorkerRouter extends IApplicationComponent
{
	/**
	 * @abstract
	 * @param array $routes
	 */
	public function setRoutes(array $routes);
	/**
	 * @abstract
	 * @param string $commandName
	 * @param array|IWorkerRoute $route
	 * @return void
	 */
	public function setRoute($commandName, $route);
	/**
	 * @abstract
	 * @return IWorkerRoute[]
	 */
	public function getRoutes();
	/**
	 * @param IWorkerJob|string $command
	 * @return IWorkerRoute
	 */
	public function getRoute($command);
}

interface IWorkerRoute
{
	/**
	 * @abstract
	 * @param string $commandName
	 * @param string $controllerId
	 * @param string $actionId
	 */
	public function __construct($commandName, $controllerId, $actionId);
	/**
	 * @abstract
	 * @return string
	 */
	public function getCommandName();
	/**
	 * @abstract
	 * @return string
	 */
	public function getControllerId();
	/**
	 * @abstract
	 * @return string
	 */
	public function getActionId();
}

interface IWorkerAction extends IAction
{
	/**
	 * @abstract
	 * @param IWorkerJob $job
	 */
	public function setJob($job);
	/**
	 * @abstract
	 * @return IWorkerJob
	 */
	public function getJob();
}

interface IWorkerController
{
	/**
	 * @abstract
	 * @param string $actionId
	 * @return IWorkerAction
	 */
	public function createAction($actionId);
}
