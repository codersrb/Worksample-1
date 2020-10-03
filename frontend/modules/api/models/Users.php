<?php

namespace frontend\modules\api\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "{{%users}}".
 *
 * @property integer $pkUserID
 * @property string $userName
 * @property string $userPhone
 * @property string $userEmail
 * @property string $userAuthKey
 * @property string $userPassword
 * @property string $userSigninToken
 * @property integer $userOTP
 * @property string $userResetToken
 * @property string $userProfilePicture
 * @property string $userIdentityCard
* @property string $userIdentityType
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
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'userAdded',
                'updatedAtAttribute' => 'userModified',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userOTP'], 'integer'],
            [['userAdded', 'userModified'], 'safe'],
            [['userRole', 'userIdentityCard','userStatus'], 'string'],
            [['userName'], 'string', 'max' => 60],
            [['userPhone', 'userPassword'], 'string', 'max' => 100],
            [['userEmail'], 'string', 'max' => 150],
            [['userAuthKey'], 'string', 'max' => 32],
            [['userSigninToken', 'userResetToken', 'userIdentityCard'], 'string', 'max' => 255],
            [['userProfilePicture'], 'string', 'max' => 500],
            [['userEmail'], 'unique'],
            [['userPhone'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
     public function attributeLabels()
     {
         return [
             'pkUserID' => 'User ID',
             'userName' => 'Name',
             'userPhone' => 'Phone',
             'userEmail' => 'Email',
             'userAuthKey' => 'Auth Key',
             'userPassword' => 'Password',
             'userSigninToken' => 'Signin Token',
             'userOTP' => 'OTP',
             'userResetToken' => 'Reset Token',
             'userProfilePicture' => 'Profile Picture',
             'userIdentityCard' => 'Identity Card',
             'userIdentityType' => 'Identity Type',
             'userAdded' => 'Added On',
             'userModified' => 'Modified On',
             'userRole' => 'User Role',
             'userStatus' => 'Status',
         ];
     }
}
