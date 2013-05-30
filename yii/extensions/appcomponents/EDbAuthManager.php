<?php
class EDbAuthManager extends CDbAuthManager
{
	/**
	 * Get all roles for a certain user
	 */
	public function getAllRoles($userId = null)
	{
		$userId = $this->_getUserId($userId);
		$roles = parent::getRoles($userId);
		$return = $roles;
		foreach($roles as $role)
			$return = array_merge($return, $this->_getChildRoles($role->name));
		
		return $return;
	}

	/**
	 * Revoke all permissions of a user
	 */
	public function revokeAll($userId)
	{
		if (is_null($userId) || $userId == Yii::app()->user->id)
			throw new CHttpException(500, 'Unable to remove the permissions from user "'.$userId.'"');
		$roles = $this->getAllRoles($userId);
		foreach($roles as $role)
			parent::revoke($role->name, $userId);
	}

	/**
	 * Revoke all permissions of a user and assign a new one
	 */
	public function reAssign($itemName, $userId)
	{
		if (is_null($userId) || $userId == Yii::app()->user->id)
			throw new CHttpException(500, 'Unable to reassign permissions for user "'.$userId.'"');
		$this->revokeAll($userId);
		parent::assign($itemName,$userId);
	}

	/**
	 * Determine the user id
	 */
	private function _getUserId($userId)
	{
		if (is_null($userId) && !Yii::app()->user->isGuest)
			return Yii::app()->user->id;
		else if (!is_null($userId))
			return $userId;
		else
			throw new CHttpException(500, 'Unable to resolve userId');
	}

	/**
	 * Get all child roles of a certain role
	 */
	private function _getChildRoles($role)
	{
		$roles = array();
		$children = parent::getItemChildren($role);
		if (count($children) == 0)
			return array();
		foreach($children as $c)
			if ($c->type == CAuthItem::TYPE_ROLE)
				$roles = array_merge($roles, array($c->name => $c), $this->_getChildRoles($c->name));
		
		return $roles;
	}
}