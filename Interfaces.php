<?php
/**
 * File contains all interfaces to worker components.
 *
 * @author Alexey Korchevsky <mitallast@gmail.com>
 * @link https://github.com/mitallast/yii-gearman
 * @copyright Alexey Korchevsky <mitallast@gmail.com> 2010-2011
 * @license https://github.com/mitallast/yii-gearman/blob/master/license
 */

/**
 * Interface of worker daemon component.
 *
 * @author Alexey Korchevsky <mitallast@gmail.com>
 * @package ext.worker
 * @version 0.3
 * @since 0.2
 * @see WorkerDaemon
 */
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

/**
 * Interface of worker job object.
 *
 * @author Alexey Korchevsky <mitallast@gmail.com>
 * @package ext.worker
 * @version 0.3
 * @since 0.2
 * @see WorkerJob
 */
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

/**
 * Interface of worker command.
 *
 * @author Alexey Korchevsky <mitallast@gmail.com>
 * @package ext.worker
 * @version 0.3
 * @since 0.3
 * @see WorkerCommand
 */
interface IWorkerCommand
{
	/**
	 * @abstract
	 */
	public function run(IWorkerJob $job);
}