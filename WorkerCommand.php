<?php
/**
 * File contains classes WorkerCommand and WorkerEvent
 */

/**
 * Class WorkerCommand is base worker command class.
 * For create user command it's need to extends this class and override
 * function process()
 *
 * @author Alexey Korchevsky <mitallast@gmail.com>
 * @package ext.worker
 * @version 0.3
 * @since 0.3
 */
abstract class WorkerCommand extends CComponent implements IWorkerCommand
{
	abstract public function process(IWorkerJob $job);

	public function run(IWorkerJob $job)
	{
		if($this->beforeRun($job))
		{
			$this->process($job);
			$this->afterRun($job);
		}
	}
	
	protected function afterRun(IWorkerJob $job)
	{
		$event = new WorkerEvent($this, $job);
		$this->raiseEvent("onAfterRun", $event);
		return $event->isValid == true;
	}

	protected function beforeRun(IWorkerJob $job)
	{
		$event = new WorkerEvent($this, $job);
		$this->raiseEvent("onBeforeRun", $event);
		return $event->isValid == true;
	}
}

/**
 * Class WorkerEvent is the base event for all worker events.
 *
 * @author Alexey Korchevsky <mitallast@gmail.com>
 * @package ext.worker
 * @version 0.3
 * @since 0.3
 */
class WorkerEvent extends CEvent
{
	public $job;

	public $isValid = true;

	public function __construct($sender, $job)
	{
		$this->job = $job;
		parent::__construct($sender);
	}
}