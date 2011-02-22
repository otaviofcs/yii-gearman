<?php
/**
 * File contains class WorkerApplication
 *
 * @author Alexey Korchevsky <mitallast@gmail.com>
 * @link https://github.com/mitallast/yii-gearman
 * @copyright Alexey Korchevsky <mitallast@gmail.com> 2010-2011
 * @license https://github.com/mitallast/yii-gearman/blob/master/license
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
 * Example of worker bootstrap script:
 * <code>
 * // change the following paths if necessary
 * $yii=dirname(__FILE__).'/../yii/yii.php';
 * $config=dirname(__FILE__).'/protected/config/worker.php';
 *
 * // remove the following lines when in production mode
 * defined('YII_DEBUG') or define('YII_DEBUG',true);
 *
 * // specify how many levels of call stack should be shown in each log message
 * defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);
 * require_once($yii);
 * require_once(dirname(__FILE__).'/protected/extensions/worker/WorkerApplication.php');
 *
 * Yii::createApplication("WorkerApplication", $config)->run();
 * </code>
 *
 * Example of worker config:
 * <code>
 * return array(
 *     'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
 *     'import'=>array(
 *         'ext.worker.*',
 *     ),
 *     'components'=>array(
 *         'worker'=>array(
 *             'class'=>'WorkerDaemon',
 *             'servers'=>array('192.168.56.101'),
 *         ),
 *    ),
 * );
 * </code>
 *
 * @see WorkerDaemon
 * @see WorkerRoute
 * @see WorkerController
 * @see AbstractWorkerAction
 * @author Alexey Korchevsky <mitallast@gmail.com>
 * @package ext.worker
 * @version 0.3
 * @since 0.2
 *
 * @param
 */
class WorkerApplication extends CApplication
{
	/**
	 * Hash config. Map worker function to command name.
	 * <code>
	 * array(
	 *   "strrevert" => "application.commands.StringRevertCommand",
	 *   "strtrim" => "StringTrimCommand",
	 * )
	 * </code>
	 *
	 * @var array
	 */
	public $commandMap = array();

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
		$worker = $this->getWorker();

	    foreach($this->commandMap as $commandName => $alias)
	    {
			Yii::import($alias);
			if($pos=strrpos($alias,'.'))
				$class = (string)substr($alias,$pos+1);
			else
				$class = $alias;

			$command = new $class;
		    $worker->setCommand($commandName, array($command, 'run'));
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
	 * Displays the captured PHP error.
	 * This method displays the error in console mode when there is
	 * no active error handler.
	 * @param integer $code error code
	 * @param string $message error message
	 * @param string $file error file
	 * @param string $line error line
	 */
	public function displayError($code,$message,$file,$line)
	{
		echo "PHP Error[$code]: $message\n";
		echo "    in file $file at line $line\n";
		$trace=debug_backtrace();
		// skip the first 4 stacks as they do not tell the error position
		if(count($trace)>4)
			$trace=array_slice($trace,4);
		foreach($trace as $i=>$t)
		{
			if(!isset($t['file']))
				$t['file']='unknown';
			if(!isset($t['line']))
				$t['line']=0;
			if(!isset($t['function']))
				$t['function']='unknown';
			echo "#$i {$t['file']}({$t['line']}): ";
			if(isset($t['object']) && is_object($t['object']))
				echo get_class($t['object']).'->';
			echo "{$t['function']}()\n";
		}
	}
	/**
	 * Displays the uncaught PHP exception.
	 * This method displays the exception in console mode when there is
	 * no active error handler.
	 * @param Exception $exception the uncaught exception
	 */
	public function displayException($exception)
	{
		if(YII_DEBUG)
		{
			echo get_class($exception) . "\n";
			echo $exception->getMessage() . ' (' . $exception->getFile() . ' : ' . $exception->getLine() . "\n";
			echo $exception->getTraceAsString() . "\n";
		}
		else
		{
			echo get_class($exception) . "\n";
			echo $exception->getMessage() . "\n";
		}
	}
	/**
	 * Registers the core application components.
	 * This method overrides the parent implementation by registering additional core components.
	 * @see setComponents
	 */
	protected function registerCoreComponents()
	{
		parent::registerCoreComponents();

		$components = array(
			'worker' => array(
				'class' => 'WorkerDaemon',
			),
		);

		$this->setComponents($components);
	}
}
