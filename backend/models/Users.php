<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "{{%users}}".
 *
 * @property string $pkUserID
 * @property string $userEmail
 * @property string $userAuthKey
 * @property string $userPassword
 * @property string $userResetToken
 * @property string $userName
 * @property string $userNumber
 * @property string $userProfilePicture
 * @property string $userAdded
 * @property string $userModified
 * @property string $userRole
 * @property string $userStatus
 */
class Users extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%users}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userEmail', 'userAuthKey', 'userPassword', 'userName'], 'required'],
            [['userAdded', 'userModified'], 'safe'],
            [['userRole', 'userStatus'], 'string'],
            [['userEmail'], 'string', 'max' => 150],
            [['userAuthKey'], 'string', 'max' => 32],
            [['userPassword'], 'string', 'max' => 100],
            [['userResetToken'], 'string', 'max' => 255],
            [['userName'], 'string', 'max' => 60],
            [['userNumber'], 'string', 'max' => 15],
            [['userPProfileicture'], 'string', 'max' => 500],
            [['userEmail'], 'unique'],
            [['userNumber'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pkUserID' => 'Account ID',
            'userEmail' => 'Email',
            'userAuthKey' => 'Auth Key',
            'userPassword' => 'Password',
            'userResetToken' => 'Reset Token',
            'userName' => 'Name',
            'userNumber' => 'Number',
            'userProfilePicture' => 'Picture',
            'userAdded' => 'Added On',
            'userModified' => 'Modified On',
            'userRole' => 'Account Role',
            'userStatus' => 'Status',
        ];
    }
}
