<?php
/**
* File contains class WorkerInlineAction
*
* @author Alexey Korchevsky <mitallast@gmail.com>
* @link https://github.com/mitallast/yii-worker
* @copyright Alexey Korchevsky <mitallast@gmail.com> 2010-2011
*/

/**
* Class WorkerInlineAction
*
* @author Alexey Korchevsky <mitallast@gmail.com>
* @package ext.worker
* @version 0.1 15.01.11 15:35
* @since 0.1
*/
class WorkerInlineAction extends AbstractWorkerAction
{
	/**
	 * Runs worker action.
	 *
	 * @throws CException
	 * @return array hash array(controllerId, actionName)
	 */
	public function run()
	{
		if(!($this->getJob() instanceof IWorkerJob))
		{
			throw new CException(Yii::t("worker", "Gearman job object not setted to controller action"));
		}

		$controller=$this->getController();
		$methodName='action'.$this->getId();
		$method=new ReflectionMethod($controller,$methodName);
		if($method->getNumberOfParameters() == 1)
		{

			return $method->invokeArgs($controller,array(
				$this->getJob()
			));
		}
		else
			throw new CException("Controller action must contains 1 parameter");
	}
}