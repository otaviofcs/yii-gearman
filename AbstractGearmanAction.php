<?php
/**
 * File contains class AbstractGearmanAction
 *
 * @author Alexey Korchevsky <mitallast@gmail.com>
 * @link https://github.com/mitallast/yii-gearman
 * @copyright Alexey Korchevsky <mitallast@gmail.com> 2010-2011
 */

/**
 * Class AbstractGearmanAction is the base class for gearman controller action classes.
 *
 * @abstract
 * @author Alexey Korchevsky <mitallast@gmail.com>
 * @package ext.datamapper
 * @version 0.1
 * @since 0.1
 */
abstract class AbstractGearmanAction extends CAction
{
	private $_job;
	/**
	 * Set job to work in action.
	 * @param GearmanJob $job
	 */
	public function setJob(GearmanJob $job)
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