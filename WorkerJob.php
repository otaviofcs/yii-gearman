<?php
/**
* File contains class WorkerJob
*
* @author Alexey Korchevsky <mitallast@gmail.com>
* @link https://github.com/mitallast/yii-worker
* @copyright Alexey Korchevsky <mitallast@gmail.com> 2010-2011
*/

/**
* Class WorkerJob
*
* @author Alexey Korchevsky <mitallast@gmail.com>
* @package ext.worker
* @version 0.1 15.01.11 15:26
* @since 0.1
*/
class WorkerJob extends CComponent implements IWorkerJob
{
	private $job;
	/**
	 * @param string $commandName
	 * @param GearmanJob $job
	 */
	public function __construct(GearmanJob $job)
	{
		$this->job = $job;
	}
	/**
	 * @return string
	 */
	public function getCommandName()
	{
		return $this->job->functionName();
	}

	/**
	 * @return string
	 */
	public function getIdentifier()
	{
		return $this->job->unique();
	}

	/**
	 * @return string
	 */
	public function getWorkload()
	{
		return $this->job->workload();
	}

	/**
	 * Sends result data and the complete status update for this job.
     *
     * @link http://php.net/manual/en/gearmanjob.sendcomplete.php
     * @param string $result Serialized result data
     * @return bool
	 */
	public function sendComplete($data)
	{
		return $this->job->sendComplete($data);
	}

	/**
	 * @param Exception $exception
	 * @return void
	 */
	public function sendException($exception)
	{
		return $this->job->sendException($exception);
	}
}