<?php
/**
 * File contains class AbstractGearmanController
 *
 * @author Alexey Korchevsky <mitallast@gmail.com>
 * @link https://github.com/mitallast/yii-gearman
 * @copyright Alexey Korchevsky <mitallast@gmail.com> 2010-2011
 */

/**
 * Class AbstractGearmanController manages gearman actions like CController.
 *
 * @author Alexey Korchevsky <mitallast@gmail.com>
 * @package ext.datamapper
 * @version 0.1
 * @since 0.1
 */
class AbstractGearmanController extends CController
{
	/**
	 * Creates the action instance based on the action name.
	 * The action can be either an inline action or an object.
	 * The latter is created by looking up the action map specified in {@link actions}.
	 * 
	 * @param string $actionID ID of the action. If empty, the {@link defaultAction default action} will be used.
	 * @return AbstractGearmanAction the action instance, null if the action does not exist.
	 * @see actions
	 */
	public function createAction($actionID)
	{
		if($actionID==='')
			$actionID=$this->defaultAction;
		if(method_exists($this,'action'.$actionID) && strcasecmp($actionID,'s')) // we have actions method
			return new GearmanInlineAction($this,$actionID);
		else
			return $this->createActionFromMap($this->actions(),$actionID,$actionID);
	}
}
