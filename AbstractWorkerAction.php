<?php
/**
* File contains class AbstractWorkerAction
*
* @author Alexey Korchevsky <mitallast@gmail.com>
* @link https://github.com/mitallast/yii-worker
* @copyright Alexey Korchevsky <mitallast@gmail.com> 2010-2011
*/

/**
 * Class AbstractWorkerAction
 *
 * @abstract
 * @author Alexey Korchevsky <mitallast@gmail.com>
 * @package ext.worker
 * @version 0.1 15.01.11 15:34
 * @since 0.1
 */
abstract class AbstractWorkerAction extends CAction implements IWorkerAction
{
	private $_job;
	
	/**
	 * Set job to work in action.
	 * @param GearmanJob $job
	 */
	public function setJob($job)
	{
		$this->_job = $job;
	}
	/**
	 * Get job to work in action.
	 * @return GearmanJob
	 */
	public function getJob()
	{
		return $this->_job;
	}
}