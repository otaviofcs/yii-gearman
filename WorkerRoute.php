<?php
/**
* File contains class WorkerRoute
*
* @author Alexey Korchevsky <mitallast@gmail.com>
* @link https://github.com/mitallast/yii-worker
* @copyright Alexey Korchevsky <mitallast@gmail.com> 2010-2011
*/

/**
* Class WorkerRoute
*
* @author Alexey Korchevsky <mitallast@gmail.com>
* @package ext.worker
* @version 0.1 15.01.11 13:17
* @since 0.1
*/
class WorkerRoute extends CComponent implements IWorkerRoute
{
	private $_commandName;
	private $_actionId;
	private $_controllerId;
	/**
	 * @param string $commandName
	 * @param string $controllerId
	 * @param string $actionId
	 */
	public function __construct($commandName, $controllerId, $actionId)
	{
		if(!strlen($commandName))
			throw new CException(Yii::t(
				'worker',
				'$commandName is empty'
			));

		if(!strlen($controllerId))
			throw new CException(Yii::t(
				'worker',
				'$controllerId is empty'
			));

		if(!strlen($actionId))
			throw new CException(Yii::t(
				'worker',
				'$controllerId is empty'
			));
		
		$this->_commandName = (string)$commandName;
	    $this->_controllerId = (string)$controllerId;
	    $this->_actionId = (string)$actionId;
	}
	/**
	 * @return string
	 */
	public function getActionId()
	{
		return $this->_actionId;
	}
	/**
	 * @return string
	 */
	public function getCommandName()
	{
		return $this->_commandName;
	}
	/**
	 * @return string
	 */
	public function getControllerId()
	{
		return $this->_controllerId;
	}
}