<?php
class User extends CActiveRecord
{
	const
		MIN_PASSWORD_LENGTH = 6,
		MAX_PASSWORD_LENGTH = 14,
		PAGE_SIZE = 30,
		DEFAULT_ROLE = 'member',
		PASSWORD_PATTERN='ddcCddcc';
	
	public
		$originalPassword,
		$role,
		$password_repeat,
		$new_password,
		$new_password_repeat;
	
	/**
	 * Returns the static model of the specified AR class.
	 * @return User the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'User';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('username', 'required', 'on'=>array('insert','update')),
			array('username', 'unique', 'caseSensitive'=>false,'on'=>array('insert','update')),
			array('password', 'required', 'on'=>array('insert')),
			array('active', 'numerical', 'integerOnly'=>true, 'on'=>array('update')),
			array('username', 'length', 'max'=>30,'on'=>array('insert')),
			array('password', 'length', 'min'=>self::MIN_PASSWORD_LENGTH, 'max'=>self::MAX_PASSWORD_LENGTH, 'on'=>array('insert','changepass')),
			array('new_password', 'compare', 'on'=>array('changepass')),
			array('new_password_repeat', 'safe', 'on'=>array('changepass')),
			array('password_repeat', 'safe', 'on'=>array('changepass')),
			array('email', 'length', 'max'=>255,'on'=>array('insert','update')),
			array('email', 'email','on'=>array('insert','update')),
			array('email', 'unique', 'caseSensitive'=>false, 'on'=>array('insert','update')),
			array('role', 'in', 'range'=>array_keys(UserController::getAvailableRolesListData()), 'on'=>array('update','insert')),
			array('active', 'boolean', 'on'=>array('insert','update')),
			array('username, email, active, role', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'authAssignment'=>array(self::HAS_ONE, 'AuthAssignment', 'userid', 'joinType' => 'LEFT JOIN'),
			'viewableLetters'=>array(self::MANY_MANY, 'Letter', 'UserLetterPermission(userId,letterId)'),
		);
	}

	/**
	 * Store the md5 of the users password so we can see
	 * if it changed onBeforeSave
	 */
	protected function afterFind()
	{
		$this->originalPassword = $this->password;
		if (!is_null($this->authAssignment))
			$this->role = $this->authAssignment->itemname;
		parent::afterFind();
	}

	/**
	 * Set the user's password to the md5 of that password
	 */
	protected function beforeSave()
	{
		if (!parent::beforeSave()) return false;

		if ($this->originalPassword != $this->password)
		{
			$this->password = md5($this->password);
		}
		return true;
	}

	/**
	 * Set user permissions
	 */
	public function afterSave()
	{
		if (isset($this->role) && Yii::app()->user->checkAccess($this->role) && (is_null($this->authAssignment) || (!is_null($this->authAssignment) && $this->authAssignment->itemname !== $this->role)))
		{
			Yii::app()->authManager->reAssign($this->role, $this->id);
		}
		parent::afterSave();
	}

	/**
	 * Revoke all permissions
	 */
	public function afterDelete()
	{
		Yii::app()->authManager->revokeAll($this->id);
		
		$permissions=UserLetterPermission::model()->findAll('userId=:userId', array(':userId'=>$this->id));
		foreach($permissions as $permission)
			$permission->delete();
		
		parent::afterDelete();
	}

	/**
	 * Reset the password
	 */
	public function resetPassword($pattern=self::PASSWORD_PATTERN)
	{
		$this->password=$newPass=Fn::getRandomString(8, $pattern);
		if ($this->save(false, array('password')))
			return $newPass;
		throw new CHttpException(500, 'Invalid request');
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'=>'Id',
			'username'=>'Gebruikersnaam',
			'password'=>$this->scenario == 'changepass' ? 'Huidige wachtwoord' : 'Wachtwoord',
			'password_repeat'=>'Wachtwoord herhalen',
			'email'=>'E-mail adres',
			'active'=>'Actief (kan inloggen)',
			'role'=>'Rechten',
			'new_password'=>'Nieuw wachtwoord',
			'new_password_repeat'=>'Herhaal nieuw wachtwoord',
		);
	}
	
	/**
	 * Get all users that only have the "viewer" permission
	 */
	public static function getViewersListData()
	{
		return CHtml::listData(
			User::model()->with(array('authAssignment'))->findAll('authAssignment.itemname="viewer"'),
			'id',
			'username'
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria=new CDbCriteria;
		$criteria->compare('username',$this->username,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('authItem.name',$this->role);
		$criteria->compare('active', $this->active);

		$roles = array();
		$allRoles = Yii::app()->authManager->getAllRoles();
		foreach($allRoles as $role)
			$roles[] = $role->name;
		$condition = '`authAssignment`.`itemname` IN ("'.implode('","', $roles).'") OR `authAssignment`.`itemname` IS NULL';
		$criteria->with = array(
			'authAssignment'=>array('condition'=>$condition),
			'authAssignment.authItem'
		);
		
		return new CActiveDataProvider('User', array(
			'criteria'=>$criteria,
			'sort'=>array(
				'attributes'=>array(
					'username',
					'email',
					'active',
					'role'=>array(
						'asc'=>'authItem.description',
						'desc'=>'authItem.description DESC',
					)
				),
				'defaultOrder'=>array(
					'username'=>false,
				),
			),
			'pagination'=>array(
				'pageSize'=>self::PAGE_SIZE
			)
		));
	}
}