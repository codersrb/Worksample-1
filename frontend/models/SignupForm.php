<?php
namespace frontend\models;

use yii\base\Model;
use common\models\User;
use yii\db\Expression;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $userName;
    public $userEmail;
    public $password;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['userName', 'filter', 'filter' => 'trim'],
            ['userName', 'required'],
            ['userName', 'string', 'min' => 2, 'max' => 255],

            ['userEmail', 'filter', 'filter' => 'trim'],
            ['userEmail', 'required'],
            ['userEmail', 'email'],
            ['userEmail', 'string', 'max' => 255],
            ['userEmail', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This email address has already been taken.'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }
        
        $user = new User();
        $user->userName = $this->userName;
        $user->userEmail = $this->userEmail;
        $user->setPassword($this->password);
        $user->userAdded = new Expression('NOW()');
        $user->generateAuthKey();
        
        return $user->save() ? $user : null;
    }
}
