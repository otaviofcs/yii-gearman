<?php
/**
 * File contains class GearmanInlineAction
 *
 * @author Alexey Korchevsky <mitallast@gmail.com>
 * @link https://github.com/mitallast/yii-gearman
 * @copyright Alexey Korchevsky <mitallast@gmail.com> 2010-2011
 */

/**
 * Class GearmanInlineAction is implementation of AbstractGearmanAction class for inline controller class actions.
 *
 * @author Alexey Korchevsky <mitallast@gmail.com>
 * @package ext.datamapper
 * @version 0.1
 * @since 0.1
 */
class GearmanInlineAction extends AbstractGearmanAction
{
	/**
	 * Runs gearman action.
	 *
	 * @throws CException
	 * @return array hash array(controllerId, actionName)
	 */
	public function run()
	{
		if(!($this->getJob() instanceof GearmanJob))
		{
			throw new CException(Yii::t("gearman", "Gearman job object not setted to controller action"));
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